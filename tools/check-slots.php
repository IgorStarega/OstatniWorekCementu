<?php
// =========================================================================
// Weryfikacja silnika dostępnych terminów na danych testowych (#86).
//
// Uruchomienie (stos docker compose musi działać, baza z danymi testowymi):
//   docker compose exec -T app php tools/check-slots.php
//
// Skrypt sprawdza charakterystyczne przypadki z bazy testowej:
// okna pracowników, długie usługi (480 min = dokładnie jeden slot),
// przerwy Adama (styk z przerwą dozwolony, nakładanie odrzucone),
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

function checkContains(string $label, string $needle, array $actual): void
{
    check($label, [$needle], array_values(array_intersect([$needle], $actual)));
}

function checkNotContains(string $label, string $needle, array $actual): void
{
    check($label, [], array_values(array_intersect([$needle], $actual)));
}

// Najbliższy poniedziałek/wtorek jako daty testowe. Gdyby urlop Jana wypadł
// akurat w ten poniedziałek, przesuwamy test o tydzień — inaczej asercje
// o slotach pracy przechodziłyby z niewłaściwego powodu.
$monday = date('Y-m-d', strtotime('next monday'));
if ($dayOff !== null && $monday === $dayOff) {
    $monday = date('Y-m-d', strtotime($monday . ' +7 days'));
}
$tuesday = date('Y-m-d', strtotime('next tuesday'));
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Jan nie ma przerw. Usługa 1 (480 min) w oknie 08:00-16:00 ma dokładnie
// jeden slot — całe okno wypełnia jedna wizyta.
check(
    'Jan, usługa 1 (480 min), poniedziałek: dokładnie slot 08:00',
    ['08:00'],
    $availability->slotsFor(1, 1, $monday)
);

// Jan, usługa 2 (360 min), okno 08:00-16:00: start + 360 <= 960,
// więc ostatni start to 10:00, a przy siatce 15 min jest 9 slotów.
$janService2 = $availability->slotsFor(1, 2, $monday);
check(
    'Jan, usługa 2 (360 min), poniedziałek: pierwszy slot 08:00',
    ['08:00'],
    [$janService2[0] ?? '']
);
check(
    'Jan, usługa 2 (360 min), poniedziałek: ostatni slot 10:00',
    ['10:00'],
    [end($janService2) ?: '']
);
check(
    'Jan, usługa 2 (360 min), poniedziałek: 9 slotów co 15 min',
    [9],
    [count($janService2)]
);

// Adam ma przerwę 12:00-12:30. Usługa 5 (240 min) startuje tylko o 08:00:
// 08:00-12:00 kończy się dokładnie na początku przerwy (styk dozwolony),
// a każdy późniejszy start albo przecina przerwę, albo wychodzi po 16:00.
$adamService5 = $availability->slotsFor(3, 5, $monday);
checkContains(
    'Adam, usługa 5 (240 min), poniedziałek: 08:00 jest wolne',
    '08:00',
    $adamService5
);
checkNotContains(
    'Adam, usługa 5 (240 min), poniedziałek: 12:00 wypada przez przerwę',
    '12:00',
    $adamService5
);
check(
    'Adam, usługa 5 (240 min), poniedziałek: dokładnie slot 08:00',
    ['08:00'],
    $adamService5
);

// Adam, usługa 6 (180 min): slot 09:00 kończy się o 12:00, czyli dokładnie
// na początku przerwy, więc jest dopuszczony. Start 10:30 (13:30) przecina
// przerwę i nie może się pojawić.
$adamService6 = $availability->slotsFor(3, 6, $monday);
checkContains(
    'Adam, usługa 6 (180 min), poniedziałek: 09:00 kończy się na styku z przerwą',
    '09:00',
    $adamService6
);
checkNotContains(
    'Adam, usługa 6 (180 min), poniedziałek: 10:30 przecina przerwę',
    '10:30',
    $adamService6
);
check(
    'Adam, usługa 6 (180 min), poniedziałek: pełna lista wokół przerwy',
    ['08:00', '08:15', '08:30', '08:45', '09:00', '12:30', '12:45', '13:00'],
    $adamService6
);

// Adam pracuje tylko pon/śr/pt — we wtorek nie ma okna, więc brak slotów.
check(
    'Adam, usługa 5, wtorek (brak okna): brak slotów',
    [],
    $availability->slotsFor(3, 5, $tuesday)
);

// Data przeszła nigdy nie ma slotów.
check(
    'Jan, usługa 1, wczoraj: brak slotów',
    [],
    $availability->slotsFor(1, 1, $yesterday)
);

// Dzień wolny blokuje cały dzień mimo istniejącego okna tygodnia.
if ($dayOff !== null) {
    check(
        "Jan, usługa 1, dzień wolny ($dayOff): brak slotów",
        [],
        $availability->slotsFor(1, 1, $dayOff)
    );
} else {
    printf("SKIP dzień wolny Jana — tabela employee_days_off jest pusta\n");
}

printf("\n%d/%d PASS, %d FAIL\n", $pass, $pass + $fail, $fail);
exit($fail > 0 ? 1 : 0);
