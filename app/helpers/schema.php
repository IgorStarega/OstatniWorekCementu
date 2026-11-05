<?php
// Sprawdzanie schematu bazy — panel ma pokazać czytelny komunikat zamiast 500,
// gdy na istniejącej bazie nie uruchomiono jeszcze migracji 005–007.

// Zwraca nazwy tabel z podanej listy, których nie ma w bieżącej bazie.
function missingTables(PDO $pdo, array $tables): array
{
    $tables = array_values(array_unique(array_filter($tables, 'is_string')));
    if ($tables === []) {
        return [];
    }

    $placeholders = implode(', ', array_fill(0, count($tables), '?'));
    $statement = $pdo->prepare(
        "SELECT TABLE_NAME
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ($placeholders)"
    );
    $statement->execute($tables);

    $existing = array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN));

    return array_values(array_diff($tables, $existing));
}

// Wspólny, krótki komunikat dla administratora.
function missingTablesMessage(array $missing): string
{
    return 'Baza nie ma wymaganych tabel: ' . implode(', ', $missing)
        . '. Uruchom migracje: bash tools/migrate.sh.';
}
