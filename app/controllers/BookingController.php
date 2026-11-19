<?php
// Proces rezerwacji klienta: usługa -> pracownik -> data -> godzina -> potwierdzenie.
// Kontroler sprawdza to, czego nie sprawdza Reservation::create(): przeszłą datę,
// dzień wolny, okno pracy i dokładną godzinę z listy slotów.
class BookingController
{
    private const STEPS = ['service', 'employee', 'date', 'time', 'confirm'];

    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function handle(): void
    {
        requireRole('klient');

        $pdo = Database::connect($this->config['db']);

        $step = $_GET['step'] ?? 'service';
        if (!is_string($step) || !in_array($step, self::STEPS, true)) {
            $this->redirect('/?page=booking');
        }

        $state = $this->state();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost($pdo, $step, $state);
            return;
        }

        // Wejście z oferty: /?page=booking&service_id=1
        if ($step === 'service' && isset($_GET['service_id'])) {
            $serviceId = is_string($_GET['service_id']) ? (int) $_GET['service_id'] : 0;
            $service = (new Service($pdo))->findActive($serviceId);

            if ($service === null) {
                flash_set('alert', 'Usługa jest niedostępna.');
                $this->redirect('/?page=offer');
            }

            $_SESSION['booking'] = [
                'service_id'  => (int) $service['id'],
                'employee_id' => 0,
                'date'        => '',
                'start_time'  => '',
                'comment'     => '',
            ];
            $this->redirect('/?page=booking&step=employee');
        }

        // Bramka kolejności: ręczne przeskoczenie kroku kończy się powrotem.
        $blocked = $this->gate($pdo, $step, $state);
        if ($blocked !== null) {
            flash_set('alert', $blocked['message']);
            $this->redirect('/?page=booking&step=' . $blocked['step']);
        }

