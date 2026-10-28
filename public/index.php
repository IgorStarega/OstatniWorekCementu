<?php
// Jedyny punkt wejścia aplikacji (front controller). Cały ruch HTTP przechodzi tędy.

define('BASE_PATH', dirname(__DIR__));

// Spójna strefa czasowa całej aplikacji (zgodna z TZ kontenerów: Europe/Warsaw).
date_default_timezone_set('Europe/Warsaw');

// Obsługa błędów zależna od środowiska: prod nigdy nie pokazuje szczegółów,
// local może (ułatwia pracę), ale i tak wyjątki wyświetlamy przez widok 500.
$appEnv = getenv('APP_ENV') ?: 'local';
ini_set('display_errors', $appEnv === 'prod' ? '0' : '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Definicje funkcji i klas ładujemy przed konfiguracją i handlerem błędów,
// żeby widok 500 (korzystający z layoutu) działał nawet gdy config.php rzuci wyjątek.
require BASE_PATH . '/app/helpers/csrf.php';
require BASE_PATH . '/app/helpers/auth.php';
require BASE_PATH . '/app/helpers/validation.php';
require BASE_PATH . '/app/helpers/url.php';
require BASE_PATH . '/app/helpers/token.php';
require BASE_PATH . '/app/models/Database.php';
require BASE_PATH . '/app/models/User.php';
require BASE_PATH . '/app/models/PasswordReset.php';
require BASE_PATH . '/app/models/EmailVerification.php';
require BASE_PATH . '/app/models/EmailSend.php';
require BASE_PATH . '/app/models/ServiceCategory.php';
require BASE_PATH . '/app/models/Service.php';
require BASE_PATH . '/app/models/Employee.php';
require BASE_PATH . '/app/models/EmployeeService.php';
require BASE_PATH . '/app/models/EmployeeAvailability.php';
require BASE_PATH . '/app/models/Reservation.php';
require BASE_PATH . '/app/models/Stats.php';
require BASE_PATH . '/app/controllers/HomeController.php';
require BASE_PATH . '/app/controllers/AuthController.php';
require BASE_PATH . '/app/controllers/PasswordResetController.php';
require BASE_PATH . '/app/controllers/EmailVerificationController.php';
require BASE_PATH . '/app/controllers/ProfileController.php';
require BASE_PATH . '/app/controllers/DashboardController.php';
require BASE_PATH . '/app/controllers/AdminController.php';

// Nieobsłużony wyjątek: zapisujemy szczegóły do logu, a użytkownik widzi
// neutralny widok 500 (bez ścieżek, numerów linii i stack trace).
set_exception_handler(function (Throwable $exception): void {
    error_log(sprintf(
        'Nieobsłużony wyjątek %s: %s w %s:%d',
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));

    if (!headers_sent()) {
        http_response_code(500);
    }

    $pageTitle = 'Błąd serwera';
    require BASE_PATH . '/app/views/layout/header.php';
    require BASE_PATH . '/app/views/errors/500.php';
    require BASE_PATH . '/app/views/layout/footer.php';
});

$config = require BASE_PATH . '/config/config.php';

// Hartowanie sesji — musi trafić przed pierwszym session_start().
// use_strict_mode blokuje przyjmowanie nieznanych identyfikatorów sesji,
// httponly ukrywa ciasteczko przed JS, samesite ogranicza wysyłanie w CSRF.
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

// Ciasteczko session tylko po HTTPS i wyłącznie w środowisku produkcyjnym.
// O HTTPS za reverse proxy wnioskujemy z nagłówków proxy (requestIsHttps()).
if (($config['app']['env'] ?? 'local') === 'prod' && requestIsHttps()) {
    ini_set('session.cookie_secure', '1');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

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

    case 'profile':
        (new ProfileController($config))->index();
        break;

    case 'dashboard':
        (new DashboardController($config))->index();
        break;

    case 'forgot-password':
        (new PasswordResetController($config))->forgotPassword();
        break;

    case 'reset-password':
        (new PasswordResetController($config))->resetPassword();
        break;

    case 'verify-email':
        (new EmailVerificationController($config))->verify();
        break;

    case 'admin-dashboard':
        (new AdminController($config))->dashboard();
        break;

    case 'admin-categories':
        (new AdminController($config))->categories();
        break;

    case 'admin-services':
        (new AdminController($config))->services();
        break;

    case 'admin-employees':
        (new AdminController($config))->employees();
        break;

    case 'admin-employee-services':
        (new AdminController($config))->employeeServices();
        break;

    case 'admin-availability':
        (new AdminController($config))->availability();
        break;

    case 'admin-users':
        (new AdminController($config))->users();
        break;

    case 'admin-reservations':
        (new AdminController($config))->reservations();
        break;

    default:
        http_response_code(404);
        $pageTitle = 'Nie znaleziono strony';
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/errors/404.php';
        require BASE_PATH . '/app/views/layout/footer.php';
        break;
}
