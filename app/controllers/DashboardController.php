<?php
// Panel pracownika. Administrator także wchodzi na ?page=dashboard (test 8a),
// ale widzi tylko kartę z linkiem do panelu administratora.
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && postString('action') === 'status') {
            $this->changeStatus();
            return;
        }

        $pdo = Database::connect($this->config['db']);
        $pageTitle = 'Panel';

        if (currentUserRole() === 'admin') {
            require BASE_PATH . '/app/views/layout/header.php';
            require BASE_PATH . '/app/views/dashboard/admin_hint.php';
            require BASE_PATH . '/app/views/layout/footer.php';
            return;
        }

        $employee = (new Employee($pdo))->findByUserId((int) currentUserId());

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

    public function changeStatus(): void
    {
        requireRole('pracownik', 'admin');
        csrf_verify(postString('csrf_token'));

        $pdo = Database::connect($this->config['db']);
        $employee = (new Employee($pdo))->findByUserId((int) currentUserId());

        if ($employee === null) {
            $this->forbidden();
            return;
        }

        $id = (int) postString('id');
        $newStatus = postString('status');
        $reservation = $id > 0 ? (new Reservation($pdo))->find($id) : null;

        // Własność sprawdzamy przed jakąkolwiek zmianą: id z POST da się podmienić.
        if ($reservation === null || (int) $reservation['employee_id'] !== (int) $employee['id']) {
            $this->forbidden();
            return;
        }

        $allowed = [
            'oczekująca'   => ['potwierdzona', 'anulowana'],
            'potwierdzona' => ['zrealizowana'],
        ];
        $oldStatus = (string) $reservation['status'];

        if (!isset($allowed[$oldStatus]) || !in_array($newStatus, $allowed[$oldStatus], true)) {
            flash_set('alert', 'Niedozwolona zmiana statusu.');
            $this->redirect();
        }

        $changed = (new Reservation($pdo))->updateStatus($id, $newStatus, (int) currentUserId());

        if ($changed) {
            flash_set('status', 'Status rezerwacji został zmieniony.');
        } else {
            flash_set('alert', 'Nie udało się zmienić statusu rezerwacji.');
        }

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
