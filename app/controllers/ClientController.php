<?php
// Panel klienta: własne rezerwacje i ich szczegóły.
// Klient nigdy nie dostaje Reservation::find() — wyłącznie findForUser()/cancel(),
// więc cudze ID wygląda jak nieistniejące (404), a nie jak brak uprawnień.
class ClientController
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function index(): void
    {
        requireRole('klient');

        $pdo = Database::connect($this->config['db']);
        $rows = (new Reservation($pdo))->forUser((int) currentUserId());

        $today = date('Y-m-d');
        $future = [];
        $history = [];

        foreach ($rows as $row) {
            if ((string) $row['reservation_date'] >= $today && $row['status'] !== 'anulowana') {
                $future[] = $row;
            } else {
                $history[] = $row;
            }
        }

        $pageTitle = 'Moje rezerwacje';
        $pageClass = 'client-main';
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/client/reservations.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    public function show(int $id): void
    {
        requireRole('klient');

        // Anulowanie przychodzi POST-em na ten sam adres (action=cancel).
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->cancel();
            return;
        }

        $pdo = Database::connect($this->config['db']);
        $reservations = new Reservation($pdo);
        $reservation = $reservations->findForUser($id, (int) currentUserId());

        if ($reservation === null) {
            $this->notFound();
            return;
        }

        $history = (new StatusHistory($pdo))->forReservation($id);
        $canCancel = $this->canCancel($reservation, $reservations);

        $pageTitle = 'Rezerwacja #' . (int) $reservation['id'];
        $pageClass = 'client-main';
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/client/reservation.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    public function cancel(): void
    {
        requireRole('klient');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            $pageTitle = 'Metoda niedozwolona';
            $allowMethod = 'POST';
            require BASE_PATH . '/app/views/layout/header.php';
            require BASE_PATH . '/app/views/errors/405.php';
            require BASE_PATH . '/app/views/layout/footer.php';
            return;
        }

        csrf_verify(postString('csrf_token'));

        $pdo = Database::connect($this->config['db']);
        $id = (int) postString('id');
        $userId = (int) currentUserId();

        // Cudze i nieistniejące ID kończy się tak samo — 404, zero zmian w bazie.
        if ($id < 1 || (new Reservation($pdo))->findForUser($id, $userId) === null) {
            $this->notFound();
            return;
        }

        $result = (new Reservation($pdo))->cancel($id, $userId);

        if (($result['ok'] ?? false) === true) {
            flash_set('status', 'Rezerwacja została anulowana.');
        } else {
            $error = is_string($result['error'] ?? null)
                ? $result['error']
                : 'Nie udało się anulować rezerwacji.';
            flash_set('alert', $error);
        }

        header('Location: /?page=my-reservations');
        exit;
    }

    // Przełożenie wizyty: GET pokazuje formularz, POST zapisuje nowy termin.
    // Cudze ID kończy się 404 tak samo jak w show() — bez ujawniania istnienia.
    public function reschedule(): void
    {
        requireRole('klient');

        $pdo = Database::connect($this->config['db']);
        $reservations = new Reservation($pdo);
        $userId = (int) currentUserId();

        $rawId = $_SERVER['REQUEST_METHOD'] === 'POST'
            ? postString('id')
            : (is_string($_GET['id'] ?? null) ? $_GET['id'] : '');
        $id = ctype_digit($rawId) ? (int) $rawId : 0;

        $reservation = $id > 0 ? $reservations->findForUser($id, $userId) : null;
        if ($reservation === null) {
            $this->notFound();
            return;
        }

        // Anulowanej i zrealizowanej wizyty nie zmieniamy także przez
        // bezpośredni adres — UI ukrywa link, ale to nie jest zabezpieczenie.
        $status = (string) $reservation['status'];
        if (in_array($status, ['anulowana', 'zrealizowana'], true)
            || $reservations->isStarted($id)) {
            flash_set('alert', 'Tej wizyty nie można już zmienić.');
            header('Location: /?page=reservation&id=' . $id);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveReschedule($pdo, $reservations, $reservation);
            return;
        }

        $date = $this->rescheduleDate($reservation);

        $this->renderReschedule($pdo, $reservation, $date, []);
    }

    // Zapis nowego terminu. Data i godzina są sprawdzane dokładnie tak samo
    // jak w procesie rezerwacji: start musi być na liście slotówFor().
    private function saveReschedule(PDO $pdo, Reservation $reservations, array $reservation): void
    {
        csrf_verify(postString('csrf_token'));

        $id = (int) $reservation['id'];
        $date = trim(postString('date'));
        $startTime = trim(postString('start_time'));
        $errors = [];

        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            $errors[] = 'Podaj poprawną datę wizyty.';
        } elseif ($date < date('Y-m-d')) {
            $errors[] = 'Data nie może być z przeszłości.';
        }

        $slots = [];
        if ($errors === []) {
            $slots = (new AvailabilityService($pdo))->slotsFor(
                (int) $reservation['employee_id'],
                (int) $reservation['service_id'],
                $date
            );

            if ($startTime === '' || !in_array($startTime, $slots, true)) {
                $errors[] = 'Wybierz godzinę z listy dostępnych terminów.';
            }
        }

        if ($errors !== []) {
            // 422, nie 200: żądanie było poprawne technicznie, ale termin
            // nie przeszedł walidacji. Dane w bazie pozostają bez zmian.
            http_response_code(422);
            $this->renderReschedule($pdo, $reservation, $date !== '' ? $date : date('Y-m-d'), $errors);
            return;
        }

        $result = $reservations->reschedule(
            $id,
            (int) currentUserId(),
            $date,
            $startTime
        );

        if (($result['ok'] ?? false) === true) {
            flash_set('status', 'Termin wizyty został zmieniony.');
        } else {
            $error = is_string($result['error'] ?? null)
                ? $result['error']
                : 'Nie udało się zmienić terminu wizyty.';
            flash_set('alert', $error);
        }

        header('Location: /?page=reservation&id=' . $id);
        exit;
    }

    // Domyślna data formularza: dotychczasowy termin, o ile nie jest przeszły.
    private function rescheduleDate(array $reservation): string
    {
        $today = date('Y-m-d');
        $date = is_string($_GET['date'] ?? null) ? trim($_GET['date']) : '';

        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        if ($parsed === false || $parsed->format('Y-m-d') !== $date || $date < $today) {
            $reservationDate = (string) $reservation['reservation_date'];
            $date = $reservationDate >= $today ? $reservationDate : $today;
        }

        return $date;
    }

    private function renderReschedule(PDO $pdo, array $reservation, string $date, array $errors): void
    {
        $slots = (new AvailabilityService($pdo))->slotsFor(
            (int) $reservation['employee_id'],
            (int) $reservation['service_id'],
            $date
        );

        $pageTitle = 'Przełożenie wizyty #' . (int) $reservation['id'];
        $pageClass = 'client-main';
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/client/reschedule.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    // Przycisk anulowania pokazujemy tylko dla nieanulowanej wizyty, która
    // jeszcze się nie rozpoczęła. Start liczy model tym samym warunkiem SQL
    // co cancel(), więc UI i zapis nie mogą się rozjechać.
    private function canCancel(array $reservation, Reservation $reservations): bool
    {
        if ((string) $reservation['status'] === 'anulowana') {
            return false;
        }

        return !$reservations->isStarted((int) $reservation['id']);
    }

    private function notFound(): void
    {
        http_response_code(404);
        $pageTitle = 'Nie znaleziono rezerwacji';
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/errors/404.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }
}
