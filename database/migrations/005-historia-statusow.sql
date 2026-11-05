-- =========================================================================
-- Migracja 005 — historia zmian statusów rezerwacji (etap 3, ocena 6).
--
-- Po co: dotychczas zmiana statusu nadpisywała poprzednią wartość, więc nie
-- było wiadomo, kto i kiedy ją zmienił. Każda zmiana trafia teraz do
-- status_history — również utworzenie rezerwacji (old_status = NULL).
--
-- Idempotentna: tabelę zakładamy przez CREATE TABLE IF NOT EXISTS, więc drugie
-- uruchomienie niczego nie zdubluje i nie wyrzuci błędu.
--
-- Uruchomienie (z katalogu repozytorium, stos docker compose musi działać):
--   docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/005-historia-statusow.sql
-- =========================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `status_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reservation_id` int NOT NULL,
  `old_status` enum('oczekująca','potwierdzona','zrealizowana','anulowana') DEFAULT NULL,
  `new_status` enum('oczekująca','potwierdzona','zrealizowana','anulowana') NOT NULL,
  `changed_by` int DEFAULT NULL,
  `changed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `changed_by` (`changed_by`),
  KEY `changed_at` (`changed_at`),
  CONSTRAINT `status_history_ibfk_1` FOREIGN KEY (`reservation_id`)
    REFERENCES `reservations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `status_history_ibfk_2` FOREIGN KEY (`changed_by`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
