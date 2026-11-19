<?php
// Endpoint JSON z wolnymi terminami. To drugi punkt wejścia do tej samej
// logiki co formularz rezerwacji — walidacja musi być identyczna, a nie
// uproszczona tylko dlatego, że odpowiedź jest czytana przez JavaScript.
class ApiController
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function slots(): void
    {
        // Nagłówek zawsze przed jakimkolwiek wyjściem — także przy błędzie.
        header('Content-Type: application/json; charset=utf-8');

        // Gość nie może dostać HTML-a przekierowania na logowanie: klient
        // JavaScript oczekuje JSON-a i na jego podstawie pokazuje komunikat.
        if (currentUserId() === null || currentUserRole() !== 'klient') {
            $this->respond(403, ['ok' => false, 'error' => 'Brak dostępu.']);
            return;
        }

        $employeeId = $this->getInt('employee_id');
        $serviceId = $this->getInt('service_id');
        $date = is_string($_GET['date'] ?? null) ? trim($_GET['date']) : '';

        try {
            $pdo = Database::connect($this->config['db']);

            $service = $serviceId > 0 ? (new Service($pdo))->findActive($serviceId) : null;
            if ($service === null) {
                $this->respond(422, ['ok' => false, 'error' => 'Usługa jest niedostępna.']);
                return;
            }

            $employeeBelongs = false;
            foreach ((new Employee($pdo))->forService($serviceId) as $employee) {
                if ((int) $employee['id'] === $employeeId) {
                    $employeeBelongs = true;
                    break;
                }
            }

            if (!$employeeBelongs) {
                $this->respond(422, ['ok' => false, 'error' => 'Wybrany pracownik nie wykonuje tej usługi.']);
                return;
            }

            // Data w obie strony: createFromFormat przyjmie 2026-02-31,
            // ale round-trip już nie, więc podwójna data odpada.
            $parsed = DateTime::createFromFormat('Y-m-d', $date);
            if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
                $this->respond(422, ['ok' => false, 'error' => 'Podaj poprawną datę w formacie RRRR-MM-DD.']);
                return;
            }

            if ($date < date('Y-m-d')) {
                $this->respond(422, ['ok' => false, 'error' => 'Data nie może być z przeszłości.']);
                return;
            }

            $slots = (new AvailabilityService($pdo))->slotsFor($employeeId, $serviceId, $date);

            $this->respond(200, [
                'ok'    => true,
                'date'  => $date,
                'slots' => $slots,
                'count' => count($slots),
            ]);
        } catch (PDOException $exception) {
            // Szczegóły wyłącznie do logu — klient nie może zobaczyć SQLSTATE.
            error_log('ApiController::slots: ' . $exception->getMessage());
            $this->respond(500, ['ok' => false, 'error' => 'Błąd serwera.']);
        }
    }

    // Cały JSON w jednej linii — bez JSON_PRETTY_PRINT.
    private function respond(int $status, array $payload): void
    {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // Parametr GET jako dodatnia liczba całkowita; tablice i śmieci dają 0.
    private function getInt(string $key): int
    {
        $value = $_GET[$key] ?? null;

        return is_string($value) && ctype_digit($value) ? (int) $value : 0;
    }
}
