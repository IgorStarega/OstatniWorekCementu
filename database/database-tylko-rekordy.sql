-- =========================================
-- DANE TESTOWE
-- OSTATNI WOREK CEMENTU
-- =========================================

-- Plik jest zwykle doklejany po strukturze, która na końcu przywraca
-- character_set_client z połączenia (zwykle latin1). Bez tej linii polskie
-- znaki zapisałyby się podwójnie zakodowane.
SET NAMES utf8mb4;

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
(first_name, last_name, email, password, phone, role, is_active, email_verified_at)
VALUES

('Jan',
 'Kowalski',
 'jan.kowalski@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '500600700',
 'pracownik',
 1,
 NOW()),

('Piotr',
 'Nowak',
 'piotr.nowak@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '501601701',
 'pracownik',
 1,
 NOW()),

('Adam',
 'Wiśniewski',
 'adam.wisniewski@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '502602702',
 'pracownik',
 1,
 NOW()),

('Marek',
 'Wójcik',
 'marek.wojcik@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '503603703',
 'pracownik',
 1,
 NOW()),

('Anna',
 'Kowalska',
 'anna.kowalska@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '504604704',
 'klient',
 1,
 NOW()),

('Tomasz',
 'Zieliński',
 'tomasz.zielinski@example.com',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '505605705',
 'klient',
 1,
 NOW()),

('Administrator',
 'Systemu',
 'admin@ostatniworekcementu.pl',
 '$2y$10$heU/HBucatZT8FUGs7ZrJOi7WSTkBwX9hNc/M5Rz5hvx4gECoeiXW',
 '506606706',
 'admin',
 1,
 NOW());


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
-- Daty wyliczane od CURDATE(), ale wyłącznie na dni, w których dany
-- pracownik ma okno pracy mieszczące całą usługę. Import w dowolny
-- dzień tygodnia daje zestaw: dziś, przyszłe, przeszłe, anulowana.
-- day_of_week: 1=pon ... 7=ndz
-- =========================================

-- Strefa czasowa importu musi być polska: dump ustawia +00:00, więc bez
-- tego CURDATE() przy imporcie wieczorem wskazywałby dzień poprzedni.

CREATE TEMPORARY TABLE seq (o INT PRIMARY KEY);
INSERT INTO seq VALUES (-14),(-13),(-12),(-11),(-10),(-9),(-8),(-7),(-6),(-5),
                       (-4),(-3),(-2),(-1),(0),(1),(2),(3),(4),(5),(6),(7),
                       (8),(9),(10),(11),(12),(13),(14);

SET @d_r1 = (SELECT MAX(DATE_ADD(CURDATE(), INTERVAL o DAY)) FROM seq
  WHERE o <= -1 AND EXISTS (
    SELECT 1 FROM employee_availability a
    WHERE a.employee_id = 1
      AND a.day_of_week = ((DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL o DAY))+5)%7)+1
      AND a.start_time <= '08:00:00' AND a.end_time >= '16:00:00'));

SET @d_r2 = (SELECT MIN(DATE_ADD(CURDATE(), INTERVAL o DAY)) FROM seq
  WHERE o >= 1 AND EXISTS (
    SELECT 1 FROM employee_availability a
    WHERE a.employee_id = 2
      AND a.day_of_week = ((DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL o DAY))+5)%7)+1
      AND a.start_time <= '09:00:00' AND a.end_time >= '13:00:00'));

SET @d_r3 = (SELECT MIN(DATE_ADD(CURDATE(), INTERVAL o DAY)) FROM seq
  WHERE o >= 5 AND EXISTS (
    SELECT 1 FROM employee_availability a
    WHERE a.employee_id = 3
      AND a.day_of_week = ((DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL o DAY))+5)%7)+1
      AND a.start_time <= '08:00:00' AND a.end_time >= '12:00:00'));

SET @d_r4 = (SELECT MAX(DATE_ADD(CURDATE(), INTERVAL o DAY)) FROM seq
  WHERE o <= -1 AND EXISTS (
    SELECT 1 FROM employee_availability a
    WHERE a.employee_id = 4
      AND a.day_of_week = ((DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL o DAY))+5)%7)+1
      AND a.start_time <= '07:00:00' AND a.end_time >= '15:00:00'));

-- R5: ten sam pracownik i start co R2, więc data MUSI być inna
-- (UNIQUE uq_employee_slot = employee_id + date + start_time).
SET @d_r5 = (SELECT MIN(DATE_ADD(CURDATE(), INTERVAL o DAY)) FROM seq
  WHERE o >= 1
    AND DATE_ADD(CURDATE(), INTERVAL o DAY) <> @d_r2
    AND EXISTS (
    SELECT 1 FROM employee_availability a
    WHERE a.employee_id = 2
      AND a.day_of_week = ((DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL o DAY))+5)%7)+1
      AND a.start_time <= '09:00:00' AND a.end_time >= '13:00:00'));

