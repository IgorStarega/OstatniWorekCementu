## Zakres

<!-- Co zmienia ten PR i dlaczego? -->

## Weryfikacja

- [ ] `bash tools/qa-smoke.sh` → 0 FAIL
- [ ] `grep -l 'w budowie' app/views/admin/*.php` → pusto
- [ ] `find app config public -name '*.php' -print0 | xargs -0 -n1 php -l` → brak błędów
- [ ] `git diff --check` → czysto
- [ ] brak sekretów i danych dostępowych w diffie

## Uwagi

<!-- Migracje, zmiany schematu, wpływ na deploy, rzeczy do sprawdzenia manualnie -->
