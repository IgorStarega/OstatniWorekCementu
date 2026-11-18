<?php
// =========================================================================
// Weryfikacja silnika dostępnych terminów na danych testowych (#86).
//
// Uruchomienie (stos docker compose musi działać, baza z danymi testowymi):
//   docker compose exec -T app php tools/check-slots.php
//
// Skrypt sprawdza charakterystyczne przypadki z bazy testowej:
// okna pracowników, długie usługi (480 min = dokładnie jeden slot),
// dzień bez okna, datę przeszłą i dzień wolny. Wyjdzie z kodem 1,
// gdy którakolwiek asercja nie się zgodzi.
// =========================================================================

require dirname(__DIR__) . '/app/models/EmployeeAvailability.php';
require dirname(__DIR__) . '/app/services/AvailabilityService.php';

date_default_timezone_set('Europe/Warsaw');

// Połączenie jak w tools/migrate.php — przez zmienne środowiskowe kontenera.
$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '3306';
$name = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS');

if ($name === '' || $user === '' || $pass === false) {
    fwrite(STDERR, "BŁĄD: brak zmiennych środowiskowych DB_NAME, DB_USER, DB_PASS.\n");
    exit(1);
}

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name),
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (Throwable $e) {
    fwrite(STDERR, 'BŁĄD połączenia z bazą danych: ' . $e->getMessage() . "\n");
    exit(1);
}

$availability = new AvailabilityService($pdo);

// Dzień wolny Jana z danych testowych (CURDATE() + 7 przy imporcie).
$statement = $pdo->query(
    "SELECT date FROM employee_days_off WHERE employee_id = 1 ORDER BY date ASC LIMIT 1"
);
$dayOff = $statement->fetchColumn();
$dayOff = $dayOff === false ? null : (string) $dayOff;

$pass = 0;
$fail = 0;

// Wypisuje PASS/FAIL i liczy wyniki; porównanie na dokładnej liście slotów.
function check(string $label, array $expected, array $actual): void
{
    global $pass, $fail;

    if ($expected === $actual) {
        printf("PASS %s\n", $label);
        $pass++;
    } else {
        printf(
            "FAIL %s — oczekiwano [%s], otrzymano [%s]\n",
            $label,
            implode(', ', $expected),
            implode(', ', $actual)
        );
        $fail++;
    }
}

// Najbliższy poniedziałek/piątek/wtorek jako daty testowe.
$monday = date('Y-m-d', strtotime('next monday'));
$friday = date('Y-m-d', strtotime('next friday'));
$tuesday = date('Y-m-d', strtotime('next tuesday'));
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Jan (pracownik 1), usługa 2 (Tynkowanie, 360 min), poniedziałek 08:00-16:00:
// start musi zmieścić 6 godzin pracy, więc ostatni możliwy to 10:00.
check(
    "Jan, usługa 2, poniedziałek: ostatni slot 10:00",
    ['10:00'],
    [end($availability->slotsFor(1, 2, $monday)) ?: '']
);

// Piątek Jana kończy się o 14:00 — 6-hour usługa zmieści się tylko od 08:00.
check(
    "Jan, usługa 2, piątek (okno do 14:00): tylko slot 08:00",
    ['08:00'],
    $availability->slotsFor(1, 2, $friday)
);

// Adam pracuje tylko pon/śr/pt — we wtorek nie ma okna, więc brak slotów.
check(
    "Adam, usługa 5, wtorek (brak okna): brak slotów",
    [],
    $availability->slotsFor(3, 5, $tuesday)
);

// Piotr, usługa 9 (480 min), poniedziałek 09:00-17:00: osiem godzin pracy
// mieści się dokładnie raz — od 09:00. To celowe, a nie błąd.
check(
    "Piotr, usługa 9 (480 min), poniedziałek: dokładnie jeden slot 09:00",
    ['09:00'],
    $availability->slotsFor(2, 9, $monday)
);

// Data przeszła nigdy nie ma slotów.
check(
    'Jan, usługa 2, wczoraj: brak slotów',
    [],
    $availability->slotsFor(1, 2, $yesterday)
);

// Dzień wolny blokuje cały dzień mimo istniejącego okna tygodnia.
if ($dayOff !== null) {
    check(
        "Jan, usługa 2, dzień wolny ($dayOff): brak slotów",
        [],
        $availability->slotsFor(1, 2, $dayOff)
    );
} else {
    printf("SKIP dzień wolny Jana — tabela employee_days_off jest pusta\n");
}

printf("\n%d/%d PASS, %d FAIL\n", $pass, $pass + $fail, $fail);
exit($fail > 0 ? 1 : 0);
