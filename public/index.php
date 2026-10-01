<?php
// Jedyny punkt wejścia aplikacji (front controller). Cały ruch HTTP przechodzi tędy.

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/config/config.php';

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

    default:
        http_response_code(404);
        $pageTitle = 'Nie znaleziono strony';
        require BASE_PATH . '/app/views/layout/header.php';
        echo '<h2>404</h2><p>Nie znaleziono strony.</p>';
        require BASE_PATH . '/app/views/layout/footer.php';
        break;
}
