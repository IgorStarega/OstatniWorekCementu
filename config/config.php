<?php
// Konfiguracja aplikacji. Dane dostępowe wyłącznie ze zmiennych środowiskowych
// (nigdy nie wpisuj tu prawdziwych haseł). DB_* przekazuje docker-compose.yml:
// host i port są stałe, a nazwę bazy i konto wyprowadzamy z MYSQL_* w .env
// (wzorem jest .env.example, domyślnie użytkownik app / hasło app123).
// Brak wymaganej zmiennej kończy się jasnym wyjątkiem zamiast cichego połączenia.

$required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$missing = [];
$values = [];

foreach ($required as $name) {
    $value = getenv($name);
    if ($value === false || $value === '') {
        $missing[] = $name;
    } else {
        $values[$name] = $value;
    }
}

if ($missing !== []) {
    throw new RuntimeException(
        'Brak wymaganych zmiennych środowiskowych bazy danych: ' . implode(', ', $missing)
        . '. Skopiuj plik .env.example do .env (cp .env.example .env) i ustaw '
        . 'MYSQL_DATABASE, MYSQL_USER oraz MYSQL_PASSWORD (domyślnie app / app123).'
    );
}

// Bazowy adres publiczny aplikacji — źródło hosta/protokołu we wszystkich linkach
// absolutnych (potwierdzenie e-mail, reset hasła). Lokalnie domyślnie
// http://localhost:8082, na serwerze (prod) wymagany i musi być https://,
// np. https://owc.dziadu.dev.
$appEnv = getenv('APP_ENV') ?: 'local';
$appUrl = rtrim(trim((string) getenv('APP_URL')), '/');

if ($appUrl === '' && $appEnv !== 'prod') {
    $appUrl = 'http://localhost:8082';
}

if ($appUrl === '' || preg_match('#^https?://[^/\s?\#]+$#i', $appUrl) !== 1) {
    throw new RuntimeException(
        'Brak lub niepoprawny APP_URL (oczekiwany format: https://domena, bez ścieżki). '
        . 'Ustaw go w pliku .env (wzór: .env.example).'
    );
}

if ($appEnv === 'prod' && stripos($appUrl, 'https://') !== 0) {
    throw new RuntimeException('W środowisku prod APP_URL musi zaczynać się od https://.');
}

// Zaufane adresy reverse proxy (np. kontener cloudflared), rozdzielone przecinkami.
// Dopuszczalne pojedyncze adresy IP oraz CIDR. Domyślnie PUSTE — świadomie nie
// ufamy całej prywatnej puli Dockera, bo każdy kontener mógłby podrobić nagłówki.
$trustedProxies = [];
foreach (explode(',', (string) getenv('TRUSTED_PROXY_IPS')) as $entry) {
    $entry = trim($entry);
    if ($entry !== '') {
        $trustedProxies[] = $entry;
    }
}

return [
    'db' => [
        'host'    => $values['DB_HOST'],
        'port'    => getenv('DB_PORT') ?: '3306',
        'name'    => $values['DB_NAME'],
        'user'    => $values['DB_USER'],
        'pass'    => $values['DB_PASS'],
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'System Rezerwacji Usług Budowlanych',
        // 'local' (domyślnie) albo 'prod' — w prod włączane jest cookie_secure.
        'env'  => $appEnv,
        // Publiczny adres aplikacji (APP_URL), bez końcowego "/".
        'url'  => $appUrl,
        // Zaufane reverse proxy (TRUSTED_PROXY_IPS) — patrz requestFromTrustedProxy().
        'trusted_proxies' => $trustedProxies,
    ],
];
