<?php
// =========================================================================
// Runner migracji SQL (PDO, bez Composera i zewnętrznych zależności).
//
// Uruchomienie:
//   php tools/migrate.php            — aplikuje brakujące migracje
//   php tools/migrate.php --status   — lista plików i ich stan w bazie
//   php tools/migrate.php --dry-run  — pokazuje, co zostałoby wykonane
// =========================================================================

declare(strict_types=1);

$statusOnly = in_array('--status', $argv, true);
$dryRun     = in_array('--dry-run', $argv, true);

$migrationsDir = dirname(__DIR__) . '/database/migrations';
$files = glob($migrationsDir . '/*.sql');

if ($files === false || $files === []) {
    fwrite(STDERR, "BŁĄD: brak plików migracji w {$migrationsDir}.\n");
    exit(1);
}

sort($files, SORT_STRING);

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '3306';
$name = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS');

if ($name === '' || $user === '' || $pass === false) {
    fwrite(STDERR, "BŁĄD: brak wymaganych zmiennych środowiskowych DB_NAME, DB_USER, DB_PASS.\n");
    exit(1);
}

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);

try {
    $pdo = new PDO($dsn, $user, (string) $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, 'BŁĄD połączenia z bazą danych: ' . $e->getMessage() . "\n");
    exit(1);
}

/**
 * Dzieli zawartość pliku SQL na pojedyncze instrukcje.
 * Respektuje literały w '...' i "..." (z podwojonymi cudzysłowami oraz backslashami),
 * komentarze liniowe (-- oraz #) i komentarze blokowe (/* ... * /).
 *
 * @return list<string>
 */
function splitSqlStatements(string $sql): array
{
    $statements = [];
    $current = '';
    $length = strlen($sql);
    $i = 0;

    while ($i < $length) {
        $ch = $sql[$i];
        $next = ($i + 1 < $length) ? $sql[$i + 1] : '';

        // Komentarz liniowy: -- (w MySQL po -- wymagany jest biały znak, koniec linii lub koniec pliku)
        if ($ch === '-' && $next === '-') {
            $after = ($i + 2 < $length) ? $sql[$i + 2] : '';
            if ($after === '' || $after === ' ' || $after === "\t" || $after === "\r" || $after === "\n") {
                $i += 2;
                while ($i < $length && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }
        }

        // Komentarz liniowy: #
        if ($ch === '#') {
            $i++;
            while ($i < $length && $sql[$i] !== "\n") {
                $i++;
            }
            continue;
        }

        // Komentarz blokowy: /* ... */
        if ($ch === '/' && $next === '*') {
            $i += 2;
            while ($i < $length && !($sql[$i] === '*' && ($i + 1 < $length) && $sql[$i + 1] === '/')) {
                $i++;
            }
            $i += 2;
            continue;
        }

        // Literały napisowe: '...' lub "..."
        if ($ch === "'" || $ch === '"') {
            $quote = $ch;
            $current .= $ch;
            $i++;

            while ($i < $length) {
                $c = $sql[$i];
                $current .= $c;
                $i++;

                if ($c === '\\') {
                    if ($i < $length) {
                        $current .= $sql[$i];
                        $i++;
                    }
                    continue;
                }

                if ($c === $quote) {
                    if ($i < $length && $sql[$i] === $quote) {
                        $current .= $sql[$i];
                        $i++;
                        continue;
                    }
                    break;
                }
            }
            continue;
        }

        // Koniec instrukcji
        if ($ch === ';') {
            $trimmed = trim($current);
            if ($trimmed !== '') {
                $statements[] = $trimmed;
            }
            $current = '';
            $i++;
            continue;
        }

        $current .= $ch;
        $i++;
    }

    $trimmed = trim($current);
    if ($trimmed !== '') {
        $statements[] = $trimmed;
    }

    return $statements;
}

try {
    if (!$statusOnly && !$dryRun) {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS `schema_migrations` (
                `version` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                `applied_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    $applied = [];
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'schema_migrations'")->fetchColumn();
    if ($tableCheck !== false) {
        $rows = $pdo->query('SELECT `version` FROM `schema_migrations`')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($rows as $version) {
            $applied[(string) $version] = true;
        }
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'BŁĄD odczytu tabeli schema_migrations: ' . $e->getMessage() . "\n");
    exit(1);
}

if ($statusOnly) {
    foreach ($files as $file) {
        $version = basename($file);
        $state = isset($applied[$version]) ? 'ZASTOSOWANA' : 'BRAK';
        fwrite(STDOUT, sprintf("%-35s %s\n", $version, $state));
    }
    exit(0);
}

$appliedCount = 0;
$insertStmt = (!$dryRun)
    ? $pdo->prepare('INSERT INTO `schema_migrations` (`version`) VALUES (:version)')
    : null;

foreach ($files as $file) {
    $version = basename($file);
    if (isset($applied[$version])) {
        continue;
    }

    $content = file_get_contents($file);
    if ($content === false) {
        fwrite(STDERR, "BŁĄD: nie można odczytać pliku {$version}.\n");
        exit(1);
    }

    $statements = splitSqlStatements($content);

    if ($dryRun) {
        fwrite(STDOUT, sprintf("[DRY-RUN] %s (%d instrukcji)\n", $version, count($statements)));
        $appliedCount++;
        continue;
    }

    try {
        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }
        $insertStmt->execute(['version' => $version]);
        $appliedCount++;
    } catch (Throwable $e) {
        fwrite(STDERR, sprintf("BŁĄD w migracji %s: %s\n", $version, $e->getMessage()));
        exit(1);
    }
}

fwrite(STDOUT, sprintf("OK: zastosowano %d migracji.\n", $appliedCount));
exit(0);
