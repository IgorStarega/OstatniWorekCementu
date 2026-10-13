<?php
// Jedyny punkt wejścia aplikacji (front controller). Cały ruch HTTP przechodzi tędy.

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/config/config.php';

// Spójna strefa czasowa całej aplikacji (zgodna z TZ kontenerów: Europe/Warsaw).
date_default_timezone_set('Europe/Warsaw');

// Hartowanie sesji — musi trafić przed pierwszym session_start().
// use_strict_mode blokuje przyjmowanie nieznanych identyfikatorów sesji,
// httponly ukrywa ciasteczko przed JS, samesite ogranicza wysyłanie w CSRF.
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

// Ciasteczko session tylko po HTTPS i wyłącznie w środowisku produkcyjnym.
if (($config['app']['env'] ?? 'local') === 'prod') {
    ini_set('session.cookie_secure', '1');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require BASE_PATH . '/app/controllers/HomeController.php';
require BASE_PATH . '/app/models/Database.php';
require BASE_PATH . '/app/models/User.php';
require BASE_PATH . '/app/controllers/AuthController.php';

$page = $_GET['page'] ?? 'home';
if (!is_string($page)) {
    $page = '';
}

// Prosta biała lista stron; parametr URL nie jest używany do dołączania plików.
switch ($page) {
    case 'home':
        (new HomeController())->index($config);
        break;

    case 'login':
        (new AuthController($config))->login();
        break;

    case 'register':
        (new AuthController($config))->register();
        break;

    case 'logout':
        (new AuthController($config))->logout();
        break;

    default:
        http_response_code(404);
        $pageTitle = 'Nie znaleziono strony';
        require BASE_PATH . '/app/views/layout/header.php';
        echo '<h2>404</h2><p>Nie znaleziono strony.</p>';
        require BASE_PATH . '/app/views/layout/footer.php';
        break;
}
