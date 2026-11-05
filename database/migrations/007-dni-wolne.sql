-- =========================================================================
-- Migracja 007 — dni wolne i urlopy pracowników (etap 3, ocena 6).
--
-- Po co: tygodniowa dostępność (employee_availability) nie wystarcza, gdy
-- pracownik bierze urlop w konkretnym dniu. UNIQUE (employee_id, date)
-- blokuje dwa wpisy na ten sam dzień.
--
-- Idempotentna: tabelę zakładamy przez CREATE TABLE IF NOT EXISTS.
--
-- Uruchomienie (z katalogu repozytorium, stos docker compose musi działać):
--   docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/007-dni-wolne.sql
-- =========================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_days_off` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `date` date NOT NULL,
  `reason` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_employee_date` (`employee_id`, `date`),
  KEY `date` (`date`),
  CONSTRAINT `employee_days_off_ibfk_1` FOREIGN KEY (`employee_id`)
    REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
