<?php
// Konfiguracja aplikacji. Dane dostępowe wyłącznie ze zmiennych środowiskowych
// (nigdy nie wpisuj tu prawdziwych haseł). Wartości ustawiamy w pliku .env —
// wzorem jest .env.example (domyślnie użytkownik app / hasło app123).
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
        . 'DB_HOST, DB_NAME, DB_USER oraz DB_PASS (domyślnie app / app123).'
    );
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
        'env'  => getenv('APP_ENV') ?: 'local',
    ],
];
