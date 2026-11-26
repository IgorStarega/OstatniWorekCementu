# Baza danych — mapa tabel i decyzje projektowe

Baza: MySQL 8.0, `utf8mb4_unicode_ci`. Schemat źródłowy:
`database/database-bez-rekordow.sql` (14 tabel), dane demo:
`database/database-tylko-rekordy.sql`, scalony zrzut: `database/database.sql`.
Diagram: [`ERD.md`](ERD.md). Migracje: `database/migrations/` (runner `tools/migrate.php`).

## Po co jest każda tabela

| Tabela | Za co odpowiada |
|---|---|
| `users` | Konta wszystkich ról (`klient`, `pracownik`, `admin`), hash hasła, blokada logowania (`failed_attempts`, `locked_until`), potwierdzenie e-maila. |
| `employees` | Profil pracownika powiązany 1:1 z `users` (`user_id` UNIQUE), opis i aktywność. |
| `employee_availability` | Tygodniowe okno pracy pracownika: jeden wiersz na dzień tygodnia. |
| `employee_breaks` | Przerwy w ramach dnia pracy (np. 11:00–11:15); osobna tabela, bo okno pracy to jeden przedział, a przerw może być kilka. |
| `employee_days_off` | Konkretne dni wolne/urlopy (data + powód), blokują cały dzień. |
| `employee_services` | Relacja N:M — które usługi wykonuje który pracownik; klucz złożony `(employee_id, service_id)`. |
| `service_categories` | Kategorie oferty (Murarskie, Malowanie itd.). |
| `services` | Usługi: kategoria, czas trwania, cena, aktywność. `final_price` w rezerwacji jest kopią ceny z chwili zapisu. |
| `reservations` | Wizyty: klient, pracownik, usługa, data, godziny, status, komentarz. |
| `status_history` | Każda zmiana statusu rezerwacji: stary → nowy, autor, czas. |
| `admin_log` | Dziennik operacji administratora (kto, co, na jakim obiekcie). |
| `password_resets` | Jednorazowe tokeny resetu hasła (tylko SHA-256 + data ważności). |
| `email_verifications` | Jednorazowe tokeny potwierdzenia e-maila (SHA-256 + ważność). |
| `email_sends` | Licznik „wysyłek” do limitów: cooldown 60 s i maks. 3 na godzinę dla resetu i weryfikacji. |
| `schema_migrations` (techniczna) | Wersje migracji już wykonanych; tworzona przez runner, nie przez DDL. |

## Klucze, UNIQUE i indeksy — po co

- `users.email` UNIQUE — jeden login na adres; dodatkowo chroni przed wyścigiem rejestracji.
- `employees.user_id` UNIQUE — jeden profil pracownika na konto (relacja 1:1).
- `employee_availability.uq_employee_day (employee_id, day_of_week)` UNIQUE — jedno okno
  pracy na dzień; drugie okno tego samego dnia byłoby niejednoznaczne dla algorytmu slotów.
- `employee_days_off.uq_employee_date (employee_id, date)` UNIQUE — jeden powód dnia wolnego.
- `employee_services` PRIMARY `(employee_id, service_id)` — naturalny klucz pary; zapobiega
  duplikatom przypisania. Indeks `service_id` obsługuje `Employee::forService()`.
- `services.idx_services_category_active (category_id, is_active)` (migracja 009) — oferta
  i strona główna filtrują po kategorii i aktywności jednym zapytaniem.
- `reservations.uq_employee_slot (employee_id, reservation_date, start_time)` UNIQUE —
  twarda blokada dwóch rezerwacji rozpoczynających się o tej samej godzinie. Jego prefiks
  `(employee_id, reservation_date)` obsługuje panel pracownika, więc osobny indeks nie jest
  potrzebny (i nie został dodany).
- `reservations.idx_reservations_user_date (user_id, reservation_date)` (migracja 009) —
  panel klienta: „moje rezerwacje” sortowane po dacie.
- `reservations.status` i `reservations.reservation_date` — filtry administratora i statystyki.
- `status_history.reservation_id`, `admin_log.user_id`, `password_resets.token_hash` itd. —
  klucze obce i UNIQUE wynikające z relacji oraz wyszukiwania tokenu.

> `admin_log.entity_id` celowo **nie** ma klucza obcego: log jest polimorficzny
> (`entity` mówi, czy chodzi o rezerwację, usługę, pracownika czy użytkownika).
> Pozostałe kolumny `*_id` mają FK.

## Konwencja `day_of_week`

`1 = poniedziałek`, `2 = wtorek`, …, `7 = niedziela` (zgodnie z `DateTime::format('N')`).
W SQL-u przy przeliczaniu z `DAYOFWEEK()` (MySQL: 1 = niedziela) używamy
`((DAYOFWEEK(data) + 5) % 7) + 1`, żeby zachować tę samą konwencję w danych i w PHP.

## Dlaczego przerwy są w osobnej tabeli

`employee_availability` ma `UNIQUE uq_employee_day (employee_id, day_of_week)`, więc
nie da się dopisać drugiego wiersza na ten sam dzień. Przerwa nie jest drugim oknem
pracy — to wyjątek **wewnątrz** okna (może być kilka, np. 10:00–10:15 i 12:00–12:30).
Dlatego `employee_breaks` ma własny klucz główny i indeks `(employee_id, day_of_week)`,
a `AvailabilityService` odejmuje przerwy od wygenerowanych slotów. Dodatkowo
`CHECK chk_break_time` pilnuje `start_time < end_time`.

## Relacje i usuwanie

- `users` → `employees` (CASCADE), `email_verifications` (CASCADE);
  `reservations` (RESTRICT) — nie usuwamy klienta z historią.
- `service_categories` → `services` (CASCADE); `employees`/`services` → `reservations`
  (RESTRICT) — historii wizyt nie tracimy.
- `reservations` → `status_history` (CASCADE); `users` → `status_history`/`admin_log`
  (SET NULL) — historia zostaje, autor staje się „systemem”.
- `password_resets` i `email_sends` nie mają FK — są powiązane adresem e-mail, żeby
  nie blokować usuwania konta i nie komplikować limitów.

## Migracje

Numerowane pliki `002`–`009` są idempotentne: przed `ALTER`/`CREATE INDEX` sprawdzają
`information_schema`, a runner zapisuje wersje w `schema_migrations`. `database.sql`
zawiera już wszystkie zmiany, więc świeży import nie wymaga migracji (entrypoint i tak
je uruchomi i pominie wykonane wersje).