-- Urlop Jana na dzień, w którym faktycznie ma okno — inaczej check-slots
-- przechodzi z niewłaściwego powodu (brak okna zamiast dnia wolnego).
SET @d_off = (SELECT MIN(DATE_ADD(CURDATE(), INTERVAL o DAY)) FROM seq
  WHERE o >= 1 AND EXISTS (
    SELECT 1 FROM employee_availability a
    WHERE a.employee_id = 1
      AND a.day_of_week = ((DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL o DAY))+5)%7)+1));

DROP TEMPORARY TABLE seq;

INSERT INTO reservations
(user_id, employee_id, service_id, final_price, reservation_date,
 start_time, end_time, status, comment)
VALUES
(5, 1, 1, 1200.00, @d_r1, '08:00:00', '16:00:00', 'zrealizowana',
 'Murowanie ściany działowej.'),
(6, 2, 3,  650.00, @d_r2, '09:00:00', '13:00:00', 'oczekująca',
 'Układanie płytek w łazience, około 12 m2.'),
(5, 3, 5,  450.00, @d_r3, '08:00:00', '12:00:00', 'oczekująca',
 'Malowanie salonu i przedpokoju.'),
(6, 4, 7, 1800.00, @d_r4, '07:00:00', '15:00:00', 'zrealizowana',
 'Ocieplenie budynku jednorodzinnego.'),
(5, 2, 4,  500.00, @d_r5, '09:00:00', '13:00:00', 'anulowana',
 'Układanie paneli w pokoju — klient wycofał zamówienie.');

-- Wizyta "na dziś". INSERT ... SELECT, nie VALUES: w weekend żadna para
-- pracownik+usługa nie mieści się w oknie, więc wiersz po prostu nie
-- powstaje, a import nie pada na NULL.
INSERT INTO reservations
(user_id, employee_id, service_id, final_price, reservation_date,
 start_time, end_time, status, comment)
SELECT 5, a.employee_id, s.id, s.price, CURDATE(), a.start_time,
       ADDTIME(a.start_time, SEC_TO_TIME(s.duration_minutes * 60)),
       'potwierdzona', 'Wizyta zaplanowana na dziś.'
FROM employee_availability a
JOIN employee_services es ON es.employee_id = a.employee_id
JOIN services s ON s.id = es.service_id AND s.is_active = 1
WHERE a.day_of_week = ((DAYOFWEEK(CURDATE()) + 5) % 7) + 1
  AND ADDTIME(a.start_time, SEC_TO_TIME(s.duration_minutes * 60)) <= a.end_time
ORDER BY a.employee_id ASC, s.id ASC
LIMIT 1;


-- =========================================
-- DNI WOLNE PRACOWNIKÓW
-- =========================================

-- Urlop Jana za tydzień (CURDATE() + 7): przy imporcie w środę jest to
-- środa, czyli dzień, w którym Jan normalnie ma okno 08:00-16:00.
-- Służy do testu, że dzień wolny blokuje wszystkie sloty tego dnia.
INSERT INTO employee_days_off (employee_id, date, reason)
VALUES
(1, @d_off, 'Urlop wypoczynkowy');


-- =========================================
-- PRZERWY W GODZINACH PRACY
-- Tylko u pracowników, których usługi MIESZCZĄ SIĘ w oknie pracy
-- z zapasem na przerwę. Jan (480 i 360 min), Piotr (480 min) i Marek
-- (480 min) mają usługi równe lub prawie równe oknu 8 h — każda przerwa
-- w środku odcięłaby wszystkie sloty. Adam ma 180 i 240 min w oknie 8 h,
-- więc przerwa 12:00-12:30 realnie odejmuje terminy, ale zostawia inne.
-- =========================================

INSERT INTO employee_breaks (employee_id, day_of_week, start_time, end_time, label)
VALUES
(3, 1, '12:00:00', '12:30:00', 'Przerwa obiadowa'),
(3, 3, '12:00:00', '12:30:00', 'Przerwa obiadowa'),
(3, 5, '12:00:00', '12:30:00', 'Przerwa obiadowa');


-- =========================================
-- HISTORIA STATUSÓW REZERWACJI
-- Pierwszy wpis każdej rezerwacji: old_status = NULL (utworzenie),
-- autor = administrator (id 7), data = created_at rezerwacji.
-- =========================================

INSERT INTO status_history
(reservation_id, old_status, new_status, changed_by, changed_at)
SELECT id, NULL, status, 7, created_at
FROM reservations;