# OstatniWorekCementu

System internetowy do obsługi firmy budowlanej oraz rezerwacji usług budowlanych. Aplikacja umożliwia klientom zapoznanie się z ofertą firmy oraz składanie rezerwacji, a pracownikom i administratorom zarządzanie usługami oraz realizowanymi zleceniami.

## Spis treści

* [Opis projektu](#opis-projektu)
* [Główne funkcjonalności](#główne-funkcjonalności)
* [Technologie](#technologie)
* [Baza danych](#baza-danych)
* [Role użytkowników](#role-użytkowników)
* [Dane testowe (Logowanie)](#dane-testowe-logowanie)
* [Zakres projektu](#zakres-projektu)
* [Struktura projektu](#struktura-projektu)
* [Wymagania](#wymagania)
* [Uruchomienie](#uruchomienie)
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
* administracja systemem.

---

## Technologie

Projekt został przygotowany jako aplikacja internetowa.

W projekcie wykorzystywane są technologie znajdujące się w katalogach aplikacji oraz konfiguracji projektu, między innymi:

* PHP,
* HTML5,
* CSS,
* JavaScript,
* SQL / relacyjna baza danych.

Szczegółowa konfiguracja aplikacji znajduje się w katalogach:

* `app/` – logika aplikacji,
* `config/` – konfiguracja,
* `database/` – pliki związane z bazą danych,
* `docs/` – dokumentacja projektu,
* `public/` – publiczna część aplikacji.

---

## Baza danych

System korzysta z relacyjnej bazy danych odpowiedzialnej za przechowywanie informacji związanych z użytkownikami, usługami oraz rezerwacjami.

Baza danych umożliwia między innymi przechowywanie:

* danych użytkowników,
* informacji o pracownikach,
* listy usług budowlanych,
* informacji o rezerwacjach,
* terminów realizacji usług,
* statusów zleceń,
* dodatkowych informacji przekazanych przez klienta.

Pliki związane z bazą danych znajdują się w katalogu: `database/`

---

## Role użytkowników

System został zaprojektowany z podziałem na role.

### Klient

Klient korzysta z publicznej części serwisu oraz może dokonywać rezerwacji usług.

Przykładowe możliwości:

> Przeglądanie usług ➔ Wybór usługi ➔ Wybór terminu ➔ Podanie informacji o zleceniu ➔ Utworzenie rezerwacji

### Pracownik

Pracownik odpowiada za realizację zleceń oraz obsługę powierzonych mu usług.

### Administrator

Administrator posiada rozszerzone uprawnienia pozwalające na zarządzanie systemem, użytkownikami, usługami oraz rezerwacjami.

---

## Dane testowe (Logowanie)

Aby ułatwić testowanie systemu, baza danych została zasilona przykładowymi użytkownikami. Hasło dla wszystkich kont testowych (zhashowane w bazie) to: **Haslo123!**

**Konto Administratora:**
* **Email:** `admin@ostatniworekcementu.pl` (Administrator Systemu)

**Konta Pracowników:**
* **Email:** `jan.kowalski@example.com` (Jan Kowalski)
* **Email:** `piotr.nowak@example.com` (Piotr Nowak)
* **Email:** `adam.wisniewski@example.com` (Adam Wiśniewski)
* **Email:** `marek.wojcik@example.com` (Marek Wójcik)

**Konta Klientów:**
* **Email:** `anna.kowalska@example.com` (Anna Kowalska)
* **Email:** `tomasz.zielinski@example.com` (Tomasz Zieliński)

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
> │   ├── controllers/    # kontrolery (Home, Auth, Profile, Dashboard)
> │   ├── models/         # modele (Database, User)
> │   ├── helpers/        # pomocniki (CSRF, autoryzacja)
> │   └── views/          # widoki (layout, strony, błędy)
> ├── config/             # konfiguracja aplikacji i serwera MySQL
> │   └── mysql/
> ├── database/           # schemat, dane testowe i scalony zrzut
> ├── docker/             # obraz PHP + konfiguracja Apache
> │   └── php/
> ├── docs/               # dokumentacja (diagram ERD)
> ├── public/             # publiczny katalog serwera (front controller)
> │   ├── css/
> │   └── js/
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
`database.sql` — scalony komplet; DDL zawsze przed danymi).

### `docker/`
Pliki potrzebne do zbudowania obrazu aplikacji (Dockerfile oraz VirtualHost Apache).

### `docs/`
Dokumentacja techniczna oraz materiały związane z projektem, m.in. diagram ERD.

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

### 1. Klonowanie repozytorium

Polecenie w terminalu:
`git clone [https://github.com/IgorStarega/OstatniWorekCementu.git](https://github.com/IgorStarega/OstatniWorekCementu.git)`
`cd OstatniWorekCementu`

### 2. Przygotowanie konfiguracji

Skopiuj wzór konfiguracji i w razie potrzeby dostosuj wartości:

`cp .env.example .env`

Plik `.env` jest w `.gitignore` — nie commitujemy go. Zawiera dane dostępowe MySQL,
phpMyAdmin oraz ustawienia portów (`BIND_ADDR`) i strefy czasowej (`TZ`).

### 3. Uruchomienie stosu

`docker compose up -d --build`

### 4. Adresy

* Aplikacja: `http://localhost:8082/` (przekierowuje na `/?page=home`)
* phpMyAdmin: `http://localhost:8084/` (logowanie jako `root`, hasło z `MYSQL_ROOT_PASSWORD`)

### 5. Dane testowe

Przy pierwszym starcie kontener `owc-db` importuje `database/database.sql`
(struktura + dane testowe). Hasło wszystkich kont testowych to `Haslo123!`.

---

## Konfiguracja

Konfiguracja lokalna jest w pliku `.env` (wzorem jest `.env.example`). Ustawiamy tam m.in.:

* `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` — połączenie aplikacji z bazą,
* `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD` — inicjalizacja MySQL,
* `PMA_HOST`, `PMA_PORT`, `PMA_USER`, `PMA_PASSWORD`, `PMA_ARBITRARY` — phpMyAdmin,
* `BIND_ADDR` — interfejs publikowanych portów, `TZ` — strefa czasowa,
* `APP_ENV` — `local` albo `prod` (w `prod` włączane jest `session.cookie_secure`).

Plik `config/config.php` czyta te zmienne i przy ich braku zgłasza jasny błąd.

**Nie należy umieszczać prawdziwych haseł oraz danych dostępowych do produkcyjnej bazy danych w repozytorium Git.**

---

## Bezpieczeństwo

Podczas wdrażania aplikacji w środowisku produkcyjnym należy zadbać między innymi o:

* bezpieczne przechowywanie haseł użytkowników,
* walidację danych przesyłanych przez formularze,
* ochronę przed SQL Injection,
* ochronę przed XSS,
* odpowiednie zarządzanie sesjami,
* kontrolę dostępu do poszczególnych funkcji systemu,
* niewprowadzanie danych dostępowych do repozytorium.

---

## Funkcje dodatkowe

W ramach rozszerzenia podstawowej funkcjonalności systemu wdrożone zostaną następujące rozwiązania:
* **Rozbudowana architektura bazy danych:** Wdrożenie mechanizmu "zamrożenia" ceny usługi w tabeli rezerwacji (odporność na zmianę cennika w czasie).
* **Wyszukiwanie i paginacja:** Ułatwione przeglądanie większych list (np. rezerwacji, usług czy użytkowników) w panelu administratora.
* **AJAX i dynamiczne pobieranie terminów:** Płynne ładowanie dostępnych godzin pracy pracownika bez przeładowywania całej strony podczas wyboru usługi.
* **Historia zmian statusów:** Rejestrowanie logów i operacji zmiany statusu rezerwacji dokonywanych przez pracowników i administratorów.

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

Kolejne elementy systemu mogą obejmować rozwój panelu pracownika i administratora
(usługi, harmonogramy, rezerwacje) oraz dalsze usprawnienia procesu obsługi rezerwacji.