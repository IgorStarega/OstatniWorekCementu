<?php
// Panel pracownika: wizyty przypisane do profilu employees powiązanego
// z zalogowanym kontem users. Administrator nadal wchodzi na ?page=dashboard
// (test 8a), ale widzi tylko kartę z linkiem do panelu administratora.
class DashboardController
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function index(): void
    {
        requireRole('pracownik', 'admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->changeStatus();
            return;
        }

        $pdo = Database::connect($this->config['db']);

        if (currentUserRole() === 'admin') {
            $pageTitle = 'Panel zarządzania';
            require BASE_PATH . '/app/views/layout/header.php';
            require BASE_PATH . '/app/views/dashboard/admin_hint.php';
            require BASE_PATH . '/app/views/layout/footer.php';
            return;
        }

        $employee = (new Employee($pdo))->findByUserId((int) currentUserId());

        $pageTitle = 'Panel pracownika';
        $pageClass = 'dashboard-main';

        if ($employee === null) {
            require BASE_PATH . '/app/views/layout/header.php';
            require BASE_PATH . '/app/views/dashboard/no_profile.php';
            require BASE_PATH . '/app/views/layout/footer.php';
            return;
        }

        $rows = (new Reservation($pdo))->forEmployee((int) $employee['id']);

        $today = date('Y-m-d');
        $todayRows = [];
        $futureRows = [];
        $historyRows = [];

        foreach ($rows as $row) {
            $date = (string) $row['reservation_date'];

            if ($date === $today) {
                $todayRows[] = $row;
            } elseif ($date > $today) {
                $futureRows[] = $row;
            } else {
                $historyRows[] = $row;
            }
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/dashboard/index.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    // Zmiana statusu wizyty przez pracownika. Najpierw własność (id z POST
    // da się podmienić), potem whitelista dozwolonych przejść.
    public function changeStatus(): void
    {
        requireRole('pracownik', 'admin');

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

        // Administrator nie ma profilu w employees, więc nie może zmieniać
        // statusów tutaj — robi to w panelu rezerwacji. Bez tego dostawał 403
        // po kliknięciu w dashboardzie.
        if (currentUserRole() === 'admin') {
            flash_set('alert', 'Administrator zmienia statusy w panelu rezerwacji.');
            $this->redirect();
        }

        $pdo = Database::connect($this->config['db']);
        $employee = (new Employee($pdo))->findByUserId((int) currentUserId());

        if ($employee === null) {
            $this->forbidden();
            return;
        }

        $id = (int) postString('id');
        $reservation = $id > 0 ? (new Reservation($pdo))->find($id) : null;

        if ($reservation === null) {
            flash_set('alert', 'Nie znaleziono rezerwacji.');
            $this->redirect();
        }

        if ((int) $reservation['employee_id'] !== (int) $employee['id']) {
            $this->forbidden();
            return;
        }

        $status = postString('status');
        $allowed = [
            'oczekująca'   => ['potwierdzona', 'anulowana'],
            'potwierdzona' => ['zrealizowana'],
        ];
        $oldStatus = (string) $reservation['status'];

        if (!isset($allowed[$oldStatus]) || !in_array($status, $allowed[$oldStatus], true)) {
            flash_set('alert', 'Ta zmiana statusu jest niedozwolona.');
            $this->redirect();
        }

        (new Reservation($pdo))->updateStatus($id, $status, (int) currentUserId());

        flash_set('status', 'Status rezerwacji został zmieniony.');
        $this->redirect();
    }

    private function forbidden(): void
    {
        http_response_code(403);
        $pageTitle = 'Brak dostępu';
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/errors/403.php';
        require BASE_PATH . '/app/views/layout/footer.php';
        exit;
    }

    private function redirect(): void
    {
        header('Location: /?page=dashboard');
        exit;
    }
}
