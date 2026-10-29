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

    // Zmiana statusu z whitelistą wartości — walidacja należy do modelu,
    // bo sam kontroler nie może być jedyną barierą.
    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        $statement = $this->pdo->prepare(
            'UPDATE reservations SET status = :status WHERE id = :id'
        );
        $statement->execute([
            'status' => $status,
            'id'     => $id,
        ]);

        return true;
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
}
