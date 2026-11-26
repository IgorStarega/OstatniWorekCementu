-- =========================================================================
-- Migracja 009 — indeksy pod realne zapytania aplikacji (etap 5).
--
-- Co dodajemy i dlaczego:
--   * reservations (user_id, reservation_date) — panel klienta czyta wszystkie
--     rezerwacje użytkownika i sortuje/porównuje je po dacie. Dotychczasowe
--     osobne indeksy user_id i reservation_date wymagały skanowania jednego
--     z nich i filtrowania drugiego.
--   * services (category_id, is_active) — oferta i strona główna filtrują
--     jednocześnie po kategorii i aktywności; indeks złożony pokrywa oba
--     warunki bez zaglądania do wierszy nieaktywnych usług.
--
-- Czego świadomie NIE dodajemy:
--   * reservations (employee_id, reservation_date) — prefiks istniejącego
--     UNIQUE uq_employee_slot (employee_id, reservation_date, start_time)
--     obsługuje panel pracownika; drugi indeks byłby duplikatem.
--   * reservations (status) już istnieje, employee_availability
--     (employee_id, day_of_week) jest UNIQUE, a employee_services ma indeks
--     service_id — te zapytania są pokryte.
--
-- Idempotentna: każdy indeks dokładamy tylko wtedy, gdy nie istnieje.
-- Używamy DO 0 zamiast SELECT 1, bo runner (tools/migrate.php) wykonuje
-- instrukcje przez PDO::exec() i nie odczytuje resultsetów.
--
-- Uruchomienie (z katalogu repozytorium, stos docker compose musi działać):
--   docker compose exec -T app php tools/migrate.php
-- =========================================================================

SET NAMES utf8mb4;

-- 1) Panel klienta: rezerwacje użytkownika po dacie.
SET @idx_user_date_exists := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'reservations'
    AND INDEX_NAME = 'idx_reservations_user_date'
);

SET @idx_user_date_sql := IF(
  @idx_user_date_exists = 0,
  'CREATE INDEX `idx_reservations_user_date` ON `reservations` (`user_id`, `reservation_date`)',
  'DO 0'
);

PREPARE idx_user_date_stmt FROM @idx_user_date_sql;
EXECUTE idx_user_date_stmt;
DEALLOCATE PREPARE idx_user_date_stmt;

-- 2) Oferta i strona główna: aktywne usługi w kategorii.
SET @idx_service_category_exists := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'services'
    AND INDEX_NAME = 'idx_services_category_active'
);

SET @idx_service_category_sql := IF(
  @idx_service_category_exists = 0,
  'CREATE INDEX `idx_services_category_active` ON `services` (`category_id`, `is_active`)',
  'DO 0'
);

PREPARE idx_service_category_stmt FROM @idx_service_category_sql;
EXECUTE idx_service_category_stmt;
DEALLOCATE PREPARE idx_service_category_stmt;
