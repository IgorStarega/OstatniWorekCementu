-- =========================================================================
-- Migracja 002 — zmiany schematu z etapu 2 dla ISTNIEJĄCYCH baz.
--
-- Po co: skrypty z docker-entrypoint-initdb.d (database/database.sql) wykonują
-- się tylko przy PUSTYM katalogu danych (data/mysql). Jeśli baza już istnieje,
-- nowe kolumny i tabele trzeba dołożyć ręcznie — właśnie tym plikiem.
--
-- Idempotentna: można uruchomić wiele razy, nic się nie zdubluje i nie wyrzuci
-- błędu 1060 (Duplicate column name). MySQL 8.0 NIE wspiera
-- "ALTER TABLE ... ADD COLUMN IF NOT EXISTS" (to rozszerzenie MariaDB), dlatego
-- przed każdym ALTER sprawdzamy information_schema.COLUMNS i wykonujemy ALTER
-- przez dynamiczny SQL (PREPARE/EXECUTE) tylko gdy kolumny brakuje.
-- Tabele zakładamy normalnie: CREATE TABLE IF NOT EXISTS.
--
-- Uruchomienie (z katalogu repozytorium, stos docker compose musi działać):
--   docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/002-etap2.sql
--
-- Zawartość:
--   1. users.failed_attempts  — licznik nieudanych logowań (limit prób)
--   2. users.locked_until     — czasowa blokada konta
--   3. tabela password_resets — jednorazowe tokeny resetu hasła (tylko hash SHA-256)
--
-- Tabela email_verifications (weryfikacja e-maila) nie istnieje jeszcze w schemacie
-- repozytorium — jej migracja trafi do kolejnego pliku (003-...) razem z funkcją.
-- =========================================================================

SET NAMES utf8mb4;

-- --- 1. users.failed_attempts ---------------------------------------------
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD COLUMN `failed_attempts` int NOT NULL DEFAULT 0 AFTER `is_active`',
        'DO 0'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'failed_attempts'
);
PREPARE migration_stmt FROM @sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

-- --- 2. users.locked_until ------------------------------------------------
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD COLUMN `locked_until` datetime DEFAULT NULL AFTER `failed_attempts`',
        'DO 0'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'locked_until'
);
PREPARE migration_stmt FROM @sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

-- --- 3. password_resets ---------------------------------------------------
-- Jednorazowe tokeny resetu hasła; w bazie trzymamy tylko ich hash SHA-256.
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
