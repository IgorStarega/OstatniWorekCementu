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
        $pageTitle = 'Logowanie';
        $errors = [];
        $email = '';
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($this->postString('email'));
            $password = $this->postString('password');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
                $errors[] = 'Podaj poprawny adres e-mail.';
            }
            if ($password === '') {
                $errors[] = 'Podaj hasło.';
            }

            if ($errors === []) {
                $user = $this->getUsers()->findByEmail($email);
                if ($user && (int) $user['is_active'] === 1 && password_verify($password, $user['password'])) {
                    if (session_status() !== PHP_SESSION_ACTIVE) {
                        session_start();
                    }
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => (int) $user['id'],
                        'role' => $user['role'],
                    ];
                    $message = 'Zalogowano poprawnie.';
                } else {
                    $errors[] = 'Nieprawidłowy e-mail lub hasło.';
                }
            }
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/auth/login.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    public function register(): void
    {
        $pageTitle = 'Rejestracja';
        $errors = [];
        $firstName = '';
        $lastName = '';
        $email = '';
        $phone = '';
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = trim($this->postString('first_name'));
            $lastName = trim($this->postString('last_name'));
            $email = trim($this->postString('email'));
            $phone = trim($this->postString('phone'));
            $password = $this->postString('password');
            $passwordConfirmation = $this->postString('password_confirmation');

            if ($firstName === '' || mb_strlen($firstName) > 50) {
                $errors[] = 'Imię jest wymagane i może mieć maksymalnie 50 znaków.';
            }
            if ($lastName === '' || mb_strlen($lastName) > 50) {
                $errors[] = 'Nazwisko jest wymagane i może mieć maksymalnie 50 znaków.';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
                $errors[] = 'Podaj poprawny adres e-mail (maksymalnie 100 znaków).';
            }
            if ($phone === '' || mb_strlen($phone) > 20) {
                $errors[] = 'Numer telefonu jest wymagany (maksymalnie 20 znaków).';
            }
            if (strlen($password) < 8) {
                $errors[] = 'Hasło musi mieć co najmniej 8 znaków.';
            }
            if ($password !== $passwordConfirmation) {
                $errors[] = 'Hasła nie są takie same.';
            }

            if ($errors === []) {
                $users = $this->getUsers();
                if ($users->findByEmail($email)) {
                    $errors[] = 'Konto z tym adresem e-mail już istnieje.';
                } else {
                    try {
                        $users->create(
                            $firstName,
                            $lastName,
                            $email,
                            $phone,
                            password_hash($password, PASSWORD_DEFAULT)
                        );
                        $message = 'Konto utworzone. Możesz się teraz zalogować.';
                    } catch (PDOException $exception) {
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

    private function postString(string $key): string
    {
        return is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
    }
}
