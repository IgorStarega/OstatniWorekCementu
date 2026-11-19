<?php
// Panel klienta: wyłącznie własne rezerwacje i ich anulowanie.
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
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/client/reservations.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    public function show(int $id): void
    {
        requireRole('klient');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && postString('action') === 'cancel') {
            $this->cancel();
            return;
        }

        // GET z action=cancel nie jest dozwolony — anulowanie tylko POST-em.
        if (is_string($_GET['action'] ?? null) && $_GET['action'] === 'cancel') {
            $this->cancel();
            return;
        }

        // IDOR: wyłącznie findForUser(), find() zostaje dla panelu admina (zadanie #91).
        $pdo = Database::connect($this->config['db']);
        $reservation = (new Reservation($pdo))->findForUser($id, (int) currentUserId());

        if ($reservation === null) {
            http_response_code(404);
            $pageTitle = 'Nie znaleziono rezerwacji';
            require BASE_PATH . '/app/views/layout/header.php';
            require BASE_PATH . '/app/views/errors/404.php';
            require BASE_PATH . '/app/views/layout/footer.php';
            return;
        }

        $history = (new StatusHistory($pdo))->forReservation($id);
        $canCancel = $this->canCancel($reservation);

        $pageTitle = 'Rezerwacja #' . (int) $reservation['id'];
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
            exit;
        }

        csrf_verify(postString('csrf_token'));

        $pdo = Database::connect($this->config['db']);
        $result = (new Reservation($pdo))->cancel(
            (int) postString('id'),
            (int) currentUserId()
        );

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

    // Przycisk anulowania tylko dla przyszłej, nieanulowanej wizyty.
    // Model i tak sprawdza to ponownie przy zapisie.
    private function canCancel(array $reservation): bool
    {
        if ((string) $reservation['status'] === 'anulowana') {
            return false;
        }

        $today = date('Y-m-d');
        $date = (string) $reservation['reservation_date'];

        if ($date > $today) {
            return true;
        }

        return $date === $today
            && substr((string) $reservation['start_time'], 0, 8) > date('H:i:s');
    }
}
