# OstatniWorekCementu

System internetowy do obsługi firmy budowlanej oraz rezerwacji usług budowlanych. Aplikacja umożliwia klientom zapoznanie się z ofertą firmy oraz składanie rezerwacji, a pracownikom i administratorom zarządzanie usługami oraz realizowanymi zleceniami.

**Autorzy:** Igor Staręga, Maciej Radzikowski (projekt 5iT, przedmiot TSiAI).

System prowadzi klienta przez pełną ścieżkę rezerwacji: prezentuje usługi z bazy,
pozwala wybrać pracownika i wolną godzinę w kalendarzu, a następnie zapisuje wizytę
i pokazuje ją w panelu klienta. Pracownik widzi swoje wizyty na dziś, przyszłe i historię,
a administrator zarządza ofertą, pracownikami, dostępnością i rezerwacjami.
Dostępność liczy serwer na podstawie tygodniowych okien pracy, dni wolnych i przerw,
więc ten sam wolny termin widzi każdy klient i aplikacja nie dopuszcza nakładania wizyt.

## Spis treści

* [Opis projektu](#opis-projektu)
* [Główne funkcjonalności](#główne-funkcjonalności)
* [Technologie](#technologie)
* [Baza danych](#baza-danych)
* [Dostępność pracowników](#dostępność-pracowników)
* [Proces rezerwacji](#proces-rezerwacji)
* [Strona główna](#strona-główna)
* [AJAX — wolne terminy](#ajax--wolne-terminy)
* [Kalendarz miesiąca](#kalendarz-miesiąca)
* [Przerwy w godzinach pracy](#przerwy-w-godzinach-pracy)
* [Przełożenie wizyty](#przełożenie-wizyty)
* [Role użytkowników](#role-użytkowników)
* [Dane testowe (Logowanie)](#dane-testowe-logowanie)
* [Zakres projektu](#zakres-projektu)
* [Struktura projektu](#struktura-projektu)
* [Wymagania](#wymagania)
* [Uruchomienie](#uruchomienie)
* [Deploy bez lokalnego repozytorium](#deploy-bez-lokalnego-repozytorium)
* [Testy](#testy)
* [QA przed oddaniem etapu](#qa-przed-oddaniem-etapu)
* [Konfiguracja](#konfiguracja)
* [Bezpieczeństwo](#bezpieczeństwo)
* [Funkcje dodatkowe](#funkcje-dodatkowe)
* [Autorzy](#autorzy)
* [Status projektu](#status-projektu)

---

## Opis projektu

**OstatniWorekCementu** to aplikacja internetowa przeznaczona dla firmy świadczącej usługi budowlane.

Głównym celem projektu jest stworzenie miejsca, w którym klient może w prosty sposób zapoznać się z ofertą firmy oraz zarezerwować interesującą go usługę.

System został zaprojektowany z myślą o obsłudze trzech podstawowych grup użytkowników:

* **klientów** – osób zainteresowanych usługami budowlanymi,
* **pracowników** – osób odpowiedzialnych za realizację usług,
* **administratorów** – osób zarządzających systemem i jego zawartością.

Aplikacja pozwala uporządkować proces obsługi klienta – od przedstawienia oferty, przez rezerwację usługi, aż po zarządzanie zleceniami.

---

## Główne funkcjonalności

### Dla klientów

* przeglądanie dostępnych usług budowlanych,
* zapoznanie się ze szczegółami wybranej usługi,
* składanie rezerwacji,
* podawanie informacji dotyczących planowanego zlecenia,
* zarządzanie własnymi rezerwacjami,
* dostęp do swojego konta użytkownika.

### Dla pracowników

* podgląd przypisanych usług,
* dostęp do informacji o rezerwacjach,
* obsługa zleceń klientów,
* zarządzanie swoim harmonogramem pracy.

### Dla administratorów

* zarządzanie użytkownikami,
* zarządzanie usługami budowlanymi,
* zarządzanie pracownikami,
* zarządzanie rezerwacjami,
* kontrolowanie dostępności usług,
* dni wolne i urlopy pracowników,
* statystyki i wykresy na dashboardzie,
* historia zmian statusów rezerwacji,
* log operacji administratora,
* eksport rezerwacji do CSV,
* administracja systemem.

---

## Technologie

Projekt został przygotowany jako aplikacja internetowa w PHP bez frameworka i Composera.
Wykorzystywane technologie i wersje:

* **PHP 8.2** (Apache w kontenerze) — logika aplikacji i walidacja serwerowa,
* **MySQL 8.0** — relacyjna baza danych (`utf8mb4_unicode_ci`),
* **HTML5, CSS3, JavaScript** (ES5/ES6, bez frameworka JS) — warstwa prezentacji,
* **Bootstrap 5.3** — lokalna kopia w `public/vendor/` (bez zewnętrznego CDN),
* **Docker / Docker Compose** — powtarzalne środowisko lokalne i deploy,
* **Git** — praca na branchach i historia zmian,
* **phpMyAdmin** — podgląd bazy podczas demonstracji.

Szczegółowa konfiguracja aplikacji znajduje się w katalogach:

* `app/` – logika aplikacji,
* `config/` – konfiguracja,
* `database/` – pliki związane z bazą danych,
* `docs/` – dokumentacja projektu,
* `public/` – publiczna część aplikacji.

---

## Baza danych

System korzysta z relacyjnej bazy danych odpowiedzialnej za przechowywanie informacji związanych z użytkownikami, usługami oraz rezerwacjami. Schemat w `database/database-bez-rekordow.sql` obejmuje **14 tabel aplikacji**; piętnastą tabelą w działającej bazie jest techniczna `schema_migrations`, tworzona przez runner migracji. Krótki opis tabel i indeksów: [`docs/BAZA.md`](docs/BAZA.md).

Baza danych umożliwia między innymi przechowywanie:

* danych użytkowników,
* informacji o pracownikach,
* listy usług budowlanych,
* informacji o rezerwacjach,
* terminów realizacji usług,
* statusów zleceń i historii ich zmian,
* logu operacji administratora,
* dni wolnych i urlopów pracowników,
* przerw w godzinach pracy pracowników,
* dodatkowych informacji przekazanych przez klienta.

Pliki związane z bazą danych znajdują się w katalogu: `database/`

---

## Dostępność pracowników

Tabela `employee_availability` przechowuje tygodniowe okna pracy pracownika.

**Konwencja `day_of_week`:** 1 = poniedziałek, 7 = niedziela.

Jeden wiersz na pracownika i dzień (`UNIQUE uq_employee_day`), `start_time` musi być
ostro mniejsze niż `end_time`. Etap 4 (proces rezerwacji) wyznacza wolne terminy
na podstawie tych okien i `services.duration_minutes`.

Dni wolne i urlopy są przechowywane w `employee_days_off` (jeden wpis na pracownika
i dzień, `UNIQUE uq_employee_date`) i obsługiwane w panelu administratora.

### Algorytm dostępności

`AvailabilityService::slotsFor()` wyznacza wolne terminy dla pary pracownik + usługa:

1. data musi przejść round-trip `DateTime::createFromFormat('Y-m-d')` i być nie wcześniejsza niż dziś,
2. okno pracy pochodzi z `employee_availability` dla `day_of_week` (1 = poniedziałek, 7 = niedziela); brak wiersza = brak slotów,
3. urlop lub dzień wolny z `employee_days_off` blokuje cały dzień,
4. usługa musi być aktywna (`is_active = 1`),
5. przerwy z `employee_breaks` dla danego dnia odrzucają każdy slot, który nachodzi
   na przerwę (`start < break_end AND end > break_start`),
6. rezerwacje pracownika z tego dnia o statusie innym niż `anulowana` są zajęte,
7. kandydaci startują co 15 minut i muszą spełnić `start + duration_minutes <= koniec okna`,
8. dla dzisiejszego dnia sloty z minioną godziną startu są odfiltrowane.

Przerwy pobieramy jedną listą na dzień, a nie osobnym zapytaniem dla każdego slotu.

**Usługa 480 min przy oknie 8:00–16:00 i bez przerwy ma dokładnie jeden slot — 8:00.
To celowe, nie błąd. Przerwa w środku okna może uniemożliwić rezerwację długiej usługi
w tym dniu — wtedy kalendarz pokazuje dzień jako pełny.**

### Reguła konfliktów

`Reservation::hasConflict()` uznaje nakładanie, gdy zachodzi jednocześnie
`start_time < :new_end AND end_time > :new_start`, a istniejąca rezerwacja nie ma
statusu `anulowana`. Dla pary 10:00–11:00 daje to 5 blokad i 2 dopuszczenia:

| Przypadek | Wynik |
|---|---|
| 09:30–10:30 | blokada |
| 10:00–11:00 (identyczny przedział) | blokada |
| 10:15–10:45 (w środku) | blokada |
| 10:30–11:30 | blokada |
| 09:30–11:30 (obejmuje całość) | blokada |
| 09:00–10:00 (koniec = start) | dopuszczenie |
| 11:00–12:00 (start = koniec) | dopuszczenie |

Te same etykiety są w `tools/test-conflicts.php` (8/8 PASS, w tym `excludeId`).
`UNIQUE uq_employee_slot` obejmuje tylko `(employee_id, reservation_date, start_time)` —
**bez `end_time`** — więc rezerwacja 10:00 i 10:30 przejdzie przez indeks. Jedyną
obroną przed nakładaniem jest `hasConflict()` wywoływane w transakcji `create()`,
z blokadą wierszy pracownika z danego dnia (`FOR UPDATE`).

### Ochrona IDOR

Panel klienta używa wyłącznie `forUser()`, `findForUser()` i `cancel()`.
`Reservation::find()` pozostaje dla administratora. Cudze ID rezerwacji w panelu
klienta kończy się **404** (nie 403), a POST anulowania cudzej rezerwacji nie zmienia
bazy. Anulowanie jest zmianą statusu na `anulowana`, nigdy `DELETE`.

---

## Proces rezerwacji

Ścieżka: **oferta → usługa → pracownik → data → godzina → potwierdzenie → zapis**.
Rezerwację składa wyłącznie rola `klient`; pracownik i administrator widzą w ofercie
nieaktywny przycisk.

Stan kroku jest w `$_SESSION['booking']` (`service_id`, `employee_id`, `date`,
`start_time`, `comment`). Bramka kolejności działa przed renderem: krok `employee`
wymaga usługi, `date` — usługi i pracownika, `time` — dodatkowo daty, a `confirm`
— godziny. Ręczne `?page=booking&step=confirm` bez stanu przekierowuje na pierwszy
brakujący krok i **niczego nie zapisuje**. „Wstecz” czyści właściwy klucz i wszystkie
późniejsze.

Każdy krok jest walidowany po stronie serwera:

* **usługa** — `Service::findActive()`, nieaktywna lub nieistniejąca jest odrzucana,
* **pracownik** — `Employee::forService()` i sprawdzenie ID na liście; podmiana
  `employee_id` w DevTools na osobę nieprzypisaną do usługi jest odrzucana,
* **data** — format `Y-m-d` z round-tripem i data nie wcześniejsza niż dziś;
  `slotsFor()` musi zwrócić niepustą listę,
* **godzina** — wartość musi być dokładnie jedną z listy `slotsFor()`; brak własnej
  arytmetyki godzin,
* **potwierdzenie** — ponowne sprawdzenie usługi, pracownika i slotu, potem
  `Reservation::create()` w jednej transakcji. Model dodatkowo waliduje datę
  i godzinę, istnienie okna `employee_availability`, brak wpisu w
  `employee_days_off` oraz zmieszczenie całej usługi w oknie pracy — także
  wtedy, gdy formularz zostanie pominięty. Zajęty termin kończy się komunikatem
  i powrotem do wyboru godziny.

Po zapisie klient trafia na `?page=my-reservations` z komunikatem zawierającym numer
rezerwacji. Panel klienta dzieli listę na nadchodzące i historię, pokazuje szczegóły
z historią statusów i pozwala anulować wyłącznie przyszłą, nieanulowaną wizytę
(serwer sprawdza datę ponownie). Panel pracownika pokazuje wizyty na dziś, przyszłe
i historię; dozwolone przejścia to `oczekująca → potwierdzona`,
`potwierdzona → zrealizowana` oraz `oczekująca → anulowana`. Te same przejścia
wymusza `Reservation::updateStatus()`, więc administrator korzysta z tej samej
mapy i nie może cofnąć statusu.

---

## Strona główna

`HomeController::index()` łączy się z bazą i pokazuje skrót realnej oferty zamiast
placeholdera z etapu 1:

* hero z nazwą firmy i przyciskami zależnymi od roli (gość: oferta + logowanie,
  klient: oferta + rezerwacja, pracownik/admin: tylko oferta),
* trzy liczniki: aktywne usługi, kategorie z aktywnymi usługami i aktywni pracownicy
  (`Employee::countActive()`),
* kafle kategorii z linkiem `?page=offer&category=ID` i liczbą usług,
* maksymalnie sześć polecanych usług z kategorią, czasem trwania, ceną i linkiem
  do szczegółów,
* CTA prowadzące do pełnej oferty.

Pusty stan (`$serviceList === []`) pokazuje czytelny komunikat zamiast białej strony.

---

## AJAX — wolne terminy

Endpoint `/?page=api-slots` (`ApiController::slots()`) jest drugim punktem wejścia
do tej samej logiki co formularz rezerwacji — nie ma osobnej, uproszczonej walidacji.

Parametry GET:

| Parametr | Znaczenie |
|---|---|
| `employee_id` | ID pracownika |
| `service_id` | ID aktywnej usługi |
| `date` | data `Y-m-d` |

Odpowiedź 200 (jedna linia JSON):

```json
{"ok":true,"date":"2026-11-20","slots":["08:00","08:15"],"count":2}
```

Walidacja jest identyczna jak w formularzu: usługa musi istnieć i być aktywna,
pracownik musi wykonywać tę usługę, data przechodzi round-trip `createFromFormat`,
a data z przeszłości jest odrzucana. Błędy walidacji dają **422** z
`{"ok":false,"error":"..."}`. Gość i role inne niż `klient` dostają **403 JSON**
(nie HTML przekierowania). Wyjątki PDO trafiają wyłącznie do `error_log()`;
klient widzi `{"ok":false,"error":"Błąd serwera."}` i kod **500**.

W kroku godziny siatka radio pozostaje w HTML jako fallback. `public/js/script.js`
przy istnieniu `#slot-grid` pobiera terminy przez `fetch()`, przerysowuje siatkę
i pokazuje błąd w `#slot-error` (bez `alert()` i bez inline'owych `onclick`).

---

## Kalendarz miesiąca

Krok daty (`app/views/booking/date.php`) pokazuje siatkę miesiąca zamiast samego
`<input type="date">`. `BookingController::buildCalendar()` liczy dni **raz na render**,
poza pętlą widoku:

* `past` — data wcześniejsza niż dziś,
* `off` — dzień wolny z `employee_days_off`,
* `free` — `slotsFor()` zwraca niepustą listę (badge z liczbą slotów, zielony),
* `full` — brak slotów (badge szary `0`).

Dni `free` są linkami `?page=booking&step=date&pick=YYYY-MM-DD`, `past` i `off`
pozostają nieklikalnymi elementami. Strzałki `←`/`→` zmieniają miesiąc parametrem
`month=YYYY-MM`. Wybór `pick` jest walidowany tak samo jak formularz: poprawna data,
nie z przeszłości i niepuste `slotsFor()`. Pod tabelą nadal jest `input type="date"`
jako alternatywa dostępnościowa.

**Ograniczenie wydajności (świadomy kompromis):** `buildCalendar()` wywołuje
`slotsFor()` raz na każdy dzień miesiąca, bo dopiero ono zna okno pracy, przerwy,
dzień wolny i zajęte terminy. Jedno wywołanie to 5 zapytań przygotowanych
(okno, dzień wolny, przerwy, usługa, rezerwacje dnia), więc pełny miesiąc to
około **150–160 zapytań na jedno wyświetlenie kroku daty**. Dni wolne pobieramy
raz na miesiąc (nie raz na dzień), a widok nie odpytuje bazy w pętli.
Przy skali projektu (maks. 31 dni, jeden użytkownik, indeksy z migracji 009)
jest to akceptowalne; optymalizacją na przyszłość byłoby jedno zapytanie
zbiorcze o dostępność całego miesiąca.

---

## Przerwy w godzinach pracy

Przerwy są w tabeli `employee_breaks` (migracja `008-przerwy.sql`), a nie w
`employee_availability`, bo `UNIQUE uq_employee_day` blokuje drugi wiersz na ten
sam dzień tygodnia. Kolumny: `employee_id`, `day_of_week` (1–7), `start_time`,
`end_time`, opcjonalna `label` (maks. 50 znaków). Ograniczenie `chk_break_time`
pilnuje `start_time < end_time`, a FK kasuje przerwy razem z pracownikiem.

`EmployeeBreak` udostępnia `forEmployee()`, `forEmployeeDay()`, `create()` i `delete()`.
`AvailabilityService::slotsFor()` pobiera przerwy **jedną listą na dzień** i odrzuca
kandydata, gdy `start < break_end AND end > break_start` — ta sama reguła nakładania
co `hasConflict()`. Panel `?page=admin-availability` ma sekcję „Przerwy” z formularzem
(dzień, od, do, etykieta), listą istniejących wpisów i usuwaniem przez POST + CSRF.
Serwer sprawdza dzień 1–7, kolejność godzin i nakładanie z istniejącymi przerwami.

Przerwy mają sens tylko u pracowników, których usługa jest krótsza niż okno pracy.
Usługa 480 min w oknie 8-godzinnym nie ma miejsca na przerwę — każda przerwa w środku
odcięłaby jedyny możliwy slot. W danych testowych przerwy ma wyłącznie Adam Wiśniewski
(usługi 180 i 240 min): poniedziałek, środa i piątek 12:00–12:30. Przerwa realnie
odejmuje terminy, ale zostawia inne — np. usługa 240 min startuje w poniedziałek
o 08:00, a 12:00 wypada.

---

## Przełożenie wizyty

Klient może zmienić termin własnej przyszłej wizyty: `?page=reschedule&id=ID`
(`ClientController::reschedule()`). GET pokazuje formularz daty i siatkę wolnych
godzin, POST zapisuje nowy termin.

`Reservation::reschedule()` działa w jednej transakcji:

1. `SELECT ... FOR UPDATE` na wierszu rezerwacji z warunkiem `id` i `user_id`,
2. odrzucenie statusów `anulowana` i `zrealizowana` oraz wizyt już rozpoczętych,
3. odczyt `services.duration_minutes` z `FOR UPDATE` i wyliczenie `end_time`,
4. `hasConflict()` z **`excludeId = $id`**, żeby rezerwacja nie kolidowała sama ze sobą,
5. `UPDATE` daty i godzin oraz wpis w `status_history` na tym samym połączeniu PDO,
6. rollback i czytelny komunikat przy `23000`, `40001` lub deadlocku `1213`.

Cudze ID daje **404**, tak samo jak w szczegółach i anulowaniu. Link „Przełóż wizytę”
pokazuje się obok „Anuluj rezerwację” tylko wtedy, gdy wizyta nie jest anulowana
i jeszcze się nie rozpoczęła.

---

## Role użytkowników

System został zaprojektowany z podziałem na role.

### Klient

Klient korzysta z publicznej części serwisu oraz składa rezerwacje:

> Przeglądanie usług ➔ Wybór usługi ➔ Wybór pracownika ➔ Wybór daty i godziny ➔ Potwierdzenie ➔ Moje rezerwacje

Może też anulować własną przyszłą rezerwację (zmiana statusu, nie usunięcie).

### Pracownik

Pracownik widzi w `?page=dashboard` wyłącznie wizyty przypisane do swojego profilu
(`employees.id` wynikające z `users.id`), podzielone na dziś, przyszłe i historię.
Może potwierdzić oczekującą wizytę, oznaczyć potwierdzoną jako zrealizowaną albo
anulować oczekującą. Cudze ID w POST jest odrzucane kodem 403 bez zmiany w bazie.

### Administrator

Administrator posiada rozszerzone uprawnienia pozwalające na zarządzanie systemem, użytkownikami, usługami oraz rezerwacjami.

---

## Dane testowe (Logowanie)

Hasło dla wszystkich kont testowych (zhashowane w bazie) to: **Haslo123!**
Pełna tabela kont (admin, czterech pracowników, dwóch klientów) znajduje się
w sekcji [Uruchomienie → Konta testowe](#konta-testowe).

---

## Zakres projektu

Projekt obejmuje stworzenie kompletnego systemu wspierającego obsługę firmy budowlanej.

### Obsługa klientów

* konta użytkowników,
* logowanie,
* zarządzanie danymi klienta,
* składanie rezerwacji,
* podgląd rezerwacji.

### Obsługa usług

* prezentacja oferty firmy,
* informacje o usługach,
* dostępność usług,
* przypisywanie usług do pracowników.

### Obsługa pracowników

* profile pracowników,
* przypisywanie usług,
* harmonogram pracy,
* obsługa zleceń.

### Obsługa administracyjna

* zarządzanie użytkownikami,
* zarządzanie usługami,
* zarządzanie pracownikami,
* zarządzanie rezerwacjami,
* kontrola działania systemu.

---

## Struktura projektu

Główna struktura repozytorium:

> OstatniWorekCementu/
> ├── app/                # logika aplikacji (MVC)
> │   ├── controllers/    # kontrolery (Home, Auth, PasswordReset, EmailVerification, Profile,
> │   │                   #            Dashboard, Admin, Offer, Booking, Client, Api)
> │   ├── models/         # modele (Database, User, PasswordReset, EmailVerification, EmailSend,
> │   │                   #         ServiceCategory, Service, Employee, EmployeeService,
> │   │                   #         EmployeeAvailability, EmployeeBreak, EmployeeDayOff,
> │   │                   #         Reservation, StatusHistory, AdminLog, Stats)
> │   ├── services/       # AvailabilityService — wyliczanie wolnych slotów z przerwami
> │   ├── helpers/        # pomocniki (CSRF, autoryzacja, walidacja, paginacja, adresy URL, tokeny, flash)
> │   └── views/          # widoki (layout, strony, offer/, booking/ — w tym kalendarz,
> │                       #         client/ — w tym reschedule, dashboard/, admin/, errors/ — 403/404/500)
> ├── config/             # konfiguracja aplikacji i serwera MySQL
> │   └── mysql/
> ├── database/           # schemat, dane testowe i scalony zrzut
> │   └── migrations/     # migracje dla istniejących baz (002–009)
> ├── docker/             # obraz PHP, php.ini i konfiguracja Apache
> │   └── php/
> │       └── entrypoint.sh # automatyczne migracje przed startem Apache
> ├── deploy/             # deploy produkcyjny z obrazu GHCR (compose + instrukcja)
> ├── docs/               # dokumentacja (diagram ERD)
> ├── public/             # publiczny katalog serwera (front controller)
> │   ├── css/            # industrialny ciemny motyw aplikacji
> │   ├── fonts/          # lokalny font Oswald (licencja OFL)
> │   ├── js/
> │   └── vendor/         # lokalny Bootstrap 5.3 (CSS + JS, bez CDN)
> ├── tools/              # narzędzia CLI (migrate.php, migrate.sh, qa-smoke.sh)
> │   ├── migrate.php     # runner migracji PDO (uruchamiany w entrypoint.sh)
> │   ├── test-conflicts.php # 7 przypadków nakładania + excludeId (8/8 PASS)
> │   └── check-slots.php # weryfikacja algorytmu dostępności z przerwami (13/13 PASS)
> ├── .env.example        # wzór konfiguracji lokalnej
> ├── docker-compose.yml  # stos lokalny (app + db + phpMyAdmin)
> ├── LICENSE
> └── README.md

### `app/`
Główna część aplikacji zawierająca logikę systemu (kontrolery, modele, widoki i pomocniki).

### `config/`
Pliki konfiguracyjne aplikacji oraz dodatkowa konfiguracja serwera MySQL.

### `database/`
Pliki związane ze strukturą oraz inicjalizacją bazy danych
(`database-bez-rekordow.sql` — DDL, `database-tylko-rekordy.sql` — dane,
`database.sql` — scalony komplet; DDL zawsze przed danymi). Podkatalog `migrations/`
zawiera numerowane migracje dla baz, które już istnieją (zob. „Aktualizacja istniejącej bazy”).

### `docker/`
Pliki potrzebne do zbudowania obrazu aplikacji (Dockerfile oraz VirtualHost Apache).

### `deploy/`
Gotowy stos produkcyjny uruchamiany z obrazu GHCR (bez montowania kodu).
Zawiera `docker-compose.yml`, `.env.example` i `README.md` z instrukcją wdrożenia.

### `docs/`
Dokumentacja techniczna oraz materiały związane z projektem. `ERD.md` — aktualny diagram
(Mermaid, renderuje się na GitHubie) i opis 14 tabel. `BAZA.md` — mapa tabel, kluczy
i indeksów oraz uzasadnienie decyzji projektowych. `OBRONA.md` — mapa wymagań na kod,
scenariusz prezentacji i pytania z odpowiedziami. `ERD.jpg` — wersja archiwalna z etapu 1.
`tools/qa-manual.md` — checklista przeklikania przed prezentacją.

### `public/`
Publicznie dostępna część aplikacji, z której korzysta użytkownik. `public/index.php`
to jedyny punkt wejścia (front controller); tutaj trafiają też pliki CSS i JS.

---

## Wymagania

Do uruchomienia projektu wymagane jest środowisko umożliwiające uruchomienie aplikacji PHP oraz relacyjnej bazy danych.

Przed rozpoczęciem pracy należy upewnić się, że środowisko posiada:

* PHP,
* serwer WWW, np. Apache,
* bazę danych,
* odpowiednią wersję narzędzi wykorzystywanych przez projekt.

---

## Uruchomienie

Projekt uruchamiamy lokalnie przez Docker Compose (PHP 8.2 + Apache, MySQL 8.0, phpMyAdmin).
Wymagane są: Docker z Compose, Git oraz wolne porty `8082` (aplikacja) i `8084` (phpMyAdmin).

### Uruchomienie od zera (kopiuj-wklej)

```bash
git clone https://github.com/IgorStarega/OstatniWorekCementu.git
cd OstatniWorekCementu
cp .env.example .env
docker compose up -d --build
```

Czas startu: pierwszy build obrazu to zwykle 1–3 minuty, samo podniesienie stosu
z gotowym obrazem około 30–60 sekund (MySQL musi zaimportować `database/database.sql`
i przejść stan `healthy`). Gotowość sprawdzamy, aż strona odpowie kodem 200:

```bash
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8082/?page=home
```

### Adresy

* Aplikacja: <http://localhost:8082/> (przekierowuje na `/?page=home`)
* phpMyAdmin: <http://localhost:8084/> (logowanie jako `root`, hasło z `MYSQL_ROOT_PASSWORD`)

### Import bazy od zera

Przy pierwszym starcie (pusty katalog `data/mysql`) kontener `owc-db` automatycznie
importuje `database/database.sql` — struktura i dane testowe. Aby odtworzyć bazę ręcznie:

```bash
docker compose down
# katalog danych MySQL należy do użytkownika kontenera, dlatego usuwamy go z kontenera:
docker run --rm -v "$PWD/data:/data" mysql:8.0 sh -c 'rm -rf /data/mysql'
docker compose up -d
```

Można też zaimportować pliki ręcznie na działającym stosie:

```bash
docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/database.sql
```

### Migracje ręcznie

Entrypoint `app` uruchamia migracje automatycznie przy każdym starcie. Ręcznie:

```bash
docker compose exec -T app php /var/www/html/tools/migrate.php --status
docker compose exec -T app php /var/www/html/tools/migrate.php
```

Migracje są idempotentne i zapisują wykonane wersje w `schema_migrations`.
Szczegóły w sekcji [Aktualizacja istniejącej bazy](#aktualizacja-istniejącej-bazy).

### Testy po uruchomieniu

```bash
bash tools/qa-smoke.sh
docker compose exec -T app php tools/check-slots.php
docker compose exec -T app php tools/test-conflicts.php
```

### Konta testowe

Hasło wszystkich kont testowych (zapisane w bazie jako hash) to **`Haslo123!`**.
Konta są potwierdzone, więc logowanie działa od razu.

| Rola | E-mail | Imię i nazwisko |
|---|---|---|
| admin | `admin@ostatniworekcementu.pl` | Administrator Systemu |
| pracownik | `jan.kowalski@example.com` | Jan Kowalski |
| pracownik | `piotr.nowak@example.com` | Piotr Nowak |
| pracownik | `adam.wisniewski@example.com` | Adam Wiśniewski |
| pracownik | `marek.wojcik@example.com` | Marek Wójcik |
| klient | `anna.kowalska@example.com` | Anna Kowalska |
| klient | `tomasz.zielinski@example.com` | Tomasz Zieliński |

### Dane testowe

Rezerwacje oraz dzień wolny mają daty liczone względem dnia importu
(`CURDATE()` plus stały odstęp w dniach), więc zawsze istnieją rezerwacje
„na dziś”, „na jutro”, przyszła, zrealizowana (wczoraj) i anulowana.
Godziny dobrane są do okien pracowników tak, by każda rezerwacja wypadała
w dniu pracy wyznaczonego pracownika (a `end_time` = `start_time` + czas trwania usługi).
Rezerwacja anulowana służy do testu, że status `anulowana` nie blokuje terminu,
a urlop (+7 dni) — że dzień wolny blokuje wszystkie sloty danego dnia.

**Ograniczenia danych testowych.** Przerwy w grafiku ma tylko Adam (piętro niżej
opisane usługi 480 min). Powód jest arytmetyczny: usługa 480 minut w oknie pracy
480 minut (8:00–16:00) ma miejsce tylko wtedy, gdy w oknie nie ma przerwy — każda
przerwa przesuwa koniec poza okno i odbiera wszystkie sloty. Dlatego przerwy
demonstrujemy na krótszych usługach Adama, a nie na najdłuższej. To celowe
ograniczenie zestawu demo, nie błąd algorytmu.

---

## Obraz Docker (GHCR)

Obraz z kodem aplikacji (`app/`, `config/`, `public/`) buduje i publikuje workflow
`.github/workflows/docker.yml` w trzech krokach: **lint → test → publish**.

* `lint` — składnia PHP (`php -l`), JS (`node --check`), skryptów bash oraz
  poprawność obu plików `docker-compose.yml`,
* `test` — `docker compose up -d --build app` na `.env.example`, oczekiwanie na
  bazę, schemat i odpowiedź aplikacji, a następnie pełny `tools/qa-smoke.sh`
  (te same testy co lokalnie),
* `publish` — dopiero po teście trafiają do GHCR tagi: `latest`, `main` i
  `sha-<skrót>` (dla `main`) oraz `X.Y.Z` i `X.Y` (dla taga `vX.Y.Z`).

Pull requesty do `main` przechodzą lint i pełny test, ale nie publikują obrazu.
Aktualizacje akcji GitHub Actions pilnuje Dependabot (`.github/dependabot.yml`).

Adres obrazu: `ghcr.io/igorstarega/ostatniworekcementu:latest`.

Obraz jest samodzielny — zawiera kod aplikacji, więc wdrożenie nie potrzebuje
montowania repozytorium. **Gotowy plik deployu: `deploy/docker-compose.yml`
(instrukcja w `deploy/README.md`).**

> **Obraz nie zawiera zrzutu `database/database.sql`.** `.dockerignore` wyklucza
> pełne zrzuty SQL, ale dołącza `database/migrations/` oraz `tools/migrate.php`.
> Przy pierwszym uruchomieniu deploy **musi** dostarczyć `database.sql` osobno
> (`cp ../database/database.sql ./database.sql` w katalogu `deploy/`), natomiast
> kolejne migracje są aplikowane automatycznie przy starcie kontenera `app`.
> Brak pliku `database.sql` przy pierwszym starcie kończy się utworzeniem
> katalogu o tej nazwie i błędem `Can't initialize batch_readline`.

`latest` publikuje się **po** smoke teście obrazu z MySQL, więc nie zawiera
nieprzetestowanego kodu.

Lokalny `docker-compose.yml` nadal montuje repozytorium w `/var/www/html`, więc
rozwój działa bez przebudowy obrazu po każdej zmianie pliku.

> **Widoczność pakietu.** Nowy pakiet w GHCR jest domyślnie prywatny. Jeśli obraz
> ma być pobierany bez logowania, ustaw jego widoczność na publiczną w
> *GitHub → Packages → OstatniWorekCementu → Package settings*.

---

## Deploy bez lokalnego repozytorium

Gdy katalog deployu jest na innej maszynie niż repo:

```bash
curl -fsSL -o docker-compose.yml \
  https://raw.githubusercontent.com/IgorStarega/OstatniWorekCementu/main/deploy/docker-compose.yml
curl -fsSL -o database.sql \
  https://raw.githubusercontent.com/IgorStarega/OstatniWorekCementu/main/database/database.sql

test -f database.sql && echo "OK: plik" || echo "BŁĄD"
grep 'database.sql' docker-compose.yml   # musi być ./database.sql
```

**Nie kopiuj `docker-compose.yml` z pamięci ani ze starych commitów.** Wersja z PR #20
montowała `./database/database.sql` (podkatalog), co powodowało utworzenie katalogu
zamiast pliku i błąd `Can't initialize batch_readline`. Aktualna wersja montuje
`./database.sql` z katalogu głównego.

---

## Aktualizacja istniejącej bazy

Skrypt `database/database.sql` jest importowany tylko przy **pustym** katalogu danych
(`data/mysql`). Dla istniejącej bazy migracje z `database/migrations/` **aplikują się
automatycznie przy starcie kontenera `app`** (`docker/php/entrypoint.sh` uruchamia
`php /var/www/html/tools/migrate.php` z pętlą retry przed startem Apache i zapisuje
wykonane pliki w tabeli `schema_migrations`). Automat można wyłączyć zmienną
środowiskową `RUN_MIGRATIONS=0`.

Dostępne są również dwie ścieżki ręczne:

**(a) Zachowujemy dane (wariant ręczny na hoście z repozytorium)** — stosujemy migracje po kolei (stos musi działać):

```bash
bash tools/migrate.sh
```

lub bezpośrednio przez runner PHP w kontenerze:

```bash
docker compose exec app php /var/www/html/tools/migrate.php --status
docker compose exec app php /var/www/html/tools/migrate.php
```

Skrypt stosuje wszystkie pliki z `database/migrations/` w kolejności. Te same
migracje można też uruchomić ręcznie przez klienta `mysql`:

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/002-etap2.sql`

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/003-etap2.sql`

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/004-poprawka-kodowania.sql`

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/005-historia-statusow.sql`

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/006-log-administratora.sql`

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/007-dni-wolne.sql`

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/008-przerwy.sql`

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/009-indeksy.sql`

Migracje są idempotentne: sprawdzają `information_schema` przed każdym `ALTER TABLE`
(MySQL 8.0 nie ma `ADD COLUMN IF NOT EXISTS`), więc ponowne uruchomienie nic nie psuje.
Migracja `002` dodaje `users.failed_attempts`, `users.locked_until` i tabelę `password_resets`;
`003` dodaje `users.email_verified_at` oraz tabele `email_verifications` i `email_sends`,
a istniejące konta oznacza jako potwierdzone (`email_verified_at = NOW()`), żeby nie zablokować
dostępu po włączeniu weryfikacji e-mail. `004` naprawia podwójne kodowanie polskich znaków
w danych (`Ocieplanie budynkÃ³w` → `Ocieplanie budynków`); poprawione kolumny nie są ruszane
przy kolejnym uruchomieniu. `005` dodaje historię zmian statusów rezerwacji (`status_history`),
`006` — log operacji administratora (`admin_log`), `007` — dni wolne i urlopy pracowników
(`employee_days_off`), a `008` — przerwy w godzinach pracy (`employee_breaks`). `009` dodaje indeksy
`idx_reservations_user_date` i `idx_services_category_active` pod panel klienta i ofertę.
Po wszystkich migracjach baza ma **14 tabel aplikacji + schema_migrations (tabela techniczna)**.

**(b) Dane są nieważne** — zaczynamy od zera:

`docker compose down`, usuwamy katalog `data/mysql`, `docker compose up -d` — świeży import
`database/database.sql` zawiera już pełny schemat.

---

## Testy

Automatyczny smoke test to **115 asercji w kodzie** (`grep -c 'pass "' tools/qa-smoke.sh`),
a w pełnym przebiegu **146/146 PASS** — część asercji powtarzają pętle (role i strony
panelu). Zakres: sesja, CSRF, rejestracja z linkiem, weryfikacja, XSS, IDOR (GET i POST),
trzy role, blokada konta, dostęp do panelu administratora, operacje destrukcyjne,
paginacja i wyszukiwanie użytkowników, favicon, ochrona własnego konta, schemat bazy,
migracje, kodowanie polskich znaków, retencja, historia statusów, log operacji, dni
wolne, przerwy, statystyki, eksport CSV, proces rezerwacji (kolejność kroków, data
z przeszłości, godzina poza slotami, pracownik nieprzypisany do usługi), stronę główną,
AJAX `api-slots` (sukces, 403, 422), kalendarz miesiąca, przełożenie wizyty i jego IDOR,
panel klienta i pracownika oraz znikanie nieaktywnej usługi z oferty. Numeracja kończy
się na teście 96 — etap 5 dodał 71–96: CSRF na pięciu trasach, IDOR przez POST, walidację
rejestracji i formularzy admina, błędne daty i nieaktywne usługi, kolizję przy przełożeniu,
brak wycieku SQL/ścieżek, liczbę tabel, klucze główne i obce oraz nagłówki bezpieczeństwa.
Uruchamiamy przeciw działającemu stosowi:

```bash
bash tools/qa-smoke.sh
```

Skrypt wymaga tylko `bash`, `curl`, `docker compose` i `grep`, zakłada `APP_ENV=local`
(linki potwierdzenia i resetu są widoczne na ekranie), jest idempotentny i po sobie sprząta.
Wynik to `X/Y PASS`; kod wyjścia `1`, gdy cokolwiek padło. Rzeczy wymagające przeglądarki
(ocena wizualna, responsywność, czytelność błędów) są w checkliście `tools/qa-manual.md`.

---

## QA przed oddaniem etapu

### Automat

```bash
bash tools/qa-smoke.sh
```

Wynik: `146/146 PASS`, 0 FAIL, 2 SKIP (115 asercji w kodzie skryptu, resztę dają pętle).
SKIP-y wymagają przeglądarki lub restartu kontenera — opisane poniżej.

### Manualnie — czego automat nie sprawdzi

**Widok 500 przy złej konfiguracji.** Podmień `MYSQL_PASSWORD` w `.env` na błędne
(compose przekazuje je aplikacji jako `DB_PASS`):

```bash
docker compose up -d --force-recreate app
curl -s http://localhost:8082/?page=home | grep -c 'SQLSTATE\|Stack trace'
```

Oczekiwane: `0` — użytkownik widzi neutralny komunikat, szczegóły lądują
w `/var/log/php/error.log`. Przywróć poprawne `MYSQL_PASSWORD` po teście.

**Responsywność.** Panel administratora na 375 px: sidebar przechodzi nad treść,
brak poziomego przewijania, tabele czytelne.

### Kontrola dostępu (PDF 5.2)

Każdy adres `?page=admin-*` jako klient i jako gość → 403 albo redirect na login.
Automat pokrywa to testem 21 (8 stron × 3 role).

### Sekrety w historii Git

```bash
git log -p | grep -iE "password|secret|api[_-]?key" | head -50
```

Poza `.env.example` nie może nic wyjść.

---

## Konfiguracja

Konfiguracja lokalna jest w pliku `.env` (wzorem jest `.env.example`). Plik `.env` nie jest
w repozytorium — jest wpisany do `.gitignore`; po sklonowaniu odtwarzamy go przez `cp .env.example .env`. Ustawiamy tam m.in.:

* `APP_ENV` — `local` albo `prod` (w `prod` włączane jest `session.cookie_secure`, o ile żądanie
  przyszło po HTTPS — także za reverse proxy),
* `APP_URL` — publiczny adres aplikacji bez końcowego `/`; z niego (i tylko z niego) powstają linki
  absolutne przez helper `absoluteUrl()`. Lokalnie `http://localhost:8082` (domyślnie), na serwerze
  `https://owc.dziadu.dev`; w `prod` jest wymagany i musi zaczynać się od `https://`,
* `TRUSTED_PROXY_IPS` — opcjonalna lista zaufanych adresów reverse proxy (pojedyncze IP lub zapisy
  CIDR, np. `172.18.0.2`, `172.18.0.0/16`), rozdzielona przecinkami. Domyślnie **pusta** — nie
  ufamy całej prywatnej puli Dockera. Ustawiamy konkretny adres kontenera tunelu (np. `cloudflared`),
  żeby działała detekcja HTTPS z `X-Forwarded-Proto` (patrz „Reverse proxy”),
* `BIND_ADDR` — interfejs publikowanych portów (domyślnie `127.0.0.1`, czyli tylko lokalnie),
* `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD` — inicjalizacja MySQL,
  a `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` dla aplikacji compose wyprowadza
  automatycznie z `MYSQL_*` i stałych wartości (`db`, `3306`) — nie trzeba ich dublować w `.env`,
* `PMA_USER`, `PMA_PASSWORD` — dane logowania do phpMyAdmin (host i port są stałe
  w `docker-compose.yml`).

**Reverse proxy.** Za tunelem Cloudflare aplikacja widzi zwykłe HTTP, dlatego protokół wykrywa z
`X-Forwarded-Proto` / `CF-Visitor`. Pętla lokalna (`127.*`, `::1`) jest zaufana zawsze; adresy
tunelu działającego jako kontener (np. `cloudflared` z sieci Dockera) podajemy w `TRUSTED_PROXY_IPS`.
Te nagłówki są wiarygodne tylko wtedy, gdy origin jest osiągalny wyłącznie przez tunel (porty na
pętli lokalnej, `BIND_ADDR=127.0.0.1`) — dlatego domyślnie lista jest pusta, a kod ignoruje te
nagłówki dla żądań z nieznanych adresów.

**Weryfikacja e-mail (polityka B).** Konto musi mieć potwierdzony adres, żeby się zalogować.
Po rejestracji aplikacja generuje link z tokenem (w bazie trzymany jest wyłącznie hash SHA-256,
ważny 24 godziny) i pokazuje go bezpośrednio na ekranie — nie ma transportu poczty. Ponowne
wygenerowanie linku ogranicza cooldown 60 s i limit 3 na godzinę (liczniki w bazie `email_sends`).
Konta testowe i konta istniejące przed tą zmianą są oznaczone jako potwierdzone, więc QA i demo
działają bez zmian.

> **Uwaga operacyjna (wdrożenie).** Kontroler potwierdzenia e-mail ustala stan konta na podstawie
> bazy (`email_verified_at`), więc nie zależy od danych zapisanych w starej sesji. Po wdrożeniu
> i tak warto poprosić użytkowników o ponowne zalogowanie — najprościej wyczyścić sesje serwera.

Plik `config/config.php` czyta `DB_*` oraz `APP_*` (przekazywane przez compose) i przy ich braku
zgłasza jasny błąd.

**Nie należy umieszczać prawdziwych haseł oraz danych dostępowych do produkcyjnej bazy danych w repozytorium Git.**

---

## Bezpieczeństwo

Mechanizmy wdrożone w kodzie (szczegóły i miejsca w plikach: [`docs/OBRONA.md`](docs/OBRONA.md)):

* **SQL Injection** — wszystkie zapytania z danymi użytkownika przechodzą przez PDO
  prepared statements (`prepare()` + `execute([...])`); nie ma sklejania danych z SQL.
  Stałe zapytania bez parametrów (np. liczniki statystyk) mogą używać `query()`.
* **XSS** — każde wyjście z bazy lub formularza trafia do HTML przez
  `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`; dotyczy to też atrybutów `href` i `value`.
* **CSRF** — token losowany `random_bytes(32)` w sesji, porównywany `hash_equals()`;
  każdy POST (logowanie, rejestracja, profil, rezerwacja, anulowanie, przełożenie,
  panel administratora, wylogowanie) wymaga poprawnego tokenu i kończy się 403.
* **IDOR** — klient czyta rezerwacje wyłącznie przez `findForUser()`/`forUser()`;
  cudze i nieistniejące ID daje **404**, nie 403 (nie zdradzamy istnienia zasobu).
  Profil bierze ID wyłącznie z sesji, a nie z `?id=`; admin ma `requireRole('admin')`.
* **Hasła** — zapis `password_hash()` (bcrypt), weryfikacja `password_verify()`;
  hasła nigdy nie trafiają do sesji ani do logów. Po zmianie hasła sesja jest odnawiana
  (`session_regenerate_id(true)`).
* **Sesja** — `session.use_strict_mode=1`, ciasteczko `httponly`, `samesite=Lax`,
  a w `APP_ENV=prod` po HTTPS także `secure`. Samo ID i rola w sesji, bez danych osobowych.
* **Tokeny jednorazowe** — reset hasła i weryfikacja e-mail trzymają w bazie tylko
  SHA-256 tokenu, mają datę ważności (1 h / 24 h) i są usuwane po użyciu.
* **Komunikaty błędów** — użytkownik widzi neutralny opis, a szczegóły wyjątków
  (SQLSTATE, ścieżki, stack trace) trafiają wyłącznie do `error_log`.
* **Sekrety** — plik `.env` nie jest w repozytorium (jest w `.gitignore`); konfigurację
  odtwarzamy z `.env.example`. W historii Git nie ma kluczy ani haseł produkcyjnych.
* **CSV** — eksport chroni komórki przed formula injection (prefiks `'` dla wartości
  zaczynających się od `=`, `+`, `-`, `@`, także po białych znakach).

---

## Funkcje dodatkowe

### Etap 4 — funkcje dodatkowe (wdrożone)

* **AJAX-owe terminy:** endpoint `?page=api-slots` zwraca JSON z walidacją identyczną
  jak formularz; siatka radio w kroku godziny pozostaje fallbackiem bez JavaScriptu.
* **Kalendarz miesiąca:** krok daty pokazuje dni z liczbą wolnych slotów, z podziałem
  na przeszłe, dni wolne, wolne i pełne; liczba zapytań to ok. 5 na dzień (≈150 na miesiąc),
  opisana w README jako świadomy kompromis.
* **Przerwy w godzinach pracy:** tabela `employee_breaks` i migracja 008; przerwy
  realnie odejmują sloty, a administrator zarządza nimi w module dostępności.
* **Przełożenie wizyty:** klient może zmienić termin własnej przyszłej wizyty;
  `Reservation::reschedule()` używa `excludeId` w `hasConflict()` i blokady wiersza.
* **Strona główna z realną ofertą:** liczniki, kategorie i polecane usługi z bazy
  zamiast placeholdera z etapu 1.

**Świadomie poza zakresem:** e-maile i przypomnienia o wizytach. Na deployu nie ma
transportu SMTP — Cloudflare Tunnel nie przepuszcza SMTP, a port 25 jest zablokowany.
Linki weryfikacji i resetu hasła są dlatego pokazywane na ekranie w trybie lokalnym.

### Podsumowanie funkcji dodatkowych

Zrealizowane rozszerzenia ponad minimum: AJAX-owe wolne terminy, kalendarz miesiąca,
przerwy w grafiku, dni wolne/urlopy, historia statusów, log operacji administratora,
eksport CSV, migracje z `schema_migrations`, reset hasła, weryfikacja e-mail,
testy automatyczne (smoke, konflikty, sloty) i responsywny interfejs.

### Świadome braki i uzasadnienie

| Brak | Dlaczego |
|---|---|
| Wysyłka e-maili i przypomnień | Brak transportu SMTP na deployu (Cloudflare Tunnel nie przepuszcza SMTP, port 25 zablokowany). Linki są pokazywane na ekranie w trybie lokalnym. |
| Powiadomienia SMS/push | Wymagałyby zewnętrznego dostawcy i sekretów; poza zakresem projektu szkolnego. |
| Generowanie PDF | Nie jest wymagane przez PDF zadań; CSV pokrywa eksport danych. |
| REST API | Aplikacja jest serwerowa (MVC + formularze); jedyny endpoint JSON (`api-slots`) obsługuje AJAX. |
| Płatności online | Brak bramki płatniczej i umowy; rezerwacja jest zapisem wizyty, nie sprzedażą. |

Zgodnie z PDF 15.4 niedokończone dodatki nie zastępują funkcji obowiązkowych,
dlatego powyższe elementy są świadomie poza zakresem, a nie „w trakcie prac”.

### Etap 3 — panel administratora (wdrożone)

* **Wspólny layout panelu:** sidebar z dziewięcioma modułami (Dashboard, Kategorie, Usługi,
  Pracownicy, Dostępność, Dni wolne, Użytkownicy, Rezerwacje, Log operacji). Moduł
  **Przypisania usług** jest dostępny z poziomu zarządzania pracownikami, ale nie jest
  osobną pozycją sidebaru. Layout jest responsywny (poniżej 768 px sidebar przechodzi nad treść), a kontrola dostępu `requireRole('admin')`
  w każdej metodzie `AdminController`.
* **Dashboard z rzeczywistymi danymi:** liczniki klientów, pracowników, usług, wszystkich
  rezerwacji i rezerwacji na dziś oraz lista pięciu najbliższych rezerwacji bez anulowanych,
  z linkami do szczegółów.
* **CRUD kategorii usług:** dodawanie, edycja i usuwanie z potwierdzeniem; usunięcie jest
  blokowane, gdy kategoria ma usługi (komunikat z liczbą).
* **CRUD usług:** kategoria z listy, cena (również `0.00`), czas trwania, aktywność
  przełączana jednym przyciskiem; usługa z rezerwacjami nie zostanie usunięta.
* **Pracownicy:** konto `users` (rola `pracownik`) i profil `employees` powstają w jednej
  transakcji PDO; duplikat e-maila jest odrzucany, a dezaktywacja blokuje logowanie.
  Zmiana roli użytkownika synchronizuje profil `employees`, a blokowanie/odblokowanie
  konta synchronizuje `users.is_active` z `employees.is_active`.
* **Relacja N:M:** przypisywanie wielu usług do pracownika (`employee_services`) z walidacją
  ID po stronie serwera i zapisem w transakcji.
* **Dostępność pracownika:** tygodniowe okna pracy w tabeli `employee_availability`;
  konwencja `day_of_week`: 1 = poniedziałek, 7 = niedziela.
* **Rezerwacje:** filtry (data od–do, status, pracownik), wyszukiwanie po kliencie lub nazwie
  usługi, zmiana statusu przez POST; anulowanie rezerwacji to zmiana statusu, nie osobny link.
* **Użytkownicy:** filtry roli i aktywności, wyszukiwanie po imieniu, nazwisku i e-mailu oraz
  ochrona własnego konta przed odebraniem uprawnień lub zablokowaniem.
* **Paginacja:** 20 rekordów na stronę w listach rezerwacji i użytkowników; aktywne filtry
  są zachowywane między stronami.
* **Bezpieczne operacje:** usuwanie wyłącznie metodą POST, z potwierdzeniem `data-confirm`;
  komunikaty błędów nie ujawniają danych technicznych (szczegóły trafiają do logu).
* **Wygląd:** ciemny, industrialny motyw „beton i stal” (grafit, żółty akcent ostrzegawczy,
  pasy hazardowe, kondensowana typografia Oswald) oparty na lokalnym Bootstrapie 5.3
  (`public/vendor/`) i `data-bs-theme="dark"`, bez zewnętrznego CDN.

### Etap 2 — wdrożone

* **Obsługa błędów bez ujawniania danych technicznych:** `display_errors` wyłączone w `prod`, szczegóły
  wyjątków trafiają tylko do logu, a użytkownik widzi neutralne widoki `app/views/errors/` (403, 404, 500).
* **Limit prób logowania i blokada konta:** po 5 nieudanych logowaniach konto jest blokowane na
  15 minut (`users.failed_attempts`, `users.locked_until`).
* **Zmiana hasła po podaniu starego:** w profilu wymagane jest obecne hasło, po zmianie odnawiany jest
  identyfikator sesji.
* **Reset hasła tokenem jednorazowym:** token w linku jest losowy, w bazie trzymany tylko jako SHA-256
  (`password_resets`), ważny 1 godzinę i usuwany po użyciu; komunikat nie zdradza, czy konto istnieje.
  Link jest generowany i pokazywany na ekranie, a jego tworzenie ogranicza cooldown 60 s i limit
  3/godzinę.
* **Weryfikacja adresu e-mail (polityka B):** po rejestracji generujemy link z tokenem (SHA-256 w bazie,
  ważny 24 godziny); logowanie dozwolone dopiero po potwierdzeniu. Ponowne wygenerowanie linku ma te
  same limity (60 s / 3 na godzinę), a komunikaty nie ujawniają, czy konto istnieje. Konta testowe są
  oznaczone jako potwierdzone. Link potwierdzający jest pokazywany na ekranie, więc konto da się
  potwierdzić i zalogować bez transportu poczty.
* **Bezpieczne zarządzanie sesją:** `use_strict_mode`, `httponly`, `samesite=Lax`, `cookie_secure` w `prod`
  po HTTPS oraz tokeny CSRF we wszystkich formularzach (logout wyłącznie przez POST + CSRF).

### Etap 1

* **Zamrożenie ceny w rezerwacji:** tabela `reservations` przechowuje `final_price` niezależnie od cennika — wdrożone.
* **Wyszukiwanie i paginacja list administracyjnych:** wdrożone w etapie 3 (rezerwacje i użytkownicy).
* **AJAX z terminami:** zrealizowany w etapie 4 — `?page=api-slots` z walidacją
  identyczną jak formularz i fallbackiem bez JavaScriptu. **Historia zmian
  statusów rezerwacji** jest wdrożona w `status_history` i obsługiwana w panelu
  administratora.

---

### Etap 3 — funkcje dodatkowe i zabezpieczenia

| Funkcja | Status |
|---|---|
| Dni wolne i urlopy pracowników | wdrożone |
| Historia zmian statusów rezerwacji | wdrożona |
| Log operacji administratora | wdrożony |
| Eksport rezerwacji do CSV | wdrożony |
| Statystyki na dashboardzie | wdrożone |
| Synchronizacja `users` ↔ `employees` przy zmianie roli i blokadzie konta | wdrożona |
| Ochrona usuwania dnia wolnego przed zmianą pracownika | wdrożona |
| Ochrona eksportu CSV przed formula injection | wdrożona |

---

## Autorzy

Igor Staręga
Maciej Radzikowski

Projekt został wykonany w ramach przedmiotu **TSiAI**.

**OstatniWorekCementu**
System rezerwacji usług budowlanych – projekt semestralny 5IT.

---

## Status projektu

Projekt jest rozwijany w ramach prac nad systemem rezerwacji usług budowlanych.

### Etap 4 — rezerwacje, panel klienta i pracownika

| # | Zadanie | Status |
|---|---------|--------|
| 1 | `AvailabilityService` — okna pracy, urlopy, rezerwacje, siatka 15 min | ukończone |
| 2 | `Reservation::hasConflict()` + `create()` w transakcji z blokadą wierszy | ukończone (8/8 PASS) |
| 3 | Proces rezerwacji: usługa → pracownik → data → godzina → potwierdzenie | ukończone |
| 4 | Panel klienta: lista, szczegóły, historia statusów, anulowanie | ukończone |
| 5 | Panel pracownika: dziś / przyszłe / historia, zmiana statusu | ukończone |
| 6 | Ochrona IDOR (404 dla cudzych ID) i CSRF wszystkich POST | ukończone |
| 7 | Testy 45–69 w `tools/qa-smoke.sh` + `test-conflicts.php` + `check-slots.php` | ukończone |
| 8 | Dokumentacja procesu, reguły konfliktów i algorytmu dostępności | ukończone |
| 9 | UX/responsywność 375 px (#99) | ukończone (CSS, empty states, przyciski 44 px; checklista manualna) |
| 10 | AJAX z terminami (`?page=api-slots` + fallback radio) | ukończone |
| 11 | Kalendarz miesiąca z liczbą wolnych terminów | ukończone |
| 12 | Przerwy w godzinach pracy (migracja 008) | ukończone |
| 13 | Przełożenie wizyty przez klienta (`excludeId`) | ukończone |

### Etap 3 — panel administratora

| # | Zadanie | Status |
|---|---------|--------|
| 1 | Layout panelu, routing, kontrola dostępu | ukończone |
| 2 | Dashboard z licznikami i najbliższymi rezerwacjami | ukończone |
| 3 | CRUD kategorii usług | ukończone |
| 4 | CRUD usług (kategoria, cena, czas, aktywność) | ukończone |
| 5 | Zarządzanie pracownikami i zakładanie kont | ukończone |
| 6 | Przypisywanie wielu usług pracownikowi (N:M) | ukończone |
| 7 | Zarządzanie dostępnością pracownika | ukończone |
| 8 | Zarządzanie użytkownikami (role, blokada) | ukończone |
| 9 | Moduł rezerwacji (lista, filtry, status) | ukończone |
| 10 | Paginacja i wyszukiwanie | ukończone |
| 11 | Ciemny motyw Bootstrap 5.3 | ukończone |
| 12 | Obraz GHCR i deploy | ukończone |

### Etap 2 — zadania

| # | Zadanie | Status |
|---|---------|--------|
| 1 | Hartowanie sesji (strict mode, httponly, samesite, timezone) | ukończone |
| 2 | Kontenery: porty, sekrety, nazwy, strefa czasowa, bind mount | ukończone |
| 3 | Konfiguracja bazy spójna z `.env` | ukończone |
| 4 | Ochrona CSRF formularzy logowania i rejestracji | ukończone |
| 5 | Logout wyłącznie w kontrolerze (POST + CSRF) | ukończone |
| 6 | Ochrona stron i mechanizm ról (#33) | ukończone |
| 7 | Panel klienta i edycja profilu (#34) | ukończone |
| 8 | Integralność rezerwacji w schemacie bazy | ukończone |
| 9 | Warstwa wizualna (CSS) i dokumentacja | ukończone |
| 10 | `APP_URL` i linki absolutne za reverse proxy (#18) | ukończone |
| 11 | Migracje dla istniejących baz (#19) | ukończone |
| 12 | README, struktura i porządki w repo (#20) | ukończone |
| 13 | Reset hasła i limity wysyłek (#71) | ukończone |
| 14 | Weryfikacja adresu e-mail po rejestracji, polityka B (#72) | ukończone |
| 15 | Usunięcie transportu poczty (linki na ekranie) i minimalizacja `.env` | ukończone |

Kolejne elementy systemu mogą obejmować powiadomienia
o rezerwacjach oraz dalsze usprawnienia obsługi zleceń.