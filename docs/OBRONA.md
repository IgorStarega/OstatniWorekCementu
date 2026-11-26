# Materiały do obrony kodu — OstatniWorekCementu

Dokument dla prowadzącego i dla zespołu: mapa wymagań na kod, scenariusz prezentacji,
pytania z odpowiedziami oraz ćwiczenia na żywo. Numeracja linii dotyczy branchu
`etap5/finalizacja-projektu`; po większych zmianach wystarczy szukać nazwy funkcji.

## 1. Mapa wymagań PDF → kod

| # | Wymóg (PDF) | Gdzie w kodzie | Co dokładnie |
|---|---|---|---|
| 1 | 2.2 Architektura MVC | `public/index.php:102` | Jeden front controller, biała lista stron w `switch`, jawne przypadki zamiast dołączania URL-a. |
| 2 | 2.2 Warstwy | `app/controllers/`, `app/models/`, `app/views/`, `app/services/` | Kontroler = przepływ, model = SQL, widok = HTML, `AvailabilityService` = algorytm slotów. |
| 3 | 9 Hashowanie haseł | `app/controllers/AuthController.php:191` | `password_hash($password, PASSWORD_DEFAULT)` przy rejestracji. |
| 4 | 9 Weryfikacja hasła | `app/controllers/AuthController.php:53` | `password_verify()` przy logowaniu; ten sam komunikat dla złego hasła i braku konta. |
| 5 | 9 Prepared statements | `app/models/Reservation.php:403`, `:543`, `:342` | `hasConflict()`, `findForUser()` i `INSERT` używają `prepare()` + `execute([...])`. |
| 6 | 9 CSRF — token | `app/helpers/csrf.php:5` | `csrf_token()` tworzy `bin2hex(random_bytes(32))` i trzyma w sesji. |
| 7 | 9 CSRF — weryfikacja | `app/helpers/csrf.php:19` | `csrf_verify()` porównuje `hash_equals()` i kończy 403 z layoutem. |
| 8 | 5.2 Kontrola dostępu | `app/helpers/auth.php:34` | `requireRole(...)` woła `requireLogin()` i sprawdza rolę; każda metoda admina ma je w pierwszej linii (`AdminController.php:15` i kolejne). |
| 9 | 9 IDOR | `app/models/Reservation.php:543` | `findForUser($id, $userId)` filtruje po `user_id`; klient nie ma dostępu do `find()`. |
| 10 | 9 IDOR → 404 | `app/controllers/ClientController.php:54` | Cudze ID wygląda jak nieistniejące: `notFound()` ustawia 404, nie 403. |
| 11 | 4.2 Algorytm dostępności | `app/services/AvailabilityService.php:18` | `slotsFor()`: okno pracy, dzień wolny, przerwy, aktywana usługa, zajęte rezerwacje, krok 15 min, dzisiejsza godzina. |
| 12 | 4.3 Reguła nakładania | `app/models/Reservation.php:403` | `start_time < :new_end AND end_time > :new_start` — dokładnie warunek z PDF. |
| 13 | 4.3 `excludeId` | `app/models/Reservation.php:426` | Przy edycji własna rezerwacja nie blokuje siebie (`AND id != :exclude_id`). |
| 14 | 9 Wyścig przy zapisie | `app/models/Reservation.php:207` | `create()` w `beginTransaction()`, walidacja w modelu, `commit()` / `rollBack()`. |
| 15 | 9 Blokada wierszy | `app/models/Reservation.php:230` | `SELECT ... FROM services ... FOR UPDATE`; analogicznie rezerwacje dnia i `reschedule()` (`:325`, `:676`). |
| 16 | 4.1 UNIQUE slotu | `database/database-bez-rekordow.sql:298` | `uq_employee_slot (employee_id, reservation_date, start_time)` — druga rezerwacja o tej samej godzinie jest odrzucana przez bazę. |
| 17 | 4.1 UNIQUE okna dnia | `database/database-bez-rekordow.sql:263` | `uq_employee_day (employee_id, day_of_week)` — jedno okno pracy na dzień. |
| 18 | 4.1 UNIQUE e-maila | `database/database-bez-rekordow.sql:337` | `users.email` — login i blokada duplikatu konta. |
| 19 | 4.1 Relacja N:M | `database/database-bez-rekordow.sql:283`, `app/models/EmployeeService.php:43` | `employee_services` z kluczem złożonym `(employee_id, service_id)`; `syncForEmployee()` w transakcji. |
| 20 | 3 Migracje | `tools/migrate.php:153`, `docker/php/entrypoint.sh:8` | Runner PDO dzieli SQL, tworzy `schema_migrations`, zapisuje wersje; entrypoint uruchamia go przed Apache z retry. |
| 21 | 4.4 Historia statusów | `app/models/Reservation.php:180`, `app/models/StatusHistory.php:14` | Każda zmiana statusu dopisuje wiersz na tym samym połączeniu/transakcji. |
| 22 | 9 Log administratora | `app/models/AdminLog.php:13`, `app/controllers/AdminController.php:1205` | `recordAdminAction()` po CRUD; brak tabeli nie wywala operacji. |
| 23 | 9 CSV / formula injection | `app/controllers/AdminController.php:1148` | `csvSafeValue()` prefiksuje `'` wartości zaczynające się od `= + - @` (także po białych znakach). |
| 24 | 9 Sesja | `public/index.php:95` | `use_strict_mode`, `httponly`, `samesite=Lax`, `secure` w prod po HTTPS; `session_regenerate_id(true)` po logowaniu (`AuthController.php:63`). |
| 25 | 9 Nagłówki bezpieczeństwa | `public/index.php:20` | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, CSP bez skryptów inline. |
| 26 | 18 Walidacja serwerowa | `app/helpers/validation.php:27` | Wspólne reguły imienia, nazwiska, e-maila, telefonu, hasła, ceny i czasu; `postString()` odrzuca tablice. |
| 27 | 18 Czytelne błędy | `public/index.php:66` | Globalny handler 500 loguje szczegóły, użytkownik widzi neutralny widok; `APP_ENV=prod` wyłącza `display_errors`. |
| 28 | 16 Puste stany | `app/views/dashboard/_table.php:10` | Każda lista ma `admin-empty` / `empty-state` / komunikat zamiast pustej tabeli. |
| 29 | 10 Nawigacja | `app/views/layout/header.php:21`, `app/views/layout/admin.php:51` | `aria-label`, aktywna pozycja, sidebar admina, `lang="pl"`, viewport. |
| 30 | 14 Testy | `tools/qa-smoke.sh` (testy 71–96) | 96 numerowanych testów (146 asercji w przebiegu), `check-slots.php` 13/13, `test-conflicts.php` 8/8. |

## 2. Jak opowiedzieć kluczowe fragmenty

**Hashowanie haseł.** Rejestracja nigdy nie zapisuje hasła jawnie — woła
`password_hash()` z domyślnym bcryptem, a logowanie porównuje przez `password_verify()`.
Bez tego wyciek bazy oznaczałby natychmiastowe przejęcie kont; hash sprawia, że
atakujący musi łamać każdy hasło osobno.

**Prepared statements.** Wszystkie zapytania z danymi użytkownika mają placeholdery
(`:id`, `:user_id`) i tablicę parametrów. Sterownik MySQL sam escapuje wartości,
więc `' OR 1=1 --` zostaje tekstem, a nie fragmentem SQL.

**CSRF.** Token jest losowy i związany z sesją; każdy formularz POST go zawiera,
a `csrf_verify()` porównuje go w czasie stałym. Bez tego obca strona mogłaby wysłać
żądanie anulowania cudzej wizyty w imieniu zalogowanego klienta.

**IDOR.** Klient dostaje wyłącznie `findForUser($id, $userId)`, gdzie `user_id`
pochodzi z sesji. Podmiana `?id=` na cudze kończy się 404 — nie zdradzamy nawet,
że taki rekord istnieje.

**Algorytm dostępności.** `slotsFor()` bierze okno pracy z `employee_availability`,
odejmuje dzień wolny, przerwy i nieanulowane rezerwacje, a kandydatów generuje co
15 minut i wymaga `start + duration <= koniec okna`. Dzięki temu ta sama funkcja
obsługuje formularz, AJAX i kalendarz — nie ma trzech różnych odpowiedzi.

**Konflikt.** `hasConflict()` pyta bazę, czy istnieje rezerwacja tego pracownika
w tym dniu, która zaczyna się przed nowym końcem i kończy po nowym początku.
Warunek `new_start < existing_end AND new_end > existing_start` poprawnie dopuszcza
styk 10:00–11:00 i 11:00–12:00.

**Wyścig.** `create()` otwiera transakcję i blokuje wiersz usługi oraz rezerwacje
dnia przez `FOR UPDATE`. Dwa kliknięcia w tej samej sekundzie ustawiają się w kolejkę:
pierwsze zajmuje slot, drugie widzi konflikt i dostaje czytelny komunikat.

**N:M.** `employee_services` ma klucz złożony `(employee_id, service_id)`, więc jedna
para nie może się zdublować, a pracownik może mieć wiele usług i odwrotnie.

**Migracje.** `migrate.php` sortuje pliki, dzieli je na instrukcje, wykonuje w transakcji
i zapisuje nazwę w `schema_migrations`. Drugie uruchomienie pomija wykonane wersje.
Entrypoint robi to przed startem Apache, więc deploy nie wymaga ręcznego SQL-a.

## 3. Scenariusz prezentacji (5–8 minut)

1. **Gość** — `http://localhost:8082/` → strona główna z realną ofertą, licznikami
   i kategoriami; klik w usługę → szczegóły → „Zaloguj się”.
2. **Rejestracja** — nowy klient, celowo zły e-mail (`abc`) → komunikat walidacji;
   poprawka, rejestracja, link potwierdzający na ekranie (brak SMTP).
3. **Rezerwacja** — klient → oferta → usługa → pracownik → kalendarz (dni z liczbą
   slotów) → godzina → potwierdzenie. Pokazać, że `end_time` i `final_price` liczy serwer.
4. **Konflikt** — druga rezerwacja na ten sam slot (w innej karcie) → czytelny
   komunikat „termin nie jest już dostępny”, powrót do wyboru godziny.
5. **Panel klienta** — „Moje rezerwacje” → anulowanie (status się zmienia, wiersz
   zostaje) → przełożenie wizyty na inny termin.
6. **Pracownik (Jan)** — dashboard → „Dziś” → zmiana statusu własnej wizyty;
   próba cudzej → 403.
7. **Admin** — dashboard ze statystykami → rezerwacje z filtrami → eksport CSV →
   log operacji pokazuje wykonane zmiany.
8. **Git** — `git log --oneline --graph --all | head -40` i krótko o PR-ach oraz
   podziale pracy.

Konta: `anna.kowalska@example.com` (klient), `jan.kowalski@example.com` (pracownik),
`admin@ostatniworekcementu.pl` (admin). Hasło: `Haslo123!`.

## 4. Dziesięć pytań, które padną

**1. Czym różni się 403 od 404 i gdzie tego używacie?**
403 mówi „jesteś zalogowany, ale nie masz uprawnień” — używamy go w `requireRole()`,
gdy np. klient wchodzi na `?page=admin-*`. 404 mówi „nie ma takiego zasobu” —
używamy go dla cudzej rezerwacji, nieistniejącej usługi i nieistniejącego ID.
Przy cudzym ID wolimy 404, żeby nie zdradzać, że rekord istnieje.

**2. Gdzie sprawdzacie konflikt i dlaczego dwa razy?**
Najpierw `slotsFor()` nie pokazuje zajętych godzin (UI), a przy zapisie `create()`
ponownie woła `hasConflict()` w transakcji z `FOR UPDATE`. Pierwsze sprawdzenie jest
dla wygody, drugie jest zabezpieczeniem — formularz można ominąć, a stan bazy mógł
się zmienić między wyświetleniem a kliknięciem.

**3. Co się stanie, gdy dwóch klientów kliknie ten sam slot w tej samej sekundzie?**
Oba żądania wchodzą w transakcję, ale blokada `FOR UPDATE` przepuszcza je po kolei.
Pierwsze wstawia rezerwację i commituje; drugie po odblokowaniu widzi ją w `hasConflict()`
i dostaje komunikat o zajętym terminie. Dodatkowo `UNIQUE uq_employee_slot` jest
ostatnią linią obrony przed dokładnie tą samą godziną startu.

**4. Dlaczego hasła nie są w bazie jawnie i jak je weryfikujecie?**
Bo wyciek bazy nie może oznaczać przejęcia kont. Zapisujemy `password_hash()`,
a przy logowaniu porównujemy `password_verify()`; hash bcrypt zawiera sól, więc dwa
takie same hasła mają różne wartości. Hasła nie trafiają do sesji ani logów.

**5. Pokaż relację N:M i wyjaśnij jej klucz złożony.**
To `employee_services (employee_id, service_id)`. Klucz złożony jest jednocześnie
kluczem głównym, więc ta sama para nie powtórzy się, a usunięcie pracownika lub usługi
kaskadowo usuwa przypisanie. `Employee::forService()` i `EmployeeService::syncForEmployee()`
korzystają z tego indeksu.

**6. Po co wam `UNIQUE uq_employee_slot` i co się dzieje przy naruszeniu?**
To indeks na `(employee_id, reservation_date, start_time)`. Chroni przed dwoma
rezerwacjami zaczynającymi się o tej samej godzinie, nawet jeśli logika aplikacji
zawiedzie. Naruszenie daje `PDOException` 23000; `create()` łapie je i zwraca
komunikat „termin jest już zajęty”, a nie 500.

**7. Jak działa migracja i co się stanie, gdy uruchomicie ją dwa razy?**
Runner czyta `database/migrations/*.sql` po kolei, dzieli na instrukcje, wykonuje
i zapisuje nazwę pliku w `schema_migrations`. Druga próba widzi wersję w tabeli
i pomija plik. Same pliki też są idempotentne (sprawdzają `information_schema`),
więc nawet ręczne uruchomienie nie psuje schematu.

**8. Gdzie jest walidacja i dlaczego nie wystarczy walidacja w JS?**
Walidacja jest w PHP (`app/helpers/validation.php`) i dodatkowo w modelach
(`Reservation::create()`, `hasConflict()`). JS i atrybuty HTML5 tylko podpowiadają
użytkownikowi; każdy może wyłączyć JS albo wysłać żądanie `curl`, więc serwer
musi sprawdzić wszystko sam.

**9. Jak klient nie może zobaczyć cudzej rezerwacji — pokaż kod.**
`ClientController::show()` woła `findForUser($id, currentUserId())`; zapytanie ma
`WHERE r.id = :id AND r.user_id = :user_id`. Jeśli nic nie znajdzie, renderuje 404.
Nie ma ścieżki, która podaje do widoku rezerwację bez warunku `user_id`.

**10. Jak dodaliście nową funkcję, np. przerwy?**
Commit `3f92638` dodaje migrację `008-przerwy.sql`, tabelę `employee_breaks`,
model, UI w dostępności i odejmowanie przerw w `slotsFor()`. Test 64 w `qa-smoke.sh`
wstawia przerwę 12:00–12:30 i sprawdza, że sloty 11:45, 12:00 i 12:15 znikają.
Czyli: migracja → model → UI → test → dokumentacja, w jednej gałęzi i PR.

## 5. Podział pracy

Na podstawie `git log --author` (aliasy: `staregaigor@gmail.com`, `Wilson`,
`Igor Staręga`, `IgorStarega` to Igor; `Maciek` to Maciej):

* **Igor Staręga** — szkielet MVC i routing, logowanie/rejestracja i sesja, proces
  rezerwacji, `AvailabilityService`, `Reservation::hasConflict()`/`create()`/`reschedule()`,
  panele klienta i pracownika, panel administratora, AJAX `api-slots`, kalendarz,
  przerwy, migracje 002–009, testy `qa-smoke.sh`, `check-slots.php`, `test-conflicts.php`,
  dokumentacja README/ERD/BAZA/OBRONA.
  Główne pliki: `app/controllers/*`, `app/models/Reservation.php`,
  `app/services/AvailabilityService.php`, `tools/*`, `database/migrations/*`.
* **Maciej Radzikowski** — ochrona eksportu CSV przed formula injection
  (`AdminController::csvSafeValue`), bezpieczne usuwanie dni wolnych z kontrolą
  właściciela (`EmployeeDayOff::delete`), synchronizacja kont `users` z profilami
  `employees` przy zmianie roli/blokadzie (`User`), struktura `database.sql`
  i uzupełnienia README.
  Główne pliki: `app/controllers/AdminController.php`, `app/models/EmployeeDayOff.php`,
  `app/models/User.php`, `database/database.sql`, `README.md`.

Każdy z nas potrafi wyjaśnić fragment drugiego: wspólnie przechodziliśmy PR-y,
a kluczowe funkcje (`hasConflict`, `slotsFor`, migracje) mają komentarze w kodzie
i testy, więc odpowiedź nie zależy od pamięci jednej osoby.

## 6. Zadania na żywo

### Ćwiczenie 1 — zmiana koloru statusu (5 min)
Plik `app/views/client/reservations.php`, tablica `$statusClasses`. Zmień
`'potwierdzona' => 'text-bg-info'` na `'text-bg-primary'` i odśwież „Moje rezerwacje”.
**Rozwiązanie:** jedna linia mapy; klasa Bootstrapa zmienia kolor badge'a.
Uwaga: tę samą mapę warto powtórzyć w `client/reservation.php` i panelu admina,
żeby statusy były spójne.

### Ćwiczenie 2 — dodatkowe pole w formularzu rejestracji (10 min)
Dodaj pole „Powtórz e-mail” (`email_confirmation`) w `app/views/auth/register.php`.
**Rozwiązanie:** `label for="email_confirmation"`, `input type="email" name="email_confirmation"`;
w `AuthController::register()` odczytaj `postString('email_confirmation')` i po
`validationEmail($email)` dodaj warunek `if ($email !== $emailConfirmation) { $errors[] = 'Adresy e-mail nie są takie same.'; }`.
Nie trzeba zmieniać bazy — pole służy tylko walidacji.

### Ćwiczenie 3 — zmiana kroku slotów z 15 na 30 minut (5 min)
W `app/services/AvailabilityService.php` w pętli `for ($start = ...; ...; $start += 15)`
zmień `15` na `30`.
**Rozwiązanie:** sloty pojawiają się co pół godziny; test `check-slots.php` i test 70
nadal przechodzą, ale ubywa terminów. To pokazuje, że krok jest parametrem algorytmu,
a nie danymi w bazie.

### Błąd 1 — granica przerwy (5 min)
W `AvailabilityService::slotsFor()` zmień warunek przerwy z `$start < $breakEnd`
na `$start <= $breakEnd`. Objaw: slot zaczynający się dokładnie na końcu przerwy
(np. 12:30 po przerwie 12:00–12:30) znika.
**Fix:** przywrócić `$start < $breakEnd`; warunek „zaczyna się przed końcem” jest
symetryczny do `hasConflict()`.

### Błąd 2 — IDOR po usunięciu warunku (5 min)
W `Reservation::findForUser()` usuń `AND r.user_id = :user_id` (zostaw `:id`).
Objaw: klient po `?page=reservation&id=` widzi cudzą rezerwację.
**Fix:** przywrócić warunek i parametr `user_id`; test 47/76 w `qa-smoke.sh` od razu
to łapie. To dobra ilustracja, dlaczego testy bezpieczeństwa są ważniejsze niż UI.

## 7. Gdzie szukać dalej

* `README.md` — uruchomienie, konta, funkcje, bezpieczeństwo.
* `docs/BAZA.md` — mapa tabel, indeksy i decyzje projektowe.
* `docs/ERD.md` — diagram 14 tabel.
* `tools/qa-manual.md` — checklista przeklikania przed prezentacją.
* `tools/qa-smoke.sh` — 96 testów; `bash tools/qa-smoke.sh`.
