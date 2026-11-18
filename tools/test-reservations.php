<?php
// =========================================================================
// Weryfikacja Reservation::create() (#88) na danych testowych.
//
// Uruchomienie (stos docker compose musi działać, baza z danymi testowymi):
//   docker compose exec -T app php tools/test-reservations.php
//
// Sprawdza cztery kryteria z zadania:
//   1. dwa RÓWNOLEGŁE create() o tym samym starcie — dokładnie jedno
//      kończy się ok=true, drugie dostaje komunikat o zajętym terminie
//      (równoległość przez dwa osobne procesy PHP w trybie "worker"),
//   2. create() z innym startem nachodzący na istniejącą rezerwację —
//      odrzucony przez hasConflict(), którego UNIQUE w bazie by nie złapał,
//   3. final_price zamrożony z ceny w chwili rezerwacji,
//   4. status_history z wpisem old_status = NULL.
// Skrypt jest idempotentny i sprząta po sobie (w bloku finally).
// Wyjdzie z kodem 1 przy jakimkolwiek FAIL.
// =========================================================================

// Tryb "worker": pojedyncze create() wywoływane równolegle z drugim
// procesem — każdy worker ma własne połączenie PDO, więc transakcje
// naprawdę стартują jednocześnie.
if (($argv[1] ?? '') === 'worker') {
    require dirname(__DIR__) . '/app/models/StatusHistory.php';
    require dirname(__DIR__) . '/app/models/Reservation.php';

    $date = $argv[2] ?? '';
    $startTime = $argv[3] ?? '';

    $pdo = testPdoConnection();
    $result = (new Reservation($pdo))->create(5, 2, 1, $date, $startTime, 'test-create worker');

    // Wynik do odczytania przez proces nadrzędny.
    echo ($result['ok'] ? 'ok id=' . $result['id'] : 'error ' . $result['error']) . "\n";
    exit(0);
}

require dirname(__DIR__) . '/app/models/StatusHistory.php';
require dirname(__DIR__) . '/app/models/Reservation.php';

date_default_timezone_set('Europe/Warsaw');

/**
 * Połączenie PDO przez zmienne środowiskowe (jak tools/migrate.php).
 */
function testPdoConnection(): PDO
{
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
        return new PDO(
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
}

$pdo = testPdoConnection();
$reservations = new Reservation($pdo);

$pass = 0;
$fail = 0;

$assert = function (string $label, bool $condition, string $details = '') use (&$pass, &$fail): void {
    if ($condition) {
        printf("PASS %s\n", $label);
        $pass++;
    } else {
        printf("FAIL %s%s\n", $label, $details !== '' ? ' — ' . $details : '');
        $fail++;
    }
};

// Daty testowe daleko od danych seedowych (+40 dni), pracownik 1, usługa 2.
$date = date('Y-m-d', strtotime('+40 days'));
$cleanup = function () use ($pdo): void {
    // Usuwa także ślad po przerwanym przebiegu (status_history kaskadowo).
    $statement = $pdo->prepare("DELETE FROM reservations WHERE comment LIKE 'test-create%'");
    $statement->execute();
};
$cleanup();

try {
    // --- 1. Dwa równoległe create() o tym samym starcie -------------------
    // Obaj pracownicy procesów startują natychmiast; wygrywa dokładnie jeden.
    $command = sprintf('php %s worker %s 08:00', escapeshellarg(__FILE__), escapeshellarg($date));
    $descriptors = [1 => ['pipe', 'w']];
    $workerA = proc_open($command, $descriptors, $pipesA);
    $workerB = proc_open($command, $descriptors, $pipesB);

    $outputA = is_array($pipesA) ? trim((string) stream_get_contents($pipesA[1])) : '';
    $outputB = is_array($pipesB) ? trim((string) stream_get_contents($pipesB[1])) : '';

    foreach ([$pipesA, $pipesB] as $pipes) {
        if (is_array($pipes)) {
            fclose($pipes[1]);
        }
    }
    if (is_resource($workerA)) {
        proc_close($workerA);
    }
    if (is_resource($workerB)) {
        proc_close($workerB);
    }

    $okCount = ($outputA !== '' && str_starts_with($outputA, 'ok')) + ($outputB !== '' && str_starts_with($outputB, 'ok'));
    $conflictCount = ($outputA !== '' && str_contains($outputA, 'zajęty')) + ($outputB !== '' && str_contains($outputB, 'zajęty'));

    $assert(
        'równoległe create() — dokładnie jedno ok=true',
        $okCount === 1 && $conflictCount === 1,
        sprintf('worker A: "%s", worker B: "%s"', $outputA, $outputB)
    );

    // --- 2. Nakładanie z innym startem (UNIQUE by tego nie złapał) --------
    // Istnieje już rezerwacja 08:00-14:00 (usługa 2, 360 min); start 10:30
    // to inny start_time, więc uq_employee_slot milczy — blokuje dopiero
    // reguła hasConflict().
    $overlap = $reservations->create(5, 2, 1, $date, '10:30', 'test-create overlap');

    $assert(
        'create() nachodzący na istniejącą rezerwację — ok=false',
        $overlap['ok'] === false && str_contains((string) $overlap['error'], 'zajęty'),
        sprintf('ok=%s error="%s"', var_export($overlap['ok'], true), $overlap['error'])
    );

    // --- 3. final_price zamrożony z ceny w chwili rezerwacji --------------
    $frozen = $reservations->create(5, 2, 1, date('Y-m-d', strtotime('+41 days')), '08:00', 'test-create freeze');
    $assert('create() dla testu ceny — ok=true', $frozen['ok'] === true, (string) $frozen['error']);

    if ($frozen['ok']) {
        $statement = $pdo->prepare('SELECT final_price FROM reservations WHERE id = :id');
        $statement->execute(['id' => $frozen['id']]);
        $priceBefore = (string) $statement->fetchColumn();

        // Zmiana cennika PO rezerwacji nie może zmienić final_price.
        $pdo->prepare('UPDATE services SET price = 9999.00 WHERE id = 2')->execute();
        $statement = $pdo->prepare('SELECT final_price FROM reservations WHERE id = :id');
        $statement->execute(['id' => $frozen['id']]);
        $priceAfter = (string) $statement->fetchColumn();
        $pdo->prepare('UPDATE services SET price = 900.00 WHERE id = 2')->execute();

        $assert(
            'final_price niezależny od późniejszej zmiany ceny usługi',
            $priceBefore === $priceAfter && $priceBefore === '900.00',
            sprintf('przed=%s po=%s', $priceBefore, $priceAfter)
        );

        // --- 4. Historia statusu z old_status = NULL ----------------------
        $statement = $pdo->prepare(
            "SELECT old_status, new_status FROM status_history WHERE reservation_id = :id"
        );
        $statement->execute(['id' => $frozen['id']]);
        $history = $statement->fetch();

        $assert(
            'status_history: wpis z old_status = NULL i statusem oczekująca',
            $history !== false
                && $history['old_status'] === null
                && $history['new_status'] === 'oczekująca',
            $history === false ? 'brak wpisu' : sprintf('old=%s new=%s', var_export($history['old_status'], true), $history['new_status'])
        );
    } else {
        $assert('final_price zamrożony (pominięto — create nie wyszedł)', false);
        $assert('status_history old_status=NULL (pominięto — create nie wyszedł)', false);
    }
} finally {
    $cleanup();
}

printf("\n%d/%d PASS, %d FAIL\n", $pass, $pass + $fail, $fail);
exit($fail > 0 ? 1 : 0);
