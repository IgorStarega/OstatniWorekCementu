#!/usr/bin/env bash
set -euo pipefail

if [ "${RUN_MIGRATIONS:-1}" != "0" ]; then
    echo "entrypoint: uruchamiam migracje bazy (tools/migrate.php)..."
    migrated=0
    for attempt in $(seq 1 10); do
        if php /var/www/html/tools/migrate.php; then
            migrated=1
            break
        fi
        echo "entrypoint: próba migracji (migrate.php) ${attempt}/10 nieudana, ponawiam za 3 s..." >&2
        sleep 3
    done

    if [ "$migrated" -ne 1 ]; then
        echo "OSTRZEŻENIE: entrypoint nie zastosował migracji (migrate.php) po 10 próbach — kontynuuję start Apache." >&2
    fi
else
    echo "entrypoint: pominięto migracje (RUN_MIGRATIONS=0)."
fi

exec docker-php-entrypoint "$@"
