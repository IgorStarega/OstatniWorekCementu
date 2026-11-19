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
