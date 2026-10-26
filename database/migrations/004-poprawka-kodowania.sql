-- =========================================================================
-- Migracja 004 — naprawa podwójnego kodowania polskich znaków w danych.
--
-- Problem: plik z danymi (database-tylko-rekordy.sql) był doklejany po
-- strukturze, która przywraca character_set_client z połączenia (zwykle
-- latin1). W efekcie polskie znaki z INSERT-ów zapisały się podwójnie
-- (np. "Ocieplanie budynkÃ³w" zamiast "Ocieplanie budynków").
--
-- Naprawa: MySQL-owe latin1 odpowiada CP1252, więc rzutowanie tekstu na
-- latin1 i ponowne zinterpretowanie bajtów jako utf8mb4 odwraca podwójne
-- kodowanie. Każdą kolumnę naprawiamy osobno i tylko wtedy, gdy sama
-- zawiera marker podwójnego kodowania. Warunek używa BINARY, bo zwykłe
-- porównanie w utf8mb4_unicode_ci jest niewrażliwe na znaki diakrytyczne
-- (np. "Å" = "A") i zmieniłoby poprawne dane.
--
-- Idempotentna: po naprawie kolumna nie zawiera już markerów, więc kolejne
-- uruchomienie nic nie zmienia.
--
-- Uruchomienie (z katalogu repozytorium, stos docker compose musi działać):
--   docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/004-poprawka-kodowania.sql
-- =========================================================================

SET NAMES utf8mb4;

UPDATE `service_categories`
SET `name` = CONVERT(BINARY(CONVERT(`name` USING latin1)) USING utf8mb4)
WHERE BINARY `name` LIKE '%Ã%' OR BINARY `name` LIKE '%Å%' OR BINARY `name` LIKE '%Ä%'
   OR BINARY `name` LIKE '%Ĺ%' OR BINARY `name` LIKE '%Ă%';

UPDATE `service_categories`
SET `description` = CONVERT(BINARY(CONVERT(`description` USING latin1)) USING utf8mb4)
WHERE BINARY `description` LIKE '%Ã%' OR BINARY `description` LIKE '%Å%' OR BINARY `description` LIKE '%Ä%'
   OR BINARY `description` LIKE '%Ĺ%' OR BINARY `description` LIKE '%Ă%';

UPDATE `services`
SET `name` = CONVERT(BINARY(CONVERT(`name` USING latin1)) USING utf8mb4)
WHERE BINARY `name` LIKE '%Ã%' OR BINARY `name` LIKE '%Å%' OR BINARY `name` LIKE '%Ä%'
   OR BINARY `name` LIKE '%Ĺ%' OR BINARY `name` LIKE '%Ă%';

UPDATE `services`
SET `description` = CONVERT(BINARY(CONVERT(`description` USING latin1)) USING utf8mb4)
WHERE BINARY `description` LIKE '%Ã%' OR BINARY `description` LIKE '%Å%' OR BINARY `description` LIKE '%Ä%'
   OR BINARY `description` LIKE '%Ĺ%' OR BINARY `description` LIKE '%Ă%';

UPDATE `users`
SET `first_name` = CONVERT(BINARY(CONVERT(`first_name` USING latin1)) USING utf8mb4)
WHERE BINARY `first_name` LIKE '%Ã%' OR BINARY `first_name` LIKE '%Å%' OR BINARY `first_name` LIKE '%Ä%'
   OR BINARY `first_name` LIKE '%Ĺ%' OR BINARY `first_name` LIKE '%Ă%';

UPDATE `users`
SET `last_name` = CONVERT(BINARY(CONVERT(`last_name` USING latin1)) USING utf8mb4)
WHERE BINARY `last_name` LIKE '%Ã%' OR BINARY `last_name` LIKE '%Å%' OR BINARY `last_name` LIKE '%Ä%'
   OR BINARY `last_name` LIKE '%Ĺ%' OR BINARY `last_name` LIKE '%Ă%';

UPDATE `employees`
SET `description` = CONVERT(BINARY(CONVERT(`description` USING latin1)) USING utf8mb4)
WHERE BINARY `description` LIKE '%Ã%' OR BINARY `description` LIKE '%Å%' OR BINARY `description` LIKE '%Ä%'
   OR BINARY `description` LIKE '%Ĺ%' OR BINARY `description` LIKE '%Ă%';

UPDATE `reservations`
SET `comment` = CONVERT(BINARY(CONVERT(`comment` USING latin1)) USING utf8mb4)
WHERE BINARY `comment` LIKE '%Ã%' OR BINARY `comment` LIKE '%Å%' OR BINARY `comment` LIKE '%Ä%'
   OR BINARY `comment` LIKE '%Ĺ%' OR BINARY `comment` LIKE '%Ă%';
