-- =========================================================================
-- Migracja 003 — weryfikacja adresu e-mail (etap 2) dla ISTNIEJĄCYCH baz.
--
-- Po co: skrypty z docker-entrypoint-initdb.d (database/database.sql) wykonują
-- się tylko przy PUSTYM katalogu danych (data/mysql). Jeśli baza już istnieje,
-- trzeba dołożyć zmiany ręcznie — właśnie tym plikiem.
--
-- Idempotentna: sprawdza information_schema przed ALTER TABLE, a tabele zakłada
-- przez CREATE TABLE IF NOT EXISTS (MySQL 8.0 nie ma ADD COLUMN IF NOT EXISTS).
--
-- Uruchomienie (z katalogu repozytorium, stos docker compose musi działać):
--   docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/003-etap2.sql
--
-- Zawartość:
--   1. users.email_verified_at     — data potwierdzenia adresu (NULL = brak)
--   2. tabela email_verifications  — jednorazowe tokeny (tylko hash SHA-256)
--   3. tabela email_sends          — dziennik wysyłek do limitów (cooldown, maks./h)
--   4. oznaczenie istniejących kont jako zweryfikowanych (nie psuje QA/demo)
-- =========================================================================

SET NAMES utf8mb4;

-- --- 1. users.email_verified_at -------------------------------------------
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `users` ADD COLUMN `email_verified_at` datetime DEFAULT NULL AFTER `locked_until`',
        'DO 0'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email_verified_at'
);
PREPARE migration_stmt FROM @sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

-- --- 2. email_verifications -----------------------------------------------
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- 3. email_sends --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_sends` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email_purpose_created` (`email`,`purpose`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- 4. istniejące konta uznajemy za potwierdzone -------------------------
-- Bez tego po włączeniu blokady logowania (polityka B) nikt nie mógłby się
-- zalogować na bazie, która powstała przed tą migracją.
UPDATE `users` SET `email_verified_at` = NOW() WHERE `email_verified_at` IS NULL;