        $this->render($pdo, $step, $state);
    }

    private function handlePost(PDO $pdo, string $step, array $state): void
    {
        csrf_verify(postString('csrf_token'));

        $action = postString('action');

        // Cofanie czyści wybraną wartość i wszystkie późniejsze.
        if ($action === 'back') {
            $to = postString('to');
            if (!in_array($to, ['service', 'employee', 'date', 'time'], true)) {
                flash_set('alert', 'Nie można wrócić do wskazanego kroku.');
                $this->redirect('/?page=booking&step=' . $step);
            }

            $state = $this->clearFrom($state, $to);
            $_SESSION['booking'] = $state;
            $this->redirect('/?page=booking&step=' . $to);
        }

        if ($step === 'service' && $action === 'select') {
            $serviceId = (int) postString('service_id');
            $service = (new Service($pdo))->findActive($serviceId);

            if ($service === null) {
                flash_set('alert', 'Wybrana usługa jest niedostępna.');
                $this->redirect('/?page=booking&step=service');
            }

            $_SESSION['booking'] = [
                'service_id'  => (int) $service['id'],
                'employee_id' => 0,
                'date'        => '',
                'start_time'  => '',
                'comment'     => '',
            ];
            $this->redirect('/?page=booking&step=employee');
        }

        if ($step === 'employee' && $action === 'select') {
            $employees = (new Employee($pdo))->forService($state['service_id']);
            $employeeIds = array_map('intval', array_column($employees, 'id'));
            $employeeId = (int) postString('employee_id');

            if (!in_array($employeeId, $employeeIds, true)) {
                flash_set('alert', 'Ten pracownik nie wykonuje wybranej usługi.');
                $this->redirect('/?page=booking&step=employee');
            }

            $state['employee_id'] = $employeeId;
            $state['date'] = '';
            $state['start_time'] = '';
            $_SESSION['booking'] = $state;
            $this->redirect('/?page=booking&step=date');
        }

        if ($step === 'date' && $action === 'select') {
            $date = postString('date');
            $parsed = DateTime::createFromFormat('Y-m-d', $date);

            if ($parsed === false || $parsed->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
                flash_set('alert', 'Podaj poprawną datę (dzisiejszą lub późniejszą).');
                $this->redirect('/?page=booking&step=date');
            }

            $slots = (new AvailabilityService($pdo))
                ->slotsFor($state['employee_id'], $state['service_id'], $date);

            if ($slots === []) {
                flash_set('alert', 'Brak wolnych terminów w tym dniu. Wybierz inny.');
                $this->redirect('/?page=booking&step=date');
            }

            $state['date'] = $date;
            $state['start_time'] = '';
            $_SESSION['booking'] = $state;
            $this->redirect('/?page=booking&step=time');
        }

        if ($step === 'time' && $action === 'select') {
            $startTime = postString('start_time');
            $slots = (new AvailabilityService($pdo))
                ->slotsFor($state['employee_id'], $state['service_id'], $state['date']);

            if (!in_array($startTime, $slots, true)) {
                flash_set('alert', 'Wybrany termin nie jest już dostępny.');
                $this->redirect('/?page=booking&step=time');
            }

            $state['start_time'] = $startTime;
            $_SESSION['booking'] = $state;
            $this->redirect('/?page=booking&step=confirm');
        }

        if ($step === 'confirm' && $action === 'create') {
            $service = (new Service($pdo))->findActive($state['service_id']);
            if ($service === null) {
                flash_set('alert', 'Usługa jest niedostępna.');
                $this->redirect('/?page=booking&step=service');
            }

            $employees = (new Employee($pdo))->forService((int) $service['id']);
            $employeeIds = array_map('intval', array_column($employees, 'id'));

            if (!in_array($state['employee_id'], $employeeIds, true)) {
                flash_set('alert', 'Ten pracownik nie wykonuje wybranej usługi.');
                $this->redirect('/?page=booking&step=employee');
            }

            // Potwierdzenie liczy sloty od nowa: termin mógł zostać zajęty.
            $slots = (new AvailabilityService($pdo))
                ->slotsFor($state['employee_id'], (int) $service['id'], $state['date']);

            if (!in_array($state['start_time'], $slots, true)) {
                flash_set('alert', 'Wybrany termin nie jest już dostępny.');
                $this->redirect('/?page=booking&step=time');
            }

            $comment = mb_substr(postString('comment'), 0, 500);
            $state['comment'] = $comment;
            $_SESSION['booking'] = $state;

            $result = (new Reservation($pdo))->create(
                (int) currentUserId(),
                (int) $service['id'],
                (int) $state['employee_id'],
                $state['date'],
                $state['start_time'],
                $comment
            );

            if (($result['ok'] ?? false) === true) {
                unset($_SESSION['booking']);
                flash_set('status', 'Rezerwacja #' . (int) $result['id'] . ' została zapisana.');
                $this->redirect('/?page=my-reservations');
            }

            // Zostawiamy usługę, pracownika i datę; klient wybiera tylko nową godzinę.
            $error = is_string($result['error'] ?? null)
                ? $result['error']
                : 'Nie udało się zapisać rezerwacji.';
            $state['start_time'] = '';
            $_SESSION['booking'] = $state;
            flash_set('alert', $error);
            $this->redirect('/?page=booking&step=time');
        }

        flash_set('alert', 'Nieznana akcja formularza.');
        $this->redirect('/?page=booking&step=' . $step);
    }

    // Zwraca pierwszy krok, na którym brakuje poprawnych danych.
    private function gate(PDO $pdo, string $step, array $state): ?array
    {
        if ($step === 'service') {
            return null;
        }

        if ($state['service_id'] < 1) {
            return ['step' => 'service', 'message' => 'Najpierw wybierz usługę.'];
        }

        $service = (new Service($pdo))->findActive($state['service_id']);
        if ($service === null) {
            return ['step' => 'service', 'message' => 'Wybrana usługa jest niedostępna.'];
        }

        if ($step === 'employee') {
            return null;
        }

        $employees = (new Employee($pdo))->forService((int) $service['id']);
        $employeeIds = array_map('intval', array_column($employees, 'id'));

        if ($state['employee_id'] < 1 || !in_array($state['employee_id'], $employeeIds, true)) {
            return ['step' => 'employee', 'message' => 'Wybierz pracownika.'];
        }

        if ($step === 'date') {
            return null;
        }

        $date = $state['date'];
        $parsed = DateTime::createFromFormat('Y-m-d', $date);

        if ($date === '' || $parsed === false || $parsed->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
            return ['step' => 'date', 'message' => 'Wybierz poprawną datę.'];
        }

        if ((new EmployeeAvailability($pdo))->isDayOff($state['employee_id'], $date)) {
            return ['step' => 'date', 'message' => 'Wybrany dzień jest dniem wolnym pracownika.'];
        }

        $slots = (new AvailabilityService($pdo))
            ->slotsFor($state['employee_id'], (int) $service['id'], $date);

        if ($slots === []) {
            return ['step' => 'date', 'message' => 'Brak wolnych terminów w tym dniu. Wybierz inny.'];
        }

        if ($step === 'time') {
            return null;
        }

        if (!in_array($state['start_time'], $slots, true)) {
            return ['step' => 'time', 'message' => 'Wybrany termin nie jest już dostępny.'];
        }

        return null;
    }

    private function render(PDO $pdo, string $step, array $state): void
    {
        $service = null;
        $employee = null;
        $employees = [];
        $services = [];
        $slots = [];

        if ($state['service_id'] > 0) {
            $service = (new Service($pdo))->findActive($state['service_id']);
        }

        if ($step === 'service') {
            $services = (new Service($pdo))->allActive();
        }

        if ($step !== 'service' && $service === null) {
            flash_set('alert', 'Wybrana usługa jest niedostępna.');
            $this->redirect('/?page=booking&step=service');
        }

        if ($step === 'employee') {
            $employees = (new Employee($pdo))->forService((int) $service['id']);
        }

        if (in_array($step, ['date', 'time', 'confirm'], true)) {
            $employees = (new Employee($pdo))->forService((int) $service['id']);

            foreach ($employees as $candidate) {
                if ((int) $candidate['id'] === (int) $state['employee_id']) {
                    $employee = $candidate;
                    break;
                }
            }

            if ($employee === null) {
                flash_set('alert', 'Wybierz pracownika.');
                $this->redirect('/?page=booking&step=employee');
            }
        }

        if ($step === 'time' || $step === 'confirm') {
            $slots = (new AvailabilityService($pdo))
                ->slotsFor((int) $employee['id'], (int) $service['id'], $state['date']);
        }

        $titles = [
            'service'  => 'Rezerwacja — wybór usługi',
            'employee' => 'Rezerwacja — wybór pracownika',
            'date'     => 'Rezerwacja — wybór daty',
            'time'     => 'Rezerwacja — wybór godziny',
            'confirm'  => 'Rezerwacja — potwierdzenie',
        ];

        $pageTitle = $titles[$step];
        $currentStep = $step;
        $booking = $state;

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/booking/' . $step . '.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    private function state(): array
    {
        $raw = $_SESSION['booking'] ?? [];
        if (!is_array($raw)) {
            $raw = [];
        }

        $state = $this->emptyState();

        if (isset($raw['service_id'])) {
            $state['service_id'] = max(0, (int) $raw['service_id']);
        }
        if (isset($raw['employee_id'])) {
            $state['employee_id'] = max(0, (int) $raw['employee_id']);
        }
        if (is_string($raw['date'] ?? null)) {
            $state['date'] = $raw['date'];
        }
        if (is_string($raw['start_time'] ?? null)) {
            $state['start_time'] = $raw['start_time'];
        }
        if (is_string($raw['comment'] ?? null)) {
            $state['comment'] = $raw['comment'];
        }

        return $state;
    }

    private function emptyState(): array
    {
        return [
            'service_id'  => 0,
            'employee_id' => 0,
            'date'        => '',
            'start_time'  => '',
            'comment'     => '',
        ];
    }

    private function clearFrom(array $state, string $step): array
    {
        if ($step === 'service') {
            return $this->emptyState();
        }

        if ($step === 'employee') {
            $state['employee_id'] = 0;
            $state['date'] = '';
            $state['start_time'] = '';
            $state['comment'] = '';
        }

        if ($step === 'date') {
            $state['date'] = '';
            $state['start_time'] = '';
            $state['comment'] = '';
        }

        if ($step === 'time') {
            $state['start_time'] = '';
            $state['comment'] = '';
        }

        return $state;
    }

    private function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}
