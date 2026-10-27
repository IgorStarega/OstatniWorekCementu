#!/usr/bin/env bash
# =========================================================================
# Smoke test QA — OstatniWorekCementu
#
# Odpala się przeciw DZIAŁAJĄCEMU stosowi docker compose. Zakłada:
#   * APP_ENV=local (linki weryfikacji i resetu hasła są pokazywane na ekranie),
#   * bazę z danymi testowymi (database/database.sql), w tym konto
#     tomasz.zielinski@example.com do testu blokady.
#
# Uruchomienie:  bash tools/qa-smoke.sh
# Zmiana adresu: BASE_URL=http://127.0.0.1:8082 bash tools/qa-smoke.sh
#
# Wymaga tylko: bash, curl, docker compose, grep. Żadnych dodatkowych zależności.
# Skrypt jest idempotentny i sprząta po sobie (usuwa konto testowe, resetuje blokadę).
# =========================================================================
set -u

cd "$(dirname "$0")/.." || exit 2

BASE_URL=${BASE_URL:-http://localhost:8082}
DB_SERVICE=${DB_SERVICE:-db}
DB_NAME=${DB_NAME:-budowlanka_rezerwacje}
DB_USER=${DB_USER:-app}
DB_PASS=${DB_PASS:-app123}

if [ -t 1 ]; then
    C_G=$'\033[32m'; C_R=$'\033[31m'; C_Y=$'\033[33m'; C_0=$'\033[0m'
else
    C_G=''; C_R=''; C_Y=''; C_0=''
fi

PASS=0
FAIL=0
SKIP=0

pass() { printf '%sPASS%s %s\n' "$C_G" "$C_0" "$1"; PASS=$((PASS + 1)); }
fail() { printf '%sFAIL%s %s — %s\n' "$C_R" "$C_0" "$1" "$2"; FAIL=$((FAIL + 1)); }
skip() { printf '%sSKIP%s %s — %s\n' "$C_Y" "$C_0" "$1" "$2"; SKIP=$((SKIP + 1)); }

TMP=$(mktemp -d)
SMOKE_EMAIL=""

# --- pomocnicze ----------------------------------------------------------

# Zapytanie do bazy (tylko odczyt/zapis po stronie danych testowych).
dbq() {
    docker compose exec -T "$DB_SERVICE" \
        mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -B -e "$1" 2>/dev/null
}

# Żądanie HTTP; kod na stdout, ciało w $TMP/body, nagłówki w $TMP/hdr.
req() {
    local method="$1" url="$2"
    shift 2
    curl -s -o "$TMP/body" -D "$TMP/hdr" -w '%{http_code}' -X "$method" "$@" "$url"
}

# Token CSRF ze wskazanej strony (ustawia ciasteczka w podanym slocie).
get_csrf() {
    local url="$1" jar="$2"
    curl -s -c "$jar" -b "$jar" "$url" \
        | grep -oP 'name="csrf_token" value="\K[^"]+' | head -1
}

# Treść alertów z ostatniej odpowiedzi.
alerts() { grep -oP '<p role="alert">\K[^<]*' "$TMP/body" | tr '\n' '|'; }

cleanup() {
    if [ -n "$SMOKE_EMAIL" ]; then
        dbq "DELETE FROM email_sends WHERE email = '$SMOKE_EMAIL'" >/dev/null 2>&1
        dbq "DELETE FROM users WHERE email = '$SMOKE_EMAIL'" >/dev/null 2>&1
    fi
    # Konto blokowane w teście 16 musi po skrypcie zostać odblokowane.
    dbq "UPDATE users SET failed_attempts = 0, locked_until = NULL
         WHERE email = 'tomasz.zielinski@example.com'" >/dev/null 2>&1
    rm -rf "$TMP"
}
trap cleanup EXIT

# --- przygotowanie -------------------------------------------------------
# Reset blokady na wypadek, gdyby poprzedni przebieg przerwał się przed cleanupem.
dbq "UPDATE users SET failed_attempts = 0, locked_until = NULL
     WHERE email = 'tomasz.zielinski@example.com'" >/dev/null 2>&1

STAMP=$(date +%s%N)
SMOKE_EMAIL="qa${STAMP}@example.com"
SMOKE_FIRST="QaSmoke"
JAR_GUEST="$TMP/guest.txt"
JAR_USER="$TMP/user.txt"

# --- 1. Strona główna: przekierowanie ------------------------------------
code=$(req GET "$BASE_URL/")
if [ "$code" = "302" ]; then
    pass "1. GET / przekierowuje (302)"
else
    fail "1. GET / przekierowuje (302)" "otrzymano $code"
fi

# --- 2. Strona główna: 200, bez błędów PHP -------------------------------
code=$(req GET "$BASE_URL/?page=home")
if [ "$code" = "200" ] && ! grep -qE 'Fatal error|Warning|Notice' "$TMP/body"; then
    pass "2. GET /?page=home = 200 bez błędów PHP"
else
    fail "2. GET /?page=home = 200 bez błędów PHP" "kod=$code"
fi

# --- 3. Nieznana strona = 404 -------------------------------------------
code=$(req GET "$BASE_URL/?page=nieistnieje")
if [ "$code" = "404" ]; then
    pass "3. GET /?page=nieistnieje = 404"
else
    fail "3. GET /?page=nieistnieje = 404" "otrzymano $code"
fi

# --- 4. Rejestracja bez CSRF = 403 --------------------------------------
code=$(req POST "$BASE_URL/?page=register" \
    --data-urlencode "first_name=Bez" --data-urlencode "last_name=Tokenu" \
    --data-urlencode "email=bez.csrf@example.com" --data-urlencode "phone=500600700" \
    --data-urlencode "password=Haslo123!" --data-urlencode "password_confirmation=Haslo123!")
if [ "$code" = "403" ]; then
    pass "4. POST register bez CSRF = 403"
else
    fail "4. POST register bez CSRF = 403" "otrzymano $code"
fi

# --- 5. Rejestracja pełna: link na ekranie (local) -----------------------
CSRF=$(get_csrf "$BASE_URL/?page=register" "$JAR_GUEST")
code=$(req POST "$BASE_URL/?page=register" -b "$JAR_GUEST" -c "$JAR_GUEST" \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "first_name=$SMOKE_FIRST" --data-urlencode "last_name=Tester" \
    --data-urlencode "email=$SMOKE_EMAIL" --data-urlencode "phone=500600700" \
    --data-urlencode "password=Haslo123!" --data-urlencode "password_confirmation=Haslo123!")
TOKEN=$(grep -oP 'verify-email(?:&amp;|&)token=\K[0-9a-f]+' "$TMP/body" | head -1)
if grep -q 'Link potwierdzający' "$TMP/body" && [ -n "$TOKEN" ]; then
    pass "5. Rejestracja zwraca link potwierdzający (link na ekranie)"
else
    fail "5. Rejestracja zwraca link potwierdzający (link na ekranie)" "kod=$code, token pusty lub brak linku"
fi

# --- 6. Weryfikacja e-mail tokenem --------------------------------------
code=$(req GET "$BASE_URL/?page=verify-email&token=$TOKEN")
if grep -q 'Adres e-mail został potwierdzony' "$TMP/body"; then
    pass "6. Weryfikacja e-mail linkiem"
else
    fail "6. Weryfikacja e-mail linkiem" "kod=$code, brak potwierdzenia"
fi

# --- 7. Token jednorazowy -----------------------------------------------
code=$(req GET "$BASE_URL/?page=verify-email&token=$TOKEN")
if grep -q 'nieprawidłowy, wygasł lub został już użyty' "$TMP/body"; then
    pass "7. Ten sam token drugi raz odrzucony"
else
    fail "7. Ten sam token drugi raz odrzucony" "kod=$code"
fi

# --- 8. Logowanie nowego konta ------------------------------------------
CSRF=$(get_csrf "$BASE_URL/?page=login" "$JAR_USER")
code=$(req POST "$BASE_URL/?page=login" -b "$JAR_USER" -c "$JAR_USER" \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "email=$SMOKE_EMAIL" --data-urlencode "password=Haslo123!")
code2=$(req GET "$BASE_URL/?page=profile" -b "$JAR_USER" -c "$JAR_USER")
if [ "$code" = "302" ] && grep -q 'Zalogowano jako' "$TMP/body"; then
    pass "8. Logowanie nowego konta działa"
else
    fail "8. Logowanie nowego konta działa" "login=$code, profil=$code2"
fi

# --- 8a. Logowanie trzech ról -------------------------------------------
# Każda rola trafia na swoją stronę startową: panel (admin/pracownik) lub profil (klient).
for entry in \
    "admin@ostatniworekcementu.pl|dashboard" \
    "jan.kowalski@example.com|dashboard" \
    "anna.kowalska@example.com|profile"; do
    creds=${entry%%|*}
    page=${entry##*|}
    JAR="$TMP/role_$RANDOM.txt"
    CSRF=$(get_csrf "$BASE_URL/?page=login" "$JAR")
    code=$(req POST "$BASE_URL/?page=login" -b "$JAR" -c "$JAR" \
        --data-urlencode "csrf_token=$CSRF" \
        --data-urlencode "email=$creds" --data-urlencode "password=Haslo123!")
    code2=$(req GET "$BASE_URL/?page=$page" -b "$JAR" -c "$JAR")
    if [ "$code" = "302" ] && [ "$code2" = "200" ] \
       && ! grep -qE 'Fatal error|Warning|Uncaught' "$TMP/body"; then
        pass "8a. logowanie $creds -> 200 bez błędów"
    else
        fail "8a. logowanie $creds" "login=$code $page=$code2"
    fi
done

# --- 9. Duplikat e-maila ------------------------------------------------
CSRF=$(get_csrf "$BASE_URL/?page=register" "$JAR_GUEST")
code=$(req POST "$BASE_URL/?page=register" -b "$JAR_GUEST" -c "$JAR_GUEST" \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "first_name=Inny" --data-urlencode "last_name=Tester" \
    --data-urlencode "email=$SMOKE_EMAIL" --data-urlencode "phone=500600701" \
    --data-urlencode "password=Haslo123!" --data-urlencode "password_confirmation=Haslo123!")
if grep -q 'już istnieje' "$TMP/body"; then
    pass "9. Duplikat e-maila odrzucony"
else
    fail "9. Duplikat e-maila odrzucony" "kod=$code"
fi

# --- 10. IDOR: profil ignoruje ?id= -------------------------------------
code=$(req GET "$BASE_URL/?page=profile&id=1" -b "$JAR_USER" -c "$JAR_USER")
if grep -q "$SMOKE_FIRST" "$TMP/body"; then
    pass "10. Profil pokazuje dane zalogowanego (ignoruje ?id=1)"
else
    fail "10. Profil pokazuje dane zalogowanego (ignoruje ?id=1)" "kod=$code, brak $SMOKE_FIRST"
fi

# --- 11. XSS: escaping w profilu ----------------------------------------
CSRF=$(get_csrf "$BASE_URL/?page=profile" "$JAR_USER")
req POST "$BASE_URL/?page=profile" -b "$JAR_USER" -c "$JAR_USER" >/dev/null \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "first_name=<script>alert(1)</script>" \
    --data-urlencode "last_name=Tester" --data-urlencode "phone=500600700"
req GET "$BASE_URL/?page=profile" -b "$JAR_USER" -c "$JAR_USER" >/dev/null
if grep -q '&lt;script&gt;' "$TMP/body" && ! grep -q '<script>alert(1)</script>' "$TMP/body"; then
    pass "11. XSS w profilu zescapedowany"
else
    fail "11. XSS w profilu zescapedowany" "brak escapowania lub surowy payload"
fi

# --- 12. CSRF na profilu = 403 ------------------------------------------
code=$(req POST "$BASE_URL/?page=profile" -b "$JAR_USER" -c "$JAR_USER" \
    --data-urlencode "first_name=Hakers" --data-urlencode "last_name=Test" \
    --data-urlencode "phone=500600700")
if [ "$code" = "403" ]; then
    pass "12. POST profilu bez CSRF = 403"
else
    fail "12. POST profilu bez CSRF = 403" "otrzymano $code"
fi

# --- 13. Wylogowanie GET = 405 ------------------------------------------
code=$(req GET "$BASE_URL/?page=logout" -b "$JAR_USER" -c "$JAR_USER")
if [ "$code" = "405" ]; then
    pass "13. GET /?page=logout = 405"
else
    fail "13. GET /?page=logout = 405" "otrzymano $code"
fi

# --- 14. Gość na profilu = 302 na login ---------------------------------
code=$(req GET "$BASE_URL/?page=profile")
if [ "$code" = "302" ] && grep -qi 'location:.*page=login' "$TMP/hdr"; then
    pass "14. Gość na profilu przekierowany na login"
else
    fail "14. Gość na profilu przekierowany na login" "kod=$code"
fi

# --- 15. Rola: klient nie wchodzi na dashboard --------------------------
JAR_ANNA="$TMP/anna.txt"
CSRF=$(get_csrf "$BASE_URL/?page=login" "$JAR_ANNA")
req POST "$BASE_URL/?page=login" -b "$JAR_ANNA" -c "$JAR_ANNA" >/dev/null \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "email=anna.kowalska@example.com" --data-urlencode "password=Haslo123!"
code=$(req GET "$BASE_URL/?page=dashboard" -b "$JAR_ANNA" -c "$JAR_ANNA")
if [ "$code" = "403" ]; then
    pass "15. Klient na dashboard = 403"
else
    fail "15. Klient na dashboard = 403" "otrzymano $code"
fi

# --- 16. Blokada konta po 5 próbach -------------------------------------
JAR_LOCK="$TMP/lock.txt"
CSRF=$(get_csrf "$BASE_URL/?page=login" "$JAR_LOCK")
for _ in 1 2 3 4 5; do
    req POST "$BASE_URL/?page=login" -b "$JAR_LOCK" -c "$JAR_LOCK" >/dev/null \
        --data-urlencode "csrf_token=$CSRF" \
        --data-urlencode "email=tomasz.zielinski@example.com" --data-urlencode "password=ZleHaslo!"
done
LOCKED_ALERT=$(grep -q 'zablokowane' "$TMP/body" && echo yes || echo no)
req POST "$BASE_URL/?page=login" -b "$JAR_LOCK" -c "$JAR_LOCK" >/dev/null \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "email=tomasz.zielinski@example.com" --data-urlencode "password=Haslo123!"
if [ "$LOCKED_ALERT" = "yes" ] && grep -q 'zablokowane' "$TMP/body"; then
    pass "16. Blokada konta po 5 nieudanych próbach"
else
    fail "16. Blokada konta po 5 nieudanych próbach" "5. próba=$LOCKED_ALERT, poprawne hasło po blokadzie odrzucone?"
fi

# --- 17. Enumeracja: identyczny komunikat -------------------------------
JAR_E1="$TMP/e1.txt"; JAR_E2="$TMP/e2.txt"
CSRF=$(get_csrf "$BASE_URL/?page=login" "$JAR_E1")
req POST "$BASE_URL/?page=login" -b "$JAR_E1" -c "$JAR_E1" >/dev/null \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "email=$SMOKE_EMAIL" --data-urlencode "password=ZleHaslo!"
MSG_EXISTING=$(alerts)
CSRF=$(get_csrf "$BASE_URL/?page=login" "$JAR_E2")
req POST "$BASE_URL/?page=login" -b "$JAR_E2" -c "$JAR_E2" >/dev/null \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "email=nie.ma.takiego@example.com" --data-urlencode "password=ZleHaslo!"
MSG_MISSING=$(alerts)
if [ -n "$MSG_EXISTING" ] && [ "$MSG_EXISTING" = "$MSG_MISSING" ]; then
    pass "17. Komunikat dla istniejącego i nieistniejącego konta identyczny"
else
    fail "17. Komunikat identyczny" "istniejące='$MSG_EXISTING' brak='$MSG_MISSING'"
fi

# --- 18. Schemat bazy ----------------------------------------------------
TABLES=$(dbq "SHOW TABLES" | grep -c .)
COLS=$(dbq "SHOW COLUMNS FROM users" | cut -f1)
if [ "$TABLES" = "10" ] \
    && printf '%s\n' "$COLS" | grep -q '^failed_attempts$' \
    && printf '%s\n' "$COLS" | grep -q '^locked_until$' \
    && printf '%s\n' "$COLS" | grep -q '^email_verified_at$'; then
    pass "18. Schemat: 10 tabel + kolumny users"
else
    fail "18. Schemat: 10 tabel + kolumny users" "tabele=$TABLES"
fi

# --- 19. Migracje idempotentne (002, 003 i 004, dwa razy) ----------------
MIG_OUT="$TMP/mig.txt"
for _ in 1 2; do
    docker compose exec -T "$DB_SERVICE" sh -c \
        'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' \
        < database/migrations/002-etap2.sql >>"$MIG_OUT" 2>&1
    docker compose exec -T "$DB_SERVICE" sh -c \
        'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' \
        < database/migrations/003-etap2.sql >>"$MIG_OUT" 2>&1
    docker compose exec -T "$DB_SERVICE" sh -c \
        'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' \
        < database/migrations/004-poprawka-kodowania.sql >>"$MIG_OUT" 2>&1
done
if ! grep -qi 'ERROR' "$MIG_OUT"; then
    pass "19. Migracje 002, 003 i 004 idempotentne (dwa przebiegi)"
else
    fail "19. Migracje 002, 003 i 004 idempotentne (dwa przebiegi)" "$(grep -i 'ERROR' "$MIG_OUT" | head -1)"
fi

# --- 20. Retencja email_sends -------------------------------------------
OLD=$(dbq "SELECT COUNT(*) FROM email_sends
           WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)")
if [ "$OLD" = "0" ]; then
    pass "20. email_sends bez wpisów starszych niż 24 h"
else
    fail "20. email_sends bez wpisów starszych niż 24 h" "starych wpisów: $OLD"
fi

# --- 21. Panel admina: dostęp tylko dla administratora -------------------
JAR_ADMIN="$TMP/admin.txt"
CSRF=$(get_csrf "$BASE_URL/?page=login" "$JAR_ADMIN")
req POST "$BASE_URL/?page=login" -b "$JAR_ADMIN" -c "$JAR_ADMIN" >/dev/null \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "email=admin@ostatniworekcementu.pl" --data-urlencode "password=Haslo123!"

for admin_page in admin-dashboard admin-categories admin-services \
                  admin-employees admin-users admin-reservations; do
    # Gość: 302 na logowanie.
    code=$(req GET "$BASE_URL/?page=$admin_page")
    if [ "$code" = "302" ] && grep -qi 'location:.*page=login' "$TMP/hdr"; then
        pass "21. gość $admin_page -> 302 login"
    else
        fail "21. gość $admin_page -> 302 login" "otrzymano $code"
    fi

    # Klient: 403, nigdy panel.
    code=$(req GET "$BASE_URL/?page=$admin_page" -b "$JAR_ANNA" -c "$JAR_ANNA")
    if [ "$code" = "403" ]; then
        pass "21. klient $admin_page -> 403"
    else
        fail "21. klient $admin_page -> 403" "otrzymano $code"
    fi

    # Administrator: 200 i wyrenderowany panel.
    code=$(req GET "$BASE_URL/?page=$admin_page" -b "$JAR_ADMIN" -c "$JAR_ADMIN")
    if [ "$code" = "200" ] && grep -q 'Panel administratora' "$TMP/body" \
       && ! grep -qE 'Fatal error|Warning|Notice|Uncaught' "$TMP/body"; then
        pass "21. admin $admin_page -> 200 panel"
    else
        fail "21. admin $admin_page -> 200 panel" "otrzymano $code"
    fi
done

# --- 21b. Polskie znaki z bazy bez podwójnego kodowania ------------------
code=$(req GET "$BASE_URL/?page=admin-categories" -b "$JAR_ADMIN" -c "$JAR_ADMIN")
if [ "$code" = "200" ] && grep -q 'Ocieplanie budynków' "$TMP/body" \
   && ! grep -q 'Ã³' "$TMP/body"; then
    pass "21b. Polskie znaki z bazy wyświetlają się poprawnie"
else
    fail "21b. Polskie znaki z bazy wyświetlają się poprawnie" "kod=$code, podwójne kodowanie?"
fi

# --- 22. Link do panelu tylko dla administratora ------------------------
req GET "$BASE_URL/?page=home" -b "$JAR_ADMIN" -c "$JAR_ADMIN" >/dev/null
if grep -q 'page=admin-dashboard' "$TMP/body"; then
    pass "22. administrator widzi link do panelu"
else
    fail "22. administrator widzi link do panelu" "brak linku"
fi

req GET "$BASE_URL/?page=home" -b "$JAR_ANNA" -c "$JAR_ANNA" >/dev/null
if ! grep -q 'page=admin-dashboard' "$TMP/body"; then
    pass "22. klient nie widzi linku do panelu"
else
    fail "22. klient nie widzi linku do panelu" "link widoczny"
fi

req GET "$BASE_URL/?page=home" >/dev/null
if ! grep -q 'page=admin-dashboard' "$TMP/body"; then
    pass "22. gość nie widzi linku do panelu"
else
    fail "22. gość nie widzi linku do panelu" "link widoczny"
fi

# --- 24. Rezerwacje: lista i filtry ------------------------------------
JAR_RES="$TMP/res.txt"
CSRF=$(get_csrf "$BASE_URL/?page=login" "$JAR_RES")
req POST "$BASE_URL/?page=login" -b "$JAR_RES" -c "$JAR_RES" >/dev/null \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "email=admin@ostatniworekcementu.pl" \
    --data-urlencode "password=Haslo123!"

code=$(req GET "$BASE_URL/?page=admin-reservations" -b "$JAR_RES" -c "$JAR_RES")
if [ "$code" = "200" ] && grep -q 'Rezerwacje' "$TMP/body" \
   && ! grep -qE 'Fatal error|Warning|Notice|Uncaught' "$TMP/body"; then
    pass "24. admin-reservations lista -> 200"
else
    fail "24. admin-reservations lista -> 200" "kod=$code"
fi

# wstrzyknięte filtry nie mogą wywalić SQL
code=$(req GET "$BASE_URL/?page=admin-reservations&status=xss&employee_id=abc&date_from=2026-13-99" \
    -b "$JAR_RES" -c "$JAR_RES")
if [ "$code" = "200" ] && ! grep -qE 'Fatal error|SQLSTATE|Uncaught' "$TMP/body"; then
    pass "24b. Rezerwacje: wstrzyknięte filtry zignorowane"
else
    fail "24b. Rezerwacje: wstrzyknięte filtry zignorowane" "kod=$code"
fi

# zmiana statusu istniejącej rezerwacji (id=1 z seeda)
CSRF=$(get_csrf "$BASE_URL/?page=admin-reservations&id=1" "$JAR_RES")
code=$(req POST "$BASE_URL/?page=admin-reservations" -b "$JAR_RES" -c "$JAR_RES" \
    --data-urlencode "csrf_token=$CSRF" \
    --data-urlencode "action=status" \
    --data-urlencode "id=1" \
    --data-urlencode "status=potwierdzona")
if [ "$code" = "302" ]; then
    pass "24c. Rezerwacje: zmiana statusu -> 302"
else
    fail "24c. Rezerwacje: zmiana statusu -> 302" "kod=$code"
fi

# --- testy wymagające przeglądarki lub restartu kontenera ----------------
skip "Widok 500 przy błędnym DB_PASS" "wymaga restartu kontenera z innym DB_PASS (environment w compose); nie testujemy w tym przebiegu"
skip "Wizualna ocena formularzy / responsywność / czytelność błędów" "wymaga przeglądarki — patrz tools/qa-manual.md"

# --- podsumowanie --------------------------------------------------------
TOTAL=$((PASS + FAIL))
printf '\n%s/%s PASS, %s FAIL, %s SKIP\n' "$PASS" "$TOTAL" "$FAIL" "$SKIP"
[ "$FAIL" -eq 0 ] || exit 1
