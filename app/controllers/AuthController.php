<?php
// Obsługa formularzy logowania i rejestracji.
class AuthController
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function login(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . $this->homeForRole((string) ($_SESSION['role'] ?? 'klient')));
            exit;
        }

        $pageTitle = 'Logowanie';
        $errors = [];
        $email = '';
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify($this->postString('csrf_token'));

            $email = mb_strtolower(trim($this->postString('email')));
            $password = $this->postString('password');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
                $errors[] = 'Podaj poprawny adres e-mail.';
            }
            if ($password === '') {
                $errors[] = 'Podaj hasło.';
            }

            if ($errors === []) {
                $user = $this->getUsers()->findByEmail($email);
                // Weryfikacja hasła za pomocą password_verify() i sprawdzenie aktywności konta
                if ($user && (int) $user['is_active'] === 1 && password_verify($password, $user['password'])) {
                    if (session_status() !== PHP_SESSION_ACTIVE) {
                        session_start();
                    }
                    session_regenerate_id(true);

                    // W sesji trzymamy wyłącznie user_id i role (bez hasła i danych osobowych).
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['role'] = $user['role'];

                    header('Location: ' . $this->homeForRole((string) $user['role']));
                    exit;
                } else {
                    $errors[] = 'Nieprawidłowy e-mail lub hasło.';
                }
            }
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/auth/login.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    public function logout(): void
    {
        // Wylogowanie wyłącznie metodą POST i z poprawnym tokenem CSRF.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit;
        }

        csrf_verify($this->postString('csrf_token'));

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: /?page=home');
        exit;
    }

    public function register(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . $this->homeForRole((string) ($_SESSION['role'] ?? 'klient')));
            exit;
        }

        $pageTitle = 'Rejestracja';
        $errors = [];
        $firstName = '';
        $lastName = '';
        $email = '';
        $phone = '';
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify($this->postString('csrf_token'));

            $firstName = trim($this->postString('first_name'));
            $lastName = trim($this->postString('last_name'));
            $email = mb_strtolower(trim($this->postString('email')));
            $phone = trim($this->postString('phone'));
            $password = $this->postString('password');
            $passwordConfirmation = $this->postString('password_confirmation');

            // Backendowa walidacja danych wejściowych — nigdy nie ufamy danym z frontendu
            if ($firstName === '' || mb_strlen($firstName) < 2 || mb_strlen($firstName) > 50) {
                $errors[] = 'Imię jest wymagane (od 2 do 50 znaków).';
            }
            if ($lastName === '' || mb_strlen($lastName) < 2 || mb_strlen($lastName) > 50) {
                $errors[] = 'Nazwisko jest wymagane (od 2 do 50 znaków).';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
                $errors[] = 'Podaj poprawny adres e-mail (maksymalnie 100 znaków).';
            }
            if ($phone === '' || !preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone)) {
                $errors[] = 'Podaj poprawny numer telefonu (od 7 do 20 znaków, dopuszczalne cyfry, spacje, myślniki i znak +).';
            }
            if (strlen($password) < 8) {
                $errors[] = 'Hasło musi mieć co najmniej 8 znaków.';
            }
            if (strlen($password) > 72) {
                $errors[] = 'Hasło może mieć maksymalnie 72 znaki.';
            }
            if ($password !== $passwordConfirmation) {
                $errors[] = 'Hasła nie są takie same.';
            }

            if ($errors === []) {
                $users = $this->getUsers();
                // Sprawdzenie, czy podany adres e-mail już istnieje w bazie (zapobieganie błędom SQL)
                if ($users->findByEmail($email)) {
                    $errors[] = 'Konto z tym adresem e-mail już istnieje.';
                } else {
                    try {
                        // Zapis hasła do bazy wyłącznie przy użyciu password_hash(), z rolą klient
                        $users->create(
                            $firstName,
                            $lastName,
                            $email,
                            $phone,
                            password_hash($password, PASSWORD_DEFAULT),
                            'klient'
                        );
                        $message = 'Konto utworzone. Możesz się teraz zalogować.';
                        // Wyczyszczenie formularza po pomyślnym utworzeniu konta
                        $firstName = '';
                        $lastName = '';
                        $email = '';
                        $phone = '';
                    } catch (PDOException $exception) {
                        // Bezpieczna obsługa naruszenia unikalności (np. wyścig zapytań)
                        if ($exception->getCode() === '23000') {
                            $errors[] = 'Konto z tym adresem e-mail już istnieje.';
                        } else {
                            throw $exception;
                        }
                    }
                }
            }
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/auth/register.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    private function getUsers(): User
    {
        $pdo = Database::connect($this->config['db']);
        return new User($pdo);
    }

    // Adres startowy po zalogowaniu, zależny od roli użytkownika.
    private function homeForRole(string $role): string
    {
        return $role === 'klient' ? '/?page=profile' : '/?page=dashboard';
    }

    private function postString(string $key): string
    {
        return is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
    }
}
