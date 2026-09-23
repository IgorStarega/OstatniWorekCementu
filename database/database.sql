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
(category_id, name, description, duration, price, active)
VALUES

(1,
 'Murowanie ścian',
 'Murowanie ścian z cegły, pustaków lub bloczków.',
 480,
 1200.00,
 TRUE),

(1,
 'Tynkowanie',
 'Tynkowanie ścian wewnętrznych i zewnętrznych.',
 360,
 900.00,
 TRUE),

(2,
 'Układanie płytek',
 'Układanie płytek ceramicznych i gresowych.',
 240,
 650.00,
 TRUE),

(2,
 'Układanie paneli',
 'Montaż paneli podłogowych.',
 240,
 500.00,
 TRUE),

(3,
 'Malowanie ścian',
 'Malowanie ścian wewnętrznych.',
 240,
 450.00,
 TRUE),

(3,
 'Malowanie sufitów',
 'Malowanie sufitów farbami przeznaczonymi do wnętrz.',
 180,
 350.00,
 TRUE),

(4,
 'Ocieplanie budynku',
 'Wykonanie ocieplenia budynku.',
 480,
 1800.00,
 TRUE),

(4,
 'Wykonanie elewacji',
 'Kompleksowe wykonanie elewacji budynku.',
 480,
 2000.00,
 TRUE),

(5,
 'Remont łazienki',
 'Kompleksowy remont łazienki.',
 480,
 2500.00,
 TRUE),

(5,
 'Wykonanie posadzki',
 'Wykonanie i przygotowanie posadzki.',
 360,
 1000.00,
 TRUE),

(6,
 'Montaż drzwi',
 'Montaż drzwi wewnętrznych i zewnętrznych.',
 180,
 400.00,
 TRUE),

(6,
 'Montaż okien',
 'Montaż okien wraz z przygotowaniem otworu.',
 240,
 600.00,
 TRUE);


-- =========================================
-- UŻYTKOWNICY
-- =========================================

INSERT INTO users
(name, surname, email, password, phone, role, active)
VALUES

('Jan',
 'Kowalski',
 'jan.kowalski@example.com',
 'TEST_PASSWORD_1',
 '500600700',
 'employee',
 TRUE),

('Piotr',
 'Nowak',
 'piotr.nowak@example.com',
 'TEST_PASSWORD_2',
 '501601701',
 'employee',
 TRUE),

('Adam',
 'Wiśniewski',
 'adam.wisniewski@example.com',
 'TEST_PASSWORD_3',
 '502602702',
 'employee',
 TRUE),

('Marek',
 'Wójcik',
 'marek.wojcik@example.com',
 'TEST_PASSWORD_4',
 '503603703',
 'employee',
 TRUE),

('Anna',
 'Kowalska',
 'anna.kowalska@example.com',
 'TEST_PASSWORD_5',
 '504604704',
 'client',
 TRUE),

('Tomasz',
 'Zieliński',
 'tomasz.zielinski@example.com',
 'TEST_PASSWORD_6',
 '505605705',
 'client',
 TRUE),

('Administrator',
 'Systemu',
 'admin@ostatniworekcementu.pl',
 'TEST_PASSWORD_ADMIN',
 '506606706',
 'admin',
 TRUE);


-- =========================================
-- PRACOWNICY
-- =========================================

INSERT INTO employees
(user_id, description, active)
VALUES

(1,
 'Murarz i tynkarz z doświadczeniem w pracach konstrukcyjnych.',
 TRUE),

(2,
 'Specjalista od układania płytek i prac wykończeniowych.',
 TRUE),

(3,
 'Malarz zajmujący się malowaniem ścian i sufitów.',
 TRUE),

(4,
 'Specjalista od elewacji oraz ocieplania budynków.',
 TRUE);


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
(user_id, employee_id, service_id, reservation_date,
 start_time, end_time, status, comment)
VALUES

(
 5,
 2,
 3,
 '2026-10-05',
 '09:00:00',
 '13:00:00',
 'confirmed',
 'Układanie płytek w łazience, około 12 m2.'
),

(
 6,
 1,
 1,
 '2026-10-07',
 '08:00:00',
 '16:00:00',
 'pending',
 'Murowanie ściany działowej.'
),

(
 5,
 3,
 5,
 '2026-10-12',
 '08:00:00',
 '12:00:00',
 'confirmed',
 'Malowanie salonu i przedpokoju.'
),

(
 6,
 4,
 7,
 '2026-10-15',
 '07:00:00',
 '15:00:00',
 'pending',
 'Ocieplenie budynku jednorodzinnego.'
);