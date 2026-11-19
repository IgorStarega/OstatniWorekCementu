-- =========================================================================
-- Migracja 008 — przerwy w godzinach pracy pracownika (etap 4, ocena 6).
--
-- Po co: okno pracy (employee_availability) mówi tylko, od kiedy do kiedy
-- pracownik jest dostępny. Przerwa na obiad nie jest osobnym oknem, bo
-- UNIQUE (employee_id, day_of_week) blokuje drugi wiersz na ten sam dzień.
-- Dlatego przerwy trzymamy w osobnej tabeli, a AvailabilityService odejmuje
-- je z listy wolnych slotów.
--
-- Idempotentna: tabela przez CREATE TABLE IF NOT EXISTS, indeks dokładamy
-- tylko wtedy, gdy nie istnieje (information_schema.STATISTICS).
--
-- Uruchomienie (z katalogu repozytorium, stos docker compose musi działać):
--   docker compose exec -T app php tools/migrate.php
-- =========================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_breaks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `day_of_week` tinyint NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `label` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_break_employee` FOREIGN KEY (`employee_id`)
    REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_break_time` CHECK (`start_time` < `end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @break_index_exists := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'employee_breaks'
    AND INDEX_NAME = 'idx_break_employee_day'
);

SET @break_index_sql := IF(
  @break_index_exists = 0,
  'CREATE INDEX `idx_break_employee_day` ON `employee_breaks` (`employee_id`, `day_of_week`)',
  'SELECT 1'
);

PREPARE break_index_stmt FROM @break_index_sql;
EXECUTE break_index_stmt;
DEALLOCATE PREPARE break_index_stmt;
