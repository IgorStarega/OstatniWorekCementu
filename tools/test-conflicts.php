<?php
// =========================================================================
// Reguła nakładania rezerwacji (#87) — 7 asercji na hasConflict().
//
// Uruchomienie (stos docker compose musi działać, baza z danymi testowymi):
//   docker compose exec -T app php tools/test-conflicts.php
//
// Konflikt sprawdzamy wyłącznie przez hasConflict(), nigdy przez INSERT:
// UNIQUE uq_employee_slot wywaliłby przy identycznym starcie i zamaskował
// wynik. Skrypt tworzy własną rezerwację testową (pracownik 1, +30 dni,
// 10:00-11:00), sprawdza 5 blokad i 2 dopuszczenia z wymagań, na koniec
// zawsze ją usuwa. Idempotentny: usuwa też ślad po przerwanym przebiegu.
// Wyjdzie z kodem 1 przy jakimkolwiek FAIL.
// =========================================================================

require dirname(__DIR__) . '/app/models/Reservation.php';

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

// Data testowa (+30 dni) — daleko od danych seedowych, więc nic innego
// nie wpływa na wynik asercji.
$date = date('Y-m-d', strtotime('+30 days'));

// Idempotencja: po przerwanym przebiegu mogła zostać rezerwacja testowa
// o identycznym kluczu (pracownik 1 + data + start 10:00).
$statement = $pdo->prepare(
    "DELETE FROM reservations
     WHERE employee_id = 1 AND reservation_date = :date AND start_time = '10:00:00'"
);
$statement->execute(['date' => $date]);

$statement = $pdo->prepare(
    "INSERT INTO reservations
        (user_id, employee_id, service_id, final_price, reservation_date,
         start_time, end_time, status, comment)
     VALUES (5, 1, 1, 1200.00, :date, '10:00:00', '11:00:00', 'oczekująca',
             'rezerwacja testowa test-conflicts.php')"
);
$statement->execute(['date' => $date]);
$testId = (int) $pdo->lastInsertId();

$pass = 0;
$fail = 0;

// Wypisuje PASS/FAIL i liczy wyniki; $expected to wartość hasConflict().
$assert = function (string $label, bool $expected, bool $actual) use (&$pass, &$fail): void {
    if ($expected === $actual) {
        printf("PASS %s\n", $label);
        $pass++;
    } else {
        printf(
            "FAIL %s — oczekiwano %s, otrzymano %s\n",
            $label,
            $expected ? 'true' : 'false',
            $actual ? 'true' : 'false'
        );
        $fail++;
    }
};

$reservations = new Reservation($pdo);

try {
    // BLOKADA: przedziały zachodzą na rezerwację 10:00-11:00.
    $assert('blokada 09:30-10:30', true, $reservations->hasConflict(1, $date, '09:30', '10:30'));
    $assert('blokada 10:00-11:00 (identyczny przedział)', true, $reservations->hasConflict(1, $date, '10:00', '11:00'));
    $assert('blokada 10:15-10:45 (w środku)', true, $reservations->hasConflict(1, $date, '10:15', '10:45'));
    $assert('blokada 10:30-11:30', true, $reservations->hasConflict(1, $date, '10:30', '11:30'));
    $assert('blokada 09:30-11:30 (obejmuje całość)', true, $reservations->hasConflict(1, $date, '09:30', '11:30'));

    // DOPUSZCZENIE: stykające się końce to nie nakładanie.
    $assert('dopuszczenie 09:00-10:00 (koniec = start)', false, $reservations->hasConflict(1, $date, '09:00', '10:00'));
    $assert('dopuszczenie 11:00-12:00 (start = koniec)', false, $reservations->hasConflict(1, $date, '11:00', '12:00'));

    // excludeId: przy edycji rezerwacja nie koliduje sama ze sobą.
    $assert(
        'excludeId — własna rezerwacja nie blokuje siebie',
        false,
        $reservations->hasConflict(1, $date, '10:00', '11:00', $testId)
    );
} finally {
    // Sprzątamy zawsze, także gdy któraś asercja rzuciła wyjątek.
    $statement = $pdo->prepare('DELETE FROM reservations WHERE id = :id');
    $statement->execute(['id' => $testId]);
}

printf("\n%d/%d PASS, %d FAIL\n", $pass, $pass + $fail, $fail);
exit($fail > 0 ? 1 : 0);
