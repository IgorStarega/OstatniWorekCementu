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
require dirname(__DIR__) . '/app/models/EmployeeBreak.php';
require dirname(__DIR__) . '/app/models/EmployeeDayOff.php';
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

// Jan ma przerwę 12:00-12:30, a usługa 2 trwa 6 h. W oknie 08:00-16:00
// każdy kandydat przechodzi przez przerwę, więc dzień nie ma slotów.
check(
    "Jan, usługa 2 (360 min), poniedziałek: przerwa blokuje cały dzień",
    [],
    $availability->slotsFor(1, 2, $monday)
);

// Piątek Jana kończy się o 14:00 — 6-godzinna usługa i tak przechodzi
// przez przerwę 12:00-12:30, więc slotów nie ma.
check(
    "Jan, usługa 2, piątek (okno do 14:00): brak slotów przez przerwę",
    [],
    $availability->slotsFor(1, 2, $friday)
);

// Adam pracuje tylko pon/śr/pt — we wtorek nie ma okna, więc brak slotów.
check(
    "Adam, usługa 5, wtorek (brak okna): brak slotów",
    [],
    $availability->slotsFor(3, 5, $tuesday)
);

// Piotr (przerwa 11:00-11:15), usługa 3 (240 min), poniedziałek 09:00-17:00:
// pierwszy slot po przerwie to 11:15, więc 11:00 nie może się pojawić.
$piotrSlots = $availability->slotsFor(2, 3, $monday);
check(
    "Piotr, usługa 3, poniedziałek: pierwszy slot po przerwie 11:15",
    ['11:15'],
    [$piotrSlots[0] ?? '']
);
check(
    "Piotr, usługa 3, poniedziałek: 11:00 wypada przez przerwę",
    [],
    array_values(array_intersect(['11:00'], $piotrSlots))
);

// Piotr, usługa 9 (480 min), poniedziałek: całe 8-godzinne okno przechodzi
// przez przerwę 11:00-11:15, więc nie ma żadnego slotu.
check(
    "Piotr, usługa 9 (480 min), poniedziałek: przerwa blokuje cały dzień",
    [],
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
