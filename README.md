# OstatniWorekCementu

System internetowy do obsługi firmy budowlanej oraz rezerwacji usług budowlanych. Aplikacja umożliwia klientom zapoznanie się z ofertą firmy oraz składanie rezerwacji, a pracownikom i administratorom zarządzanie usługami oraz realizowanymi zleceniami.

## Spis treści

* [Opis projektu](#opis-projektu)
* [Główne funkcjonalności](#główne-funkcjonalności)
* [Technologie](#technologie)
* [Baza danych](#baza-danych)
* [Dostępność pracowników](#dostępność-pracowników)
* [Role użytkowników](#role-użytkowników)
* [Dane testowe (Logowanie)](#dane-testowe-logowanie)
* [Zakres projektu](#zakres-projektu)
* [Struktura projektu](#struktura-projektu)
* [Wymagania](#wymagania)
* [Uruchomienie](#uruchomienie)
* [Deploy bez lokalnego repozytorium](#deploy-bez-lokalnego-repozytorium)
* [Testy](#testy)
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

System korzysta z relacyjnej bazy danych odpowiedzialnej za przechowywanie informacji związanych z użytkownikami, usługami oraz rezerwacjami. Schemat obejmuje **10 tabel**.

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

## Dostępność pracowników

Tabela `employee_availability` przechowuje tygodniowe okna pracy pracownika.

**Konwencja `day_of_week`:** 1 = poniedziałek, 7 = niedziela.

Jeden wiersz na pracownika i dzień (`UNIQUE uq_employee_day`), `start_time` musi być
ostro mniejsze niż `end_time`. Etap 4 (proces rezerwacji) wyznacza wolne terminy
na podstawie tych okien i `services.duration_minutes`.

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
> │   ├── controllers/    # kontrolery (Home, Auth, PasswordReset, EmailVerification, Profile, Dashboard, Admin)
> │   ├── models/         # modele (Database, User, PasswordReset, EmailVerification, EmailSend,
> │   │                   #         ServiceCategory, Service, Employee, EmployeeService,
> │   │                   #         EmployeeAvailability, Reservation, Stats)
> │   ├── helpers/        # pomocniki (CSRF, autoryzacja, walidacja, paginacja, adresy URL, tokeny)
> │   └── views/          # widoki (layout, strony, admin/, errors/ — 403/404/500)
> ├── config/             # konfiguracja aplikacji i serwera MySQL
> │   └── mysql/
> ├── database/           # schemat, dane testowe i scalony zrzut
> │   └── migrations/     # migracje dla istniejących baz (002, 003, 004)
> ├── docker/             # obraz PHP, php.ini i konfiguracja Apache
> │   └── php/
> ├── deploy/             # deploy produkcyjny z obrazu GHCR (compose + instrukcja)
> ├── docs/               # dokumentacja (diagram ERD)
> ├── public/             # publiczny katalog serwera (front controller)
> │   ├── css/            # industrialny ciemny motyw aplikacji
> │   ├── fonts/          # lokalny font Oswald (licencja OFL)
> │   ├── js/
> │   └── vendor/         # lokalny Bootstrap 5.3 (CSS + JS, bez CDN)
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
phpMyAdmin oraz ustawienia portów (`BIND_ADDR`). Strefa czasowa jest stała
(`Europe/Warsaw`) i wpisana bezpośrednio w `docker-compose.yml`.

### 3. Uruchomienie stosu

`docker compose up -d --build`

### 4. Adresy

* Aplikacja: `http://localhost:8082/` (przekierowuje na `/?page=home`)
* phpMyAdmin: `http://localhost:8084/` (logowanie jako `root`, hasło z `MYSQL_ROOT_PASSWORD`)

### 5. Dane testowe

Przy pierwszym starcie kontener `owc-db` importuje `database/database.sql`
(struktura + dane testowe). Hasło wszystkich kont testowych to `Haslo123!`.

---

## Obraz Docker (GHCR)

Obraz z kodem aplikacji (`app/`, `config/`, `public/`) buduje i publikuje workflow
`.github/workflows/docker.yml` w trzech krokach: **build → test → publish**.

* `build` — walidacja składni PHP (`php -l`) i budowa obrazu; na push powstaje
  tylko tymczasowy tag `sha-<sha commita>`,
* `test` — obraz jest uruchamiany z MySQL, importowany jest `database/database.sql`
  i wykonywany jest smoke test HTTP,
* `publish` — dopiero po teście trafiają do GHCR tagi: `latest`, `main` i
  `sha-<skrót>` (dla `main`) oraz `X.Y.Z` i `X.Y` (dla taga `vX.Y.Z`).

Pull requesty do `main` tylko lintują i budują obraz — bez push i bez publikacji.

Adres obrazu: `ghcr.io/igorstarega/ostatniworekcementu:latest`.

Obraz jest samodzielny — zawiera kod aplikacji, więc wdrożenie nie potrzebuje
montowania repozytorium. **Gotowy plik deployu: `deploy/docker-compose.yml`
(instrukcja w `deploy/README.md`).**

> **Obraz nie zawiera `database/`.** `.dockerignore` celowo wyklucza ten katalog,
> więc deploy **musi** dostarczyć `database.sql` osobno
> (`cp ../database/database.sql ./database.sql` w katalogu `deploy/`) albo
> zastosować migracje dla istniejącej bazy. Brak pliku kończy się utworzeniem
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
(`data/mysql`). Jeśli baza już istnieje, nowe kolumny i tabele z etapu 2 trzeba dołożyć
jedną z dwóch ścieżek:

**(a) Zachowujemy dane** — stosujemy migracje po kolei (stos musi działać):

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/002-etap2.sql`

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/003-etap2.sql`

`docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database/migrations/004-poprawka-kodowania.sql`

Migracje są idempotentne: sprawdzają `information_schema` przed każdym `ALTER TABLE`
(MySQL 8.0 nie ma `ADD COLUMN IF NOT EXISTS`), więc ponowne uruchomienie nic nie psuje.
Migracja `002` dodaje `users.failed_attempts`, `users.locked_until` i tabelę `password_resets`;
`003` dodaje `users.email_verified_at` oraz tabele `email_verifications` i `email_sends`,
a istniejące konta oznacza jako potwierdzone (`email_verified_at = NOW()`), żeby nie zablokować
dostępu po włączeniu weryfikacji e-mail. `004` naprawia podwójne kodowanie polskich znaków
w danych (`Ocieplanie budynkÃ³w` → `Ocieplanie budynków`); poprawione kolumny nie są ruszane
przy kolejnym uruchomieniu.

**(b) Dane są nieważne** — zaczynamy od zera:

`docker compose down`, usuwamy katalog `data/mysql`, `docker compose up -d` — świeży import
`database/database.sql` zawiera już pełny schemat.

---

## Testy

Automatyczny smoke test (36 sekcji: sesja, CSRF, rejestracja z linkiem, weryfikacja, XSS, IDOR,
trzy role, blokada konta, dostęp do panelu administratora, operacje destrukcyjne, paginacja
i wyszukiwanie, schemat bazy, migracje, kodowanie polskich znaków, retencja) uruchamiamy
przeciw działającemu stosowi:

```bash
bash tools/qa-smoke.sh
```

Skrypt wymaga tylko `bash`, `curl`, `docker compose` i `grep`, zakłada `APP_ENV=local`
(linki potwierdzenia i resetu są widoczne na ekranie), jest idempotentny i po sobie sprząta.
Wynik to `X/Y PASS`; kod wyjścia `1`, gdy cokolwiek padło. Rzeczy wymagające przeglądarki
(ocena wizualna, responsywność, czytelność błędów) są w checkliście `tools/qa-manual.md`.

---

## Konfiguracja

Konfiguracja lokalna jest w pliku `.env` (wzorem jest `.env.example`). Ustawiamy tam m.in.:

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

### Etap 3 — panel administratora (wdrożone)

* **Wspólny layout panelu:** sidebar z ośmioma modułami (Dashboard, Kategorie, Usługi,
  Pracownicy, Przypisania usług, Dostępność, Użytkownicy, Rezerwacje), responsywna siatka
  (poniżej 768 px sidebar przechodzi nad treść) i kontrola dostępu `requireRole('admin')`
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
* **AJAX z terminami i historia zmian statusów:** zaplanowane — brak ich jeszcze w kodzie.

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

Kolejne elementy systemu mogą obejmować panel pracownika, proces rezerwacji po stronie
klienta oraz dalsze usprawnienia obsługi rezerwacji.