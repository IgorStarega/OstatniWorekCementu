<?php
// Proces rezerwacji klienta: usługa -> pracownik -> data -> godzina -> potwierdzenie.
// Stan kroków trzymamy w sesji, ale każde przejście jest walidowane od nowa
// po stronie serwera — samo ?page=booking&step=confirm nie może nic zapisać.
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
            $this->redirectStep('service');
        }

        // Wejście z karty usługi: ?page=booking&step=service&service_id=X
        // ustawia usługę i przechodzi od razu do wyboru pracownika.
        if ($step === 'service' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $preselect = filter_input(INPUT_GET, 'service_id', FILTER_VALIDATE_INT);
            if ($preselect !== false && $preselect > 0
                && (new Service($pdo))->findActive($preselect) !== null) {
                $_SESSION['booking'] = [
                    'service_id'  => $preselect,
                    'employee_id' => 0,
                    'date'        => '',
                    'start_time'  => '',
                    'comment'     => '',
                ];
                $this->redirectStep('employee');
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost($pdo, $step);
            return;
        }

        // "Wstecz" czyści właściwy klucz (i wszystkie późniejsze), żeby
        // cofnięcie naprawdę cofało wybór, a nie tylko wyświetlało krok.
        if (isset($_GET['back'])) {
            $_SESSION['booking'] = $this->clearFrom($this->state(), $step);
            $this->redirectStep($step);
        }

        $state = $this->state();
        $missing = $this->firstMissing($step, $state);
        if ($missing !== null) {
            $this->redirectStep($missing);
        }

        $this->render($pdo, $step, $state);
    }

    private function handlePost(PDO $pdo, string $step): void
    {
        csrf_verify(postString('csrf_token'));

        $state = $this->state();
        $missing = $this->firstMissing($step, $state);
        if ($missing !== null) {
            $this->redirectStep($missing);
        }

        if ($step === 'service') {
            $serviceId = (int) postString('service_id');
            $service = (new Service($pdo))->findActive($serviceId);

            if ($service === null) {
                $this->render($pdo, 'service', $state, ['Usługa jest niedostępna.']);
                return;
            }

            $_SESSION['booking'] = [
                'service_id'  => (int) $service['id'],
                'employee_id' => 0,
                'date'        => '',
                'start_time'  => '',
                'comment'     => '',
            ];
            $this->redirectStep('employee');
        }

        if ($step === 'employee') {
            $service = (new Service($pdo))->findActive($state['service_id']);
            if ($service === null) {
                $_SESSION['booking'] = $this->emptyState();
                flash_set('alert', 'Usługa jest niedostępna.');
                $this->redirectStep('service');
            }

            $employees = (new Employee($pdo))->forService((int) $service['id']);
            if ($employees === []) {
                $this->render($pdo, 'employee', $state, ['Ta usługa nie ma obecnie dostępnego pracownika.']);
                return;
            }

            $employeeId = (int) postString('employee_id');
            if (!$this->employeeBelongs($employees, $employeeId)) {
                $this->render($pdo, 'employee', $state, ['Wybierz pracownika z listy.']);
                return;
            }

            $state['employee_id'] = $employeeId;
            $state['date'] = '';
            $state['start_time'] = '';
            $_SESSION['booking'] = $state;
            $this->redirectStep('date');
        }

        if ($step === 'date') {
            $service = (new Service($pdo))->findActive($state['service_id']);
            if ($service === null) {
                $_SESSION['booking'] = $this->emptyState();
                flash_set('alert', 'Usługa jest niedostępna.');
                $this->redirectStep('service');
            }

            $employee = $this->employeeForService($pdo, (int) $service['id'], $state['employee_id']);
            if ($employee === null) {
                $state['employee_id'] = 0;
                $state['date'] = '';
                $state['start_time'] = '';
                $_SESSION['booking'] = $state;
                flash_set('alert', 'Wybrany pracownik nie wykonuje już tej usługi.');
                $this->redirectStep('employee');
            }

            $date = trim(postString('date'));
            $parsed = DateTime::createFromFormat('Y-m-d', $date);
            $dateValid = $parsed !== false && $parsed->format('Y-m-d') === $date;

            if (!$dateValid || $date < date('Y-m-d')) {
                $this->render($pdo, 'date', $state, ['Podaj poprawną datę (dzisiejszą lub późniejszą).']);
                return;
            }

            $slots = (new AvailabilityService($pdo))
                ->slotsFor((int) $employee['id'], (int) $service['id'], $date);

            if ($slots === []) {
                $this->render($pdo, 'date', $state, ['Brak wolnych terminów w tym dniu, wybierz inny.']);
                return;
            }

            $state['date'] = $date;
            $state['start_time'] = '';
            $_SESSION['booking'] = $state;
            $this->redirectStep('time');
        }

        if ($step === 'time') {
            $service = (new Service($pdo))->findActive($state['service_id']);
            if ($service === null) {
                $_SESSION['booking'] = $this->emptyState();
                flash_set('alert', 'Usługa jest niedostępna.');
                $this->redirectStep('service');
            }

            $employee = $this->employeeForService($pdo, (int) $service['id'], $state['employee_id']);
            if ($employee === null) {
                $state['employee_id'] = 0;
                $state['date'] = '';
                $state['start_time'] = '';
                $_SESSION['booking'] = $state;
                flash_set('alert', 'Wybrany pracownik nie wykonuje już tej usługi.');
                $this->redirectStep('employee');
            }

            $slots = (new AvailabilityService($pdo))
                ->slotsFor((int) $employee['id'], (int) $service['id'], $state['date']);

            if ($slots === []) {
                $state['date'] = '';
                $state['start_time'] = '';
                $_SESSION['booking'] = $state;
                flash_set('alert', 'Brak wolnych terminów w tym dniu, wybierz inny.');
                $this->redirectStep('date');
            }

            $startTime = postString('start_time');
            if (!in_array($startTime, $slots, true)) {
                $this->render($pdo, 'time', $state, ['Wybierz godzinę z listy dostępnych terminów.']);
                return;
            }

            $state['start_time'] = $startTime;
            $_SESSION['booking'] = $state;
            $this->redirectStep('confirm');
        }

        if ($step === 'confirm') {
            $service = (new Service($pdo))->findActive($state['service_id']);
            if ($service === null) {
                $_SESSION['booking'] = $this->emptyState();
                flash_set('alert', 'Usługa jest niedostępna.');
                $this->redirectStep('service');
            }

            $employee = $this->employeeForService($pdo, (int) $service['id'], $state['employee_id']);
            if ($employee === null) {
                $state['employee_id'] = 0;
                $state['date'] = '';
                $state['start_time'] = '';
                $_SESSION['booking'] = $state;
                flash_set('alert', 'Wybrany pracownik nie wykonuje już tej usługi.');
                $this->redirectStep('employee');
            }

            // Potwierdzenie liczy wszystko od nowa: usługa może zostać
            // dezaktywowana, a termin zajęty między krokiem godziny i zapisem.
            $slots = (new AvailabilityService($pdo))
                ->slotsFor((int) $employee['id'], (int) $service['id'], $state['date']);

            if ($slots === [] || !in_array($state['start_time'], $slots, true)) {
                $state['start_time'] = '';
                $_SESSION['booking'] = $state;
                flash_set('alert', 'Wybrany termin nie jest już dostępny. Wybierz inny.');
                $this->redirectStep('time');
            }

            $comment = trim(postString('comment'));
            if (mb_strlen($comment) > 500) {
                flash_set('alert', 'Komentarz może mieć maksymalnie 500 znaków.');
                $this->redirectStep('confirm');
            }

            $state['comment'] = $comment;
            $_SESSION['booking'] = $state;

            $result = (new Reservation($pdo))->create(
                (int) currentUserId(),
                (int) $service['id'],
                (int) $employee['id'],
                $state['date'],
                $state['start_time'],
                $comment
            );

            if (($result['ok'] ?? false) !== true) {
                $error = is_string($result['error'] ?? null)
                    ? $result['error']
                    : 'Nie udało się zapisać rezerwacji.';
                flash_set('alert', $error);
                $this->redirectStep('time');
            }

            unset($_SESSION['booking']);
            flash_set('status', 'Rezerwacja #' . (int) $result['id'] . ' została zapisana.');

            header('Location: /?page=my-reservations');
            exit;
        }
    }

    private function render(PDO $pdo, string $step, array $state, array $errors = []): void
    {
        $service = null;
        $employee = null;
        $employees = [];
        $slots = [];
        $services = [];

        if ($state['service_id'] > 0) {
            $service = (new Service($pdo))->findActive($state['service_id']);
        }

        if ($step !== 'service' && $service === null) {
            $_SESSION['booking'] = $this->emptyState();
            flash_set('alert', 'Usługa jest niedostępna.');
            $this->redirectStep('service');
        }

        if ($step === 'service') {
            $services = (new Service($pdo))->allActive();
        }

        if ($step === 'employee') {
            $employees = (new Employee($pdo))->forService((int) $service['id']);
        }

        if (in_array($step, ['date', 'time', 'confirm'], true)) {
            $employees = (new Employee($pdo))->forService((int) $service['id']);
            $employee = $this->employeeForService($pdo, (int) $service['id'], $state['employee_id']);

            if ($employee === null) {
                $state['employee_id'] = 0;
                $state['date'] = '';
                $state['start_time'] = '';
                $_SESSION['booking'] = $state;
                flash_set('alert', 'Wybrany pracownik nie wykonuje już tej usługi.');
                $this->redirectStep('employee');
            }
        }

        if ($step === 'time') {
            $slots = (new AvailabilityService($pdo))
                ->slotsFor((int) $employee['id'], (int) $service['id'], $state['date']);

            if ($slots === []) {
                $state['date'] = '';
                $state['start_time'] = '';
                $_SESSION['booking'] = $state;
                flash_set('alert', 'Brak wolnych terminów w tym dniu, wybierz inny.');
                $this->redirectStep('date');
            }
        }

        if ($step === 'confirm') {
            $slots = (new AvailabilityService($pdo))
                ->slotsFor((int) $employee['id'], (int) $service['id'], $state['date']);

            if ($slots === [] || !in_array($state['start_time'], $slots, true)) {
                $state['start_time'] = '';
                $_SESSION['booking'] = $state;
                flash_set('alert', 'Wybrany termin nie jest już dostępny. Wybierz inny.');
                $this->redirectStep('time');
            }
        }

        $titles = [
            'service'  => 'Rezerwacja — wybór usługi',
            'employee' => 'Rezerwacja — wybór pracownika',
            'date'     => 'Rezerwacja — wybór daty',
            'time'     => 'Rezerwacja — wybór godziny',
            'confirm'  => 'Rezerwacja — potwierdzenie',
        ];

        $pageTitle = $titles[$step];
        $pageClass = 'booking-main';
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

    // Kolejność jest twarda: nie da się przeskoczyć kroku, którego brakuje.
    private function firstMissing(string $step, array $state): ?string
    {
        if ($step === 'service') {
            return null;
        }
        if ($state['service_id'] < 1) {
            return 'service';
        }

        if ($step === 'employee') {
            return null;
        }
        if ($state['employee_id'] < 1) {
            return 'employee';
        }

        if ($step === 'date') {
            return null;
        }
        if ($state['date'] === '') {
            return 'date';
        }

        if ($step === 'time') {
            return null;
        }
        if ($state['start_time'] === '') {
            return 'time';
        }

        return null;
    }

    // Cofnięcie do kroku czyści jego wartość i wszystkie późniejsze.
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

    private function employeeBelongs(array $employees, int $employeeId): bool
    {
        foreach ($employees as $employee) {
            if ((int) $employee['id'] === $employeeId) {
                return true;
            }
        }

        return false;
    }

    private function employeeForService(PDO $pdo, int $serviceId, int $employeeId): ?array
    {
        foreach ((new Employee($pdo))->forService($serviceId) as $employee) {
            if ((int) $employee['id'] === $employeeId) {
                return $employee;
            }
        }

        return null;
    }

    private function redirectStep(string $step): void
    {
        header('Location: /?page=booking&step=' . $step);
        exit;
    }
}
