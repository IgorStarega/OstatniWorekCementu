-- =========================================
-- OSTATNI WOREK CEMENTU — kompletny plik bazy
-- Powstaje ze złożenia (w tej kolejności):
--   1. database-bez-rekordow.sql (struktura)
--   2. database-tylko-rekordy.sql (dane testowe)
-- Przy edycji DDL albo danych regenerować ten plik.
-- =========================================

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Wrz 23, 2026 at 06:36 PM
-- Wersja serwera: 8.0.46
-- Wersja PHP: 8.3.26

-- Plik odtwarza bazę od zera: tworzy bazę `budowlanka_rezerwacje`
-- i całą strukturę (7 tabel, klucze, indeksy). Bez danych testowych.
CREATE DATABASE IF NOT EXISTS `budowlanka_rezerwacje`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `budowlanka_rezerwacje`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `budowlanka_rezerwacje`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `employees`
--

CREATE TABLE `employees` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `employee_availability`
--

CREATE TABLE `employee_availability` (
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `day_of_week` tinyint NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `employee_services`
--

CREATE TABLE `employee_services` (
  `employee_id` int NOT NULL,
  `service_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reservations`
--

CREATE TABLE `reservations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `service_id` int NOT NULL,
  `final_price` decimal(10,2) NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('oczekująca','potwierdzona','zrealizowana','anulowana') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'oczekująca',
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `services`
--

CREATE TABLE `services` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration_minutes` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('klient','pracownik','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'klient',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `employee_availability`
--
ALTER TABLE `employee_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indeksy dla tabeli `employee_services`
--
ALTER TABLE `employee_services`
  ADD PRIMARY KEY (`employee_id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indeksy dla tabeli `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `reservation_date` (`reservation_date`),
  ADD KEY `status` (`status`);

--
-- Indeksy dla tabeli `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeksy dla tabeli `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `employee_availability`
--
ALTER TABLE `employee_availability`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `employee_availability`
--
ALTER TABLE `employee_availability`
  ADD CONSTRAINT `employee_availability_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `employee_services`
--
ALTER TABLE `employee_services`
  ADD CONSTRAINT `employee_services_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- =========================================
-- DANE TESTOWE
-- OSTATNI WOREK CEMENTU
-- =========================================

-- KATEGORIE USŁUG

INSERT INTO service_categories (name, description) VALUES
('Prace murarskie',
 'Murowanie ścian oraz wykonywanie prac konstrukcyjnych.'),

('Prace wykończeniowe',
 'Prace związane z wykończeniem wnętrz budynków.'),

('Malowanie',
 'Malowanie ścian i sufitów.'),

('Elewacje i ocieplenia',
 'Ocieplanie budynków oraz wykonywanie elewacji.'),

('Prace remontowe',
 'Kompleksowe prace remontowe w budynkach i mieszkaniach.'),

('Montaż',
 'Montaż okien, drzwi oraz innych elementów budowlanych.');


-- =========================================
-- USŁUGI BUDOWLANE
-- =========================================

INSERT INTO services
(category_id, name, description, duration_minutes, price, is_active)
VALUES

(1,
 'Murowanie ścian',
 'Murowanie ścian z cegły, pustaków lub bloczków.',
 480,
 1200.00,
 1),

(1,
 'Tynkowanie',
 'Tynkowanie ścian wewnętrznych i zewnętrznych.',
 360,
 900.00,
 1),

(2,
 'Układanie płytek',
 'Układanie płytek ceramicznych i gresowych.',
 240,
 650.00,
 1),

(2,
 'Układanie paneli',
 'Montaż paneli podłogowych.',
 240,
 500.00,
 1),

(3,
 'Malowanie ścian',
 'Malowanie ścian wewnętrznych.',
 240,
 450.00,
 1),

(3,
 'Malowanie sufitów',
 'Malowanie sufitów farbami przeznaczonymi do wnętrz.',
 180,
 350.00,
 1),

(4,
 'Ocieplanie budynku',
 'Wykonanie ocieplenia budynku.',
 480,
 1800.00,
 1),

(4,
 'Wykonanie elewacji',
 'Kompleksowe wykonanie elewacji budynku.',
 480,
 2000.00,
 1),

(5,
 'Remont łazienki',
 'Kompleksowy remont łazienki.',
 480,
 2500.00,
 1),

(5,
 'Wykonanie posadzki',
 'Wykonanie i przygotowanie posadzki.',
 360,
 1000.00,
 1),

(6,
 'Montaż drzwi',
 'Montaż drzwi wewnętrznych i zewnętrznych.',
 180,
 400.00,
 1),

(6,
 'Montaż okien',
 'Montaż okien wraz z przygotowaniem otworu.',
 240,
 600.00,
 1);


-- =========================================
-- UŻYTKOWNICY
-- =========================================

INSERT INTO users
(first_name, last_name, email, password, phone, role, is_active)
VALUES

('Jan',
 'Kowalski',
 'jan.kowalski@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '500600700',
 'pracownik',
 1),

('Piotr',
 'Nowak',
 'piotr.nowak@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '501601701',
 'pracownik',
 1),

('Adam',
 'Wiśniewski',
 'adam.wisniewski@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '502602702',
 'pracownik',
 1),

('Marek',
 'Wójcik',
 'marek.wojcik@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '503603703',
 'pracownik',
 1),

('Anna',
 'Kowalska',
 'anna.kowalska@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '504604704',
 'klient',
 1),

('Tomasz',
 'Zieliński',
 'tomasz.zielinski@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '505605705',
 'klient',
 1),

('Administrator',
 'Systemu',
 'admin@ostatniworekcementu.pl',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '506606706',
 'admin',
 1);


-- =========================================
-- PRACOWNICY
-- =========================================

INSERT INTO employees
(user_id, description, is_active)
VALUES

(1,
 'Murarz i tynkarz z doświadczeniem w pracach konstrukcyjnych.',
 1),

(2,
 'Specjalista od układania płytek i prac wykończeniowych.',
 1),

(3,
 'Malarz zajmujący się malowaniem ścian i sufitów.',
 1),

(4,
 'Specjalista od elewacji oraz ocieplania budynków.',
 1);


-- =========================================
-- PRZYPISANIE PRACOWNIKÓW DO USŁUG
-- RELACJA N:M
-- =========================================

-- Jan Kowalski
INSERT INTO employee_services (employee_id, service_id) VALUES
(1, 1),
(1, 2);

-- Piotr Nowak
INSERT INTO employee_services (employee_id, service_id) VALUES
(2, 3),
(2, 4),
(2, 9);

-- Adam Wiśniewski
INSERT INTO employee_services (employee_id, service_id) VALUES
(3, 5),
(3, 6);

-- Marek Wójcik
INSERT INTO employee_services (employee_id, service_id) VALUES
(4, 7),
(4, 8);


-- =========================================
-- DOSTĘPNOŚĆ PRACOWNIKÓW
-- day_of_week:
-- 1 = poniedziałek
-- 2 = wtorek
-- 3 = środa
-- 4 = czwartek
-- 5 = piątek
-- 6 = sobota
-- 7 = niedziela
-- =========================================

INSERT INTO employee_availability
(employee_id, day_of_week, start_time, end_time)
VALUES

-- Jan Kowalski
(1, 1, '08:00:00', '16:00:00'),
(1, 2, '08:00:00', '16:00:00'),
(1, 3, '08:00:00', '16:00:00'),
(1, 4, '08:00:00', '16:00:00'),
(1, 5, '08:00:00', '14:00:00'),

-- Piotr Nowak
(2, 1, '09:00:00', '17:00:00'),
(2, 2, '09:00:00', '17:00:00'),
(2, 3, '09:00:00', '17:00:00'),
(2, 4, '09:00:00', '17:00:00'),
(2, 5, '09:00:00', '15:00:00'),

-- Adam Wiśniewski
(3, 1, '08:00:00', '16:00:00'),
(3, 3, '08:00:00', '16:00:00'),
(3, 5, '08:00:00', '16:00:00'),

-- Marek Wójcik
(4, 2, '07:00:00', '15:00:00'),
(4, 3, '07:00:00', '15:00:00'),
(4, 4, '07:00:00', '15:00:00'),
(4, 5, '07:00:00', '15:00:00');


-- =========================================
-- REZERWACJE
-- =========================================

INSERT INTO reservations
(user_id, employee_id, service_id, final_price, reservation_date,
 start_time, end_time, status, comment)
VALUES

(
 5,
 2,
 3,
 650.00,
 '2026-10-05',
 '09:00:00',
 '13:00:00',
 'potwierdzona',
 'Układanie płytek w łazience, około 12 m2.'
),

(
 6,
 1,
 1,
 1200.00,
 '2026-10-07',
 '08:00:00',
 '16:00:00',
 'oczekująca',
 'Murowanie ściany działowej.'
),

(
 5,
 3,
 5,
 450.00,
 '2026-10-12',
 '08:00:00',
 '12:00:00',
 'potwierdzona',
 'Malowanie salonu i przedpokoju.'
),

(
 6,
 4,
 7,
 1800.00,
 '2026-10-15',
 '07:00:00',
 '15:00:00',
 'oczekująca',
 'Ocieplenie budynku jednorodzinnego.'
);