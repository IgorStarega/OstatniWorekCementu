# Deploy produkcyjny — obraz GHCR

Gotowy stos produkcyjny: aplikacja z `ghcr.io/igorstarega/ostatniworekcementu`,
MySQL 8.0 i phpMyAdmin. **Nie służy do rozwoju** — do tego jest
`docker-compose.yml` w katalogu głównym (build z kodu i bind mount).

Wszystkie polecenia wykonuj w katalogu `deploy/`.

## Pierwsze uruchomienie

### 1. Konfiguracja

```bash
cp .env.example .env
```

Uzupełnij hasła. **`PMA_PASSWORD` musi być identyczne z `MYSQL_ROOT_PASSWORD`** —
phpMyAdmin loguje się do bazy jako `root`. Ustaw też `APP_URL` (w `prod` musi
zaczynać się od `https://`) oraz `TRUSTED_PROXY_IPS`, jeśli przed aplikacją stoi
kontener reverse proxy (np. `cloudflared`).

### 2. Schemat bazy — obowiązkowo

```bash
cp ../database/database.sql ./database.sql
```

Sprawdź, że to **plik**, a nie katalog:

```bash
test -f database.sql && echo "OK: plik" || echo "BŁĄD: brak pliku"
```

Import wykonuje się **tylko raz**, przy pustym `./data/mysql`.

> **Najczęstsza awaria: `database.sql` jako katalog.** Jeśli plik nie istnieje,
> Docker przy montażu wolumenu tworzy **katalog** o tej nazwie. MySQL dostaje
> katalog zamiast pliku SQL i kończy pracę błędem
> `ERROR: Can't initialize batch_readline`, a baza zostaje pusta.
> Po takiej awarii (tylko na świeżej instalacji!) usuń katalog i wolumen danych,
> skopiuj prawdziwy plik i uruchom ponownie:
>
> ```bash
> docker compose down
> rm -rf database.sql data/mysql
> cp ../database/database.sql ./database.sql
> test -f database.sql
> docker compose up -d
> ```

### 3. Opcjonalna konfiguracja MySQL

Compose montuje `./config/mysql` (kodowanie `utf8mb4`). Skopiuj katalog z repo:

```bash
cp -r ../config/mysql ./config/mysql
```

Bez tego kroku MySQL użyje domyślnej konfiguracji; katalog zostanie utworzony
automatycznie i nie jest to błąd krytyczny.

### 4. Start

```bash
# tylko gdy pakiet GHCR jest prywatny (PAT z read:packages)
docker login ghcr.io -u IgorStarega

docker compose pull
docker compose up -d
```

Sprawdź stan:

```bash
docker compose ps
docker compose logs -f app
```

Aplikacja: `http://127.0.0.1:8082/` (lokalnie) lub `APP_URL` za tunelem.
phpMyAdmin: `http://127.0.0.1:8084/` — za tunelem ustaw
`PMA_ABSOLUTE_URI` z ukośnikiem na końcu, np. `https://php.dziadu.dev/`.

## Aktualizacja

```bash
docker compose pull
docker compose up -d
```

`latest` w GHCR powstaje **dopiero po smoke teście** w GitHub Actions, więc nie
zawiera nieprzetestowanego kodu. Do rollbacku przypnij konkretny obraz:

```dotenv
IMAGE_TAG=sha-0123abc
```

i ponownie wykonaj `docker compose pull && docker compose up -d`.

## Aktualizacja istniejącej bazy

Nowy `database.sql` **nie** nadpisze istniejących danych — import działa tylko
przy pustym `./data/mysql`. Dla działającej bazy zastosuj migracje po kolei:

```bash
docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < ../database/migrations/002-etap2.sql
docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < ../database/migrations/003-etap2.sql
docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < ../database/migrations/004-poprawka-kodowania.sql
```

Opis migracji i zasady regenerowania `database.sql` są w głównym `README.md`
(sekcja „Aktualizacja istniejącej bazy”).

## Ważne

- `container_name` jest **globalny w obrębie jednego demona Dockera** — nie
  uruchamiaj dwóch kopii tego stosu na tym samym hoście (`owc-app`, `owc-db`,
  `owc-phpmyadmin` się zderzą).
- `deploy/.env`, `database.sql`, `data/` i `config/mysql/` są w `.gitignore` —
  nie commituj haseł ani kopii bazy.
- Nie montuj kodu aplikacji do kontenera `app`; obraz jest samodzielny.
- Po zmianie `.env` odtwórz kontenery: `docker compose up -d --force-recreate`.
