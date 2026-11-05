#!/usr/bin/env bash
# Kontrola przed oddaniem etapu 3. Nie zastępuje QA manualnego (tools/qa-manual.md),
# ale wyłapuje wszystko, co da się sprawdzić automatem.
set -u

cd "$(dirname "$0")/.." || exit 2

FAIL=0
ok()   { printf '  \033[32mOK\033[0m   %s\n' "$1"; }
bad()  { printf '  \033[31mFAIL\033[0m %s\n' "$1"; FAIL=$((FAIL+1)); }
info() { printf '\n\033[1m%s\033[0m\n' "$1"; }

info "1. Placeholdery w widokach"
if grep -rl 'w budowie' app/views/ >/dev/null 2>&1; then
    bad "znaleziono 'w budowie' w app/views/: $(grep -rl 'w budowie' app/views/ | tr '\n' ' ')"
else
    ok "brak placeholderów"
fi

info "2. Składnia PHP"
# Lokalnie może nie być PHP — wtedy lint wykonujemy w kontenerze aplikacji.
lint_php() {
    if command -v php >/dev/null 2>&1; then
        find app config public -name '*.php' -print0 | xargs -0 -n1 php -l
    else
        docker compose exec -T app sh -c \
            'find app config public -name "*.php" -print0 | xargs -0 -n1 php -l'
    fi
}
if lint_php >/dev/null 2>&1; then
    ok "wszystkie pliki PHP bez błędów składni"
else
    bad "błędy składni PHP:"
    lint_php 2>&1 | grep -v 'No syntax errors'
fi

info "3. Trasy admin-* z kontrolerem"
ROUTES=$(grep -oP "case '\Kadmin-[a-z-]+" public/index.php | sort)
for r in $ROUTES; do
    ok "trasa $r"
done

info "4. Pozycje sidebaru vs trasy"
SIDEBAR=$(grep -oP "'page'\s*=>\s*'\Kadmin-[a-z-]+" app/views/layout/admin.php | sort)
for s in $SIDEBAR; do
    if printf '%s\n' "$ROUTES" | grep -qx "$s"; then
        ok "sidebar $s ma trasę"
    else
        bad "sidebar $s NIE MA trasy w public/index.php"
    fi
done

info "5. requireRole('admin') we wszystkich metodach admina"
METHODS=$(grep -c 'public function' app/controllers/AdminController.php)
GUARDS=$(grep -c "requireRole('admin')" app/controllers/AdminController.php)
if [ "$GUARDS" -ge "$METHODS" ]; then
    ok "metod: $METHODS, requireRole: $GUARDS"
else
    bad "metod: $METHODS, requireRole: $GUARDS — brakuje $(($METHODS-$GUARDS))"
fi

info "6. CSRF na operacjach POST"
if grep -rq 'csrf_verify' app/controllers/AdminController.php; then
    ok "AdminController używa csrf_verify()"
else
    bad "AdminController NIE używa csrf_verify()"
fi

info "7. Usuwanie przez GET"
if grep -rqE 'href="[^"]*(action=delete|delete=1)' app/views/ 2>/dev/null; then
    bad "znaleziono usuwanie przez link GET w app/views/"
else
    ok "brak usuwania przez GET"
fi

info "8. htmlspecialchars w widokach admina"
MISSING=0
for f in app/views/admin/*.php; do
    if grep -q '<?= \$' "$f" && ! grep -q 'htmlspecialchars' "$f"; then
        bad "$f: surowe <?= bez htmlspecialchars"
        MISSING=$((MISSING+1))
    fi
done
[ "$MISSING" -eq 0 ] && ok "wszystkie widoki admina escapują wyjście"

info "9. Favicon"
if [ -f public/favicon.svg ] || [ -f public/favicon.ico ]; then
    ok "favicon istnieje"
else
    bad "brak public/favicon.svg i public/favicon.ico"
fi
if grep -q 'rel="icon"' app/views/layout/header.php; then
    ok "<link rel=\"icon\"> w header.php"
else
    bad "brak <link rel=\"icon\"> w header.php"
fi

info "10. Sekrety w historii Git"
SECRETS=$(git log -p 2>/dev/null \
    | grep -iE "[p]assword\s*=\s*[\"'][^\"']{6,}|[s]ecret_key|api[_-]?key\s*=\s*[\"']" \
    | grep -vE '\.env\.example|MYSQL_ROOT_PASSWORD:\s*\$\{|DB_PASS:\s*\$\{' | head -5)
if [ -n "$SECRETS" ]; then
    bad "możliwe sekrety w historii:"
    printf '%s\n' "$SECRETS"
else
    ok "brak oczywistych sekretów w historii"
fi

info "11. Pliki tymczasowe w repo"
TMPFILES=$(git ls-files | grep -E '\.(log|tmp|bak)$|^data/|^backup-' || true)
if [ -n "$TMPFILES" ]; then
    bad "pliki tymczasowe w repo: $TMPFILES"
else
    ok "brak plików tymczasowych"
fi

info "12. README wymienia wszystkie modele"
for m in EmployeeAvailability EmployeeDayOff StatusHistory AdminLog Stats Reservation; do
    if grep -q "$m" README.md; then
        ok "README wymienia $m"
    else
        bad "README NIE wymienia $m"
    fi
done

printf '\n'
if [ "$FAIL" -eq 0 ]; then
    printf '\033[32mWszystko OK. Zostało QA manualne: tools/qa-manual.md\033[0m\n'
    exit 0
fi
printf '\033[31m%s problemów. Popraw przed oddaniem.\033[0m\n' "$FAIL"
exit 1
