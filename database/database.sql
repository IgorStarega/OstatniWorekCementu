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
 '$2y$10$e8O4C6XG9J1mS6V8Qz3Y1u9R1D.sO/xZ/aK/8Y/0y1K/4B/6Y/2bC',
 '500600700',
 'pracownik',
 1),

('Piotr',
 'Nowak',
 'piotr.nowak@example.com',
 '$2y$10$e8O4C6XG9J1mS6V8Qz3Y1u9R1D.sO/xZ/aK/8Y/0y1K/4B/6Y/2bC',
 '501601701',
 'pracownik',
 1),

('Adam',
 'Wiśniewski',
 'adam.wisniewski@example.com',
 '$2y$10$e8O4C6XG9J1mS6V8Qz3Y1u9R1D.sO/xZ/aK/8Y/0y1K/4B/6Y/2bC',
 '502602702',
 'pracownik',
 1),

('Marek',
 'Wójcik',
 'marek.wojcik@example.com',
 '$2y$10$e8O4C6XG9J1mS6V8Qz3Y1u9R1D.sO/xZ/aK/8Y/0y1K/4B/6Y/2bC',
 '503603703',
 'pracownik',
 1),

('Anna',
 'Kowalska',
 'anna.kowalska@example.com',
 '$2y$10$e8O4C6XG9J1mS6V8Qz3Y1u9R1D.sO/xZ/aK/8Y/0y1K/4B/6Y/2bC',
 '504604704',
 'klient',
 1),

('Tomasz',
 'Zieliński',
 'tomasz.zielinski@example.com',
 '$2y$10$e8O4C6XG9J1mS6V8Qz3Y1u9R1D.sO/xZ/aK/8Y/0y1K/4B/6Y/2bC',
 '505605705',
 'klient',
 1),

('Administrator',
 'Systemu',
 'admin@ostatniworekcementu.pl',
 '$2y$10$e8O4C6XG9J1mS6V8Qz3Y1u9R1D.sO/xZ/aK/8Y/0y1K/4B/6Y/2bC',
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