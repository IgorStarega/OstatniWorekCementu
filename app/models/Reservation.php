<?php
// Operacje bazodanowe na rezerwacjach (odczyt z JOIN-ami i zmiana statusu).
class Reservation
{
    // Jedyne dozwolone statusy — zgodne z enumem w bazie.
    public const STATUSES = ['oczekująca', 'potwierdzona', 'zrealizowana', 'anulowana'];

    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Lista rezerwacji z danymi klienta, pracownika i usługi.
    // Filtry trafiają wyłącznie jako placeholdery — nigdy do treści SQL.
    public function list(array $filters, int $limit = 0, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhere($filters);

        $sql = $this->baseSelect()
            . $where
            . ' ORDER BY r.reservation_date DESC, r.start_time DESC, r.id DESC';

        if ($limit > 0) {
            $sql .= ' LIMIT :limit OFFSET :offset';
        }

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $name => $value) {
            $statement->bindValue(
                ':' . $name,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }

        if ($limit > 0) {
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        }

        $statement->execute();

        return $statement->fetchAll();
    }

    public function count(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);

        $statement = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM reservations r
             JOIN users u       ON u.id  = r.user_id
             JOIN employees e   ON e.id  = r.employee_id
             JOIN users eu      ON eu.id = e.user_id
             JOIN services s    ON s.id  = r.service_id'
            . $where
        );
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    // Pełne szczegóły jednej rezerwacji (te same JOIN-y co na liście).
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            $this->baseSelect() . ' WHERE r.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $reservation = $statement->fetch();

        return $reservation ?: null;
    }

    // Najbliższe rezerwacje na pulpit: od dziś, bez anulowanych.
    public function upcoming(int $limit = 5): array
    {
        $statement = $this->pdo->prepare(
            "SELECT r.id, r.reservation_date, r.start_time, r.status,
                    CONCAT(u.first_name, ' ', u.last_name) AS client_name,
                    s.name AS service_name
             FROM reservations r
             JOIN users u    ON u.id = r.user_id
             JOIN services s ON s.id = r.service_id
             WHERE r.reservation_date >= CURDATE()
               AND r.status != 'anulowana'
             ORDER BY r.reservation_date ASC, r.start_time ASC
             LIMIT :limit"
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    // Ostatnie rezerwacje — używane na pulpicie, gdy nie ma nadchodzących.
    public function recent(int $limit = 5): array
    {
        $statement = $this->pdo->prepare(
            "SELECT r.id, r.reservation_date, r.start_time, r.status,
                    CONCAT(u.first_name, ' ', u.last_name) AS client_name,
                    s.name AS service_name
             FROM reservations r
             JOIN users u    ON u.id = r.user_id
             JOIN services s ON s.id = r.service_id
             ORDER BY r.reservation_date DESC, r.start_time DESC, r.id DESC
             LIMIT :limit"
        );
        $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    // Zmiana statusu z whitelistą wartości — walidacja należy do modelu,
    // bo sam kontroler nie może być jedyną barierą. SELECT ... FOR UPDATE
    // blokuje wiersz, więc dwa równoległe kliknięcia nie zapiszą sprzecznej historii.
    public function updateStatus(int $id, string $status, ?int $changedBy = null): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                'SELECT status FROM reservations WHERE id = :id FOR UPDATE'
            );
            $statement->execute(['id' => $id]);
            $oldStatus = $statement->fetchColumn();

            if ($oldStatus === false) {
                $this->pdo->rollBack();
                return false;
            }

            $oldStatus = (string) $oldStatus;

            // Bez zmiany nie ma wpisu — historia ma opisywać faktyczne operacje.
            if ($oldStatus === $status) {
                $this->pdo->rollBack();
                return true;
            }

            $statement = $this->pdo->prepare(
                'UPDATE reservations SET status = :status WHERE id = :id'
            );
            $statement->execute([
                'status' => $status,
                'id'     => $id,
            ]);

            (new StatusHistory($this->pdo))->record($id, $oldStatus, $status, $changedBy);

            $this->pdo->commit();

            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    // Nowa rezerwacja klienta. Cała sekwencja w JEDNEJ transakcji, bo między
    // odczytem ceny/usługi a INSERT-em ktoś może zmienić dane. Zwraca wynik
    // zamiast rzucać wyjątkiem — kontroler pokazuje klientowi 'error',
    // a szczegóły trafiają do error_log().
    public function create(
        int $userId,
        int $serviceId,
        int $employeeId,
        string $date,
        string $startTime,
        string $comment
    ): array {
        try {
            $this->pdo->beginTransaction();

            // Cena i czas trwania zablokowane do końca transakcji (FOR UPDATE),
            // żeby final_price pochodził z odczytu, który właśnie zatwierdzamy.
            $statement = $this->pdo->prepare(
                'SELECT duration_minutes, price, is_active FROM services
                 WHERE id = :id FOR UPDATE'
            );
            $statement->execute(['id' => $serviceId]);
            $service = $statement->fetch();

            if ($service === false || (int) $service['is_active'] !== 1) {
                $this->pdo->rollBack();

                return ['ok' => false, 'id' => null, 'error' => 'Usługa jest niedostępna.'];
            }

            // Koniec rezerwacji liczy SERWER z czasu trwania usługi —
            // formularz nigdy nie dostarcza end_time.
            $endTime = (new DateTime($startTime))
                ->modify('+' . (int) $service['duration_minutes'] . ' minutes')
                ->format('H:i:s');

            // Pracownik musi faktycznie wykonywać usługę i mieć aktywne
            // konto — sprawdzamy w bazie, a nie tylko w formularzu.
            $statement = $this->pdo->prepare(
                'SELECT COUNT(*)
                 FROM employee_services es
                 JOIN employees e ON e.id = es.employee_id
                 JOIN users u ON u.id = e.user_id
                 WHERE es.employee_id = :employee_id
                   AND es.service_id = :service_id
                   AND e.is_active = 1
                   AND u.is_active = 1'
            );
            $statement->execute([
                'employee_id' => $employeeId,
                'service_id'  => $serviceId,
            ]);

            if ((int) $statement->fetchColumn() === 0) {
                $this->pdo->rollBack();

                return ['ok' => false, 'id' => null, 'error' => 'Ten pracownik nie wykonuje wybranej usługi.'];
            }

            // Blokada wierszy rezerwacji pracownika z tego dnia: dwa równoległe
            // zapisy nie przejdą obok kontroli konfliktów (wyścig na hasConflict).
            $statement = $this->pdo->prepare(
                'SELECT id FROM reservations
                 WHERE employee_id = :employee_id AND reservation_date = :date
                 FOR UPDATE'
            );
            $statement->execute([
                'employee_id' => $employeeId,
                'date'        => $date,
            ]);

            // Nakładanie się na istniejącą rezerwację — UNIQUE w bazie łapie
            // tylko identyczny start, nakładanie wykrywa wyłącznie ta reguła.
            if ($this->hasConflict($employeeId, $date, $startTime, $endTime)) {
                $this->pdo->rollBack();

                return ['ok' => false, 'id' => null, 'error' => 'Ten termin został właśnie zajęty. Wybierz inny.'];
            }

            // final_price zamrożony: zapisujemy cenę z chwili rezerwacji.
            $statement = $this->pdo->prepare(
                "INSERT INTO reservations
                    (user_id, employee_id, service_id, final_price, reservation_date,
                     start_time, end_time, status, comment)
                 VALUES (:user_id, :employee_id, :service_id, :final_price, :date,
                         :start_time, :end_time, 'oczekująca', :comment)"
            );
            $statement->execute([
                'user_id'     => $userId,
                'employee_id' => $employeeId,
                'service_id'  => $serviceId,
                'final_price' => $service['price'],
                'date'        => $date,
                'start_time'  => $startTime,
                'end_time'    => $endTime,
                'comment'    => $comment,
            ]);
            $reservationId = (int) $this->pdo->lastInsertId();

            // Start historii na TYM SAMYM połączeniu: StatusHistory::record()
            // celowo nie otwiera własnej transakcji (PDO nie ma zagnieżdżania),
            // więc wpis trafi do bazy razem z rezerwacją.
            (new StatusHistory($this->pdo))->record($reservationId, null, 'oczekująca', $userId);

            $this->pdo->commit();

            return ['ok' => true, 'id' => $reservationId, 'error' => null];
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            // Szczegóły tylko do logu — klientowi komunikat bez SQLSTATE.
            error_log('Reservation::create: ' . $exception->getMessage());

            // Duplikat uq_employee_slot — identyczny start prześcignął nas
            // między kontrolą konfliktów a INSERT-em.
            if ($exception->getCode() === '23000') {
                return ['ok' => false, 'id' => null, 'error' => 'Ten termin został właśnie zajęty. Wybierz inny.'];
            }

            return ['ok' => false, 'id' => null, 'error' => 'Nie udało się zapisać rezerwacji.'];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log('Reservation::create: ' . $exception->getMessage());

            return ['ok' => false, 'id' => null, 'error' => 'Nie udało się zapisać rezerwacji.'];
        }
    }

    // Czy nowa rezerwacja nakłada się na istniejącą rezerwację pracownika
    // w danym dniu. UNIQUE w bazie łapie wyłącznie identyczny start_time —
    // nakładanie się (np. 10:00-11:00 wobec 10:30-11:30) musi wykryć
    // ta metoda, inaczej podwójna rezerwacja przejdzie przez bazę.
    // Stykające się końce (09:00-10:00 i 10:00-11:00) NIE są konfliktem.
    public function hasConflict(
        int $employeeId,
        string $date,
        string $start,
        string $end,
        ?int $excludeId = null
    ): bool {
        // Kolumny są typu TIME, więc ujednolicamy format na H:i:s.
        $start = self::normalizeTime($start);
        $end = self::normalizeTime($end);

        $sql = "SELECT COUNT(*) FROM reservations
                WHERE employee_id = :employee_id
                  AND reservation_date = :date
                  AND status != 'anulowana'
                  AND start_time < :new_end
                  AND end_time > :new_start";

        $params = [
            'employee_id' => $employeeId,
            'date'        => $date,
            'new_start'   => $start,
            'new_end'     => $end,
        ];

        // Przy edycji rezerwacja nie koliduje sama ze sobą.
        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    // 'H:i' albo 'H:i:s' -> zawsze 'H:i:s'; niepoprawna godzina -> bez zmian
    // (zapytanie wtedy nic nie znajdzie, co jest bezpieczniejsze niż błąd).
    private static function normalizeTime(string $time): string
    {
        if (preg_match('/^\d{2}:\d{2}$/', $time) === 1) {
            return $time . ':00';
        }

        return $time;
    }

    private function baseSelect(): string
    {
        return 'SELECT r.id, r.user_id, r.employee_id, r.service_id, r.final_price,
                       r.reservation_date, r.start_time, r.end_time, r.status, r.comment, r.created_at,
                       CONCAT(u.first_name, \' \', u.last_name)  AS client_name,
                       u.email                                 AS client_email,
                       u.phone                                 AS client_phone,
                       CONCAT(eu.first_name, \' \', eu.last_name) AS employee_name,
                       s.name                                  AS service_name,
                       s.duration_minutes                      AS service_duration
                FROM reservations r
                JOIN users u       ON u.id  = r.user_id
                JOIN employees e   ON e.id  = r.employee_id
                JOIN users eu      ON eu.id = e.user_id
                JOIN services s    ON s.id  = r.service_id';
    }

    // Buduje WHERE i parametry. Każdy warunek ma własny placeholder.
    private function buildWhere(array $filters): array
    {
        $conditions = [];
        $params = [];

        if (isset($filters['date_from']) && is_string($filters['date_from'])) {
            $conditions[] = 'r.reservation_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (isset($filters['date_to']) && is_string($filters['date_to'])) {
            $conditions[] = 'r.reservation_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (isset($filters['status'])
            && is_string($filters['status'])
            && in_array($filters['status'], self::STATUSES, true)) {
            $conditions[] = 'r.status = :status';
            $params['status'] = $filters['status'];
        }

        if (isset($filters['employee_id']) && (int) $filters['employee_id'] > 0) {
            $conditions[] = 'r.employee_id = :employee_id';
            $params['employee_id'] = (int) $filters['employee_id'];
        }

        // Trzy osobne placeholdery — MySQL z natywnymi prepared statements
        // nie pozwala użyć tego samego nazwanego parametru kilka razy.
        if (isset($filters['q']) && is_string($filters['q']) && $filters['q'] !== '') {
            $conditions[] = '(u.first_name LIKE :q_first OR u.last_name LIKE :q_last OR s.name LIKE :q_service)';
            $like = '%' . $filters['q'] . '%';
            $params['q_first'] = $like;
            $params['q_last'] = $like;
            $params['q_service'] = $like;
        }

        return [
            $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions),
            $params,
        ];
    }

    // Rezerwacje jednego klienta (panel klienta). Widok dostaje gotowe
    // nazwy usługi i pracownika, żeby nie robić zapytań w pętli.
    public function forUser(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT r.id, r.reservation_date, r.start_time, r.end_time, r.status,
                    r.final_price, r.comment,
                    s.name AS service_name, s.duration_minutes,
                    CONCAT(eu.first_name, ' ', eu.last_name) AS employee_name
             FROM reservations r
             JOIN services s ON s.id = r.service_id
             JOIN employees e ON e.id = r.employee_id
             JOIN users eu ON eu.id = e.user_id
             WHERE r.user_id = :user_id
             ORDER BY r.reservation_date DESC, r.start_time DESC, r.id DESC"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    // Jedna rezerwacja właściciela. Cudze ID ma wyglądać jak nieistniejące,
    // dlatego kontroler klienta używa wyłącznie tej metody.
    public function findForUser(int $id, int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT r.id, r.reservation_date, r.start_time, r.end_time, r.status,
                    r.final_price, r.comment,
                    s.name AS service_name, s.duration_minutes,
                    CONCAT(eu.first_name, ' ', eu.last_name) AS employee_name
             FROM reservations r
             JOIN services s ON s.id = r.service_id
             JOIN employees e ON e.id = r.employee_id
             JOIN users eu ON eu.id = e.user_id
             WHERE r.id = :id AND r.user_id = :user_id
             LIMIT 1"
        );
        $statement->execute([
            'id'      => $id,
            'user_id' => $userId,
        ]);
        $reservation = $statement->fetch();

        return $reservation ?: null;
    }

    // Anulowanie przez właściciela: zmiana statusu, nigdy DELETE.
    // Wiersz blokujemy FOR UPDATE, a StatusHistory dostaje TO SAMO $pdo,
    // bo PDO nie obsługuje zagnieżdżonych transakcji.
    public function cancel(int $id, int $userId): array
    {
        try {
            $this->pdo->beginTransaction();

            $statement = $this->pdo->prepare(
                'SELECT id, status, reservation_date, start_time,
                        (reservation_date < CURDATE()
                         OR (reservation_date = CURDATE() AND start_time <= CURTIME())) AS already_started
                 FROM reservations
                 WHERE id = :id AND user_id = :user_id
                 FOR UPDATE'
            );
            $statement->execute([
                'id'      => $id,
                'user_id' => $userId,
            ]);
            $reservation = $statement->fetch();

            if ($reservation === false) {
                $this->pdo->rollBack();

                return ['ok' => false, 'error' => 'Nie znaleziono rezerwacji.'];
            }

            $oldStatus = (string) $reservation['status'];

            if ($oldStatus === 'anulowana') {
                $this->pdo->rollBack();

                return ['ok' => false, 'error' => 'Ta rezerwacja jest już anulowana.'];
            }

            if ((int) $reservation['already_started'] === 1) {
                $this->pdo->rollBack();

                return ['ok' => false, 'error' => 'Nie można anulować wizyty, która już się rozpoczęła.'];
            }

            $statement = $this->pdo->prepare(
                "UPDATE reservations
                 SET status = 'anulowana'
                 WHERE id = :id AND user_id = :user_id"
            );
            $statement->execute([
                'id'      => $id,
                'user_id' => $userId,
            ]);

            (new StatusHistory($this->pdo))->record($id, $oldStatus, 'anulowana', $userId);

            $this->pdo->commit();

            return ['ok' => true, 'error' => null];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log('Reservation::cancel: ' . $exception->getMessage());

            return ['ok' => false, 'error' => 'Nie udało się anulować rezerwacji.'];
        }
    }
}
