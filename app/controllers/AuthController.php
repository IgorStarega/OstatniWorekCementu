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
            header('Location: /?page=home');
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

                    // Zapisanie w tablicy $_SESSION kluczowych danych: user_id oraz role (bez hasła!)
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user'] = [
                        'id'         => (int) $user['id'],
                        'first_name' => $user['first_name'],
                        'last_name'  => $user['last_name'],
                        'email'      => $user['email'],
                        'role'       => $user['role'],
                    ];

                    header('Location: /?page=home');
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

        require BASE_PATH . '/public/logout.php';
    }

    public function register(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!empty($_SESSION['user_id']) || !empty($_SESSION['user'])) {
            header('Location: /?page=home');
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

    private function postString(string $key): string
    {
        return is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
    }
}
