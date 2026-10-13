<?php
// Konfiguracja aplikacji. Dane dostępowe wyłącznie ze zmiennych środowiskowych
// (nigdy nie wpisuj tu prawdziwych haseł). Lokalne wartości domyślne poniżej.
return [
    'db' => [
        'host'    => getenv('DB_HOST') ?: 'db',
        'port'    => getenv('DB_PORT') ?: '3306',
        'name'    => getenv('DB_NAME') ?: 'budowlanka_rezerwacje',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'System Rezerwacji Usług Budowlanych',
        // 'local' (domyślnie) albo 'prod' — w prod włączane jest cookie_secure.
        'env'  => getenv('APP_ENV') ?: 'local',
    ],
];
