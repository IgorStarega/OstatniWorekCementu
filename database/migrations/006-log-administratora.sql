-- =========================================================================
-- Migracja 006 — log operacji administratora (etap 3, ocena 6).
--
-- Po co: panel ma zostawiać ślad po udanych operacjach create/update/delete/
-- toggle/role/status. W details zapisujemy krótki opis bez danych wrażliwych
-- (żadnych haseł, tokenów ani pełnych danych osobowych).
--
-- Idempotentna: tabelę zakładamy przez CREATE TABLE IF NOT EXISTS.
--
-- Uruchomienie (z katalogu repozytorium, stos docker compose musi działać):
--   docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/006-log-administratora.sql
-- =========================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `admin_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(32) NOT NULL,
  `entity` varchar(32) NOT NULL,
  `entity_id` int DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  KEY `entity` (`entity`, `entity_id`),
  CONSTRAINT `admin_log_ibfk_1` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
