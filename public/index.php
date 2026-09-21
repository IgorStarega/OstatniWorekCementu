<?php
// Jedyny punkt wejścia aplikacji (front controller). Cały ruch HTTP przechodzi tędy.

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/config/config.php';

require BASE_PATH . '/app/controllers/HomeController.php';

// Routing z białej listy — nigdy nie dołączamy plików na podstawie surowego inputu.
$routes = [
    'home' => [HomeController::class, 'index'],
];

$page = $_GET['page'] ?? 'home';

if (!isset($routes[$page])) {
    http_response_code(404);
    $pageTitle = 'Nie znaleziono strony';
    require BASE_PATH . '/app/views/layout/header.php';
    echo '<h2>404</h2><p>Nie znaleziono strony.</p>';
    require BASE_PATH . '/app/views/layout/footer.php';
    exit;
}

[$controllerClass, $method] = $routes[$page];
$controller = new $controllerClass();
$controller->$method($config);
