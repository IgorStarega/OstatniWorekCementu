#!/usr/bin/env bash
# =========================================================================
# Migracje bazy dla istniejącego stosu docker compose.
#
# Uruchomienie (z katalogu repozytorium):
#   bash tools/migrate.sh
#
# Skrypt stosuje wszystkie pliki database/migrations/*.sql po kolei.
# Migracje są idempotentne, więc można go uruchamiać wielokrotnie.
# =========================================================================
set -euo pipefail

cd "$(dirname "$0")/.."

DB_SERVICE=${DB_SERVICE:-db}

if ! docker compose ps --status running --services 2>/dev/null | grep -qx "$DB_SERVICE"; then
    echo "BŁĄD: usługa '$DB_SERVICE' nie działa. Uruchom najpierw: docker compose up -d" >&2
    exit 2
fi

shopt -s nullglob
migrations=(database/migrations/*.sql)

if [ ${#migrations[@]} -eq 0 ]; then
    echo "BŁĄD: brak plików w database/migrations/." >&2
    exit 2
fi

for migration in "${migrations[@]}"; do
    echo "==> $migration"
    docker compose exec -T "$DB_SERVICE" sh -c \
        'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < "$migration"
done

echo "OK: zastosowano ${#migrations[@]} migracji."
