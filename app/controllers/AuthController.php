<?php
// Obsługa formularzy logowania i rejestracji.
class AuthController
{
    // Po tylu nieudanych próbach konto jest czasowo blokowane.
    private const MAX_LOGIN_ATTEMPTS = 5;

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
            csrf_verify(postString('csrf_token'));

            $email = mb_strtolower(trim(postString('email')));
            $password = postString('password');

            if (($error = validationEmail($email, 'Podaj poprawny adres e-mail.')) !== null) {
                $errors[] = $error;
            }
            if ($password === '') {
                $errors[] = 'Podaj hasło.';
            }

            if ($errors === []) {
                $users = userRepository($this->config);
                $user = $users->findByEmail($email);

                if ($user && $this->isLocked($user)) {
                    // Konto z aktywną blokadą — nawet poprawne hasło nie przechodzi.
                    $errors[] = 'Konto jest tymczasowo zablokowane. Spróbuj ponownie za '
                        . $this->lockMinutesLeft($user) . ' min.';
                } elseif ($user && (int) $user['is_active'] === 1
                    && password_verify($password, $user['password'])) {
                    // Polityka B: konto bez potwierdzonego e-maila nie może się zalogować.
                    if (empty($user['email_verified_at'])) {
                        $errors[] = 'Najpierw potwierdź adres e-mail. Sprawdź skrzynkę '
                            . 'lub wyślij link ponownie na stronie potwierdzenia.';
                    } else {
                        $users->clearFailedLogins((int) $user['id']);

                        if (session_status() !== PHP_SESSION_ACTIVE) {
                            session_start();
                        }
                        session_regenerate_id(true);

                        // W sesji trzymamy wyłącznie user_id i role (bez hasła i danych osobowych).
                        $_SESSION['user_id'] = (int) $user['id'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['email_verified'] = true;

                        header('Location: ' . $this->homeForRole((string) $user['role']));
                        exit;
                    }
                } else {
                    // Ten sam komunikat dla złego hasła i nieistniejącego konta (ochrona przed enumeracją).
                    if ($user) {
                        $attempts = (int) $user['failed_attempts'] + 1;
                        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
                            $users->lockAccount((int) $user['id']);
                            $errors[] = 'Konto zostało tymczasowo zablokowane na 15 minut.';
                        } else {
                            $users->incrementFailedAttempts((int) $user['id']);
                            $errors[] = 'Nieprawidłowy e-mail lub hasło.';
                        }
                    } else {
                        $errors[] = 'Nieprawidłowy e-mail lub hasło.';
                    }
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

        csrf_verify(postString('csrf_token'));

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
        $verificationLink = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));

            $firstName = trim(postString('first_name'));
            $lastName = trim(postString('last_name'));
            $email = mb_strtolower(trim(postString('email')));
            $phone = trim(postString('phone'));
            $password = postString('password');
            $passwordConfirmation = postString('password_confirmation');

            // Backendowa walidacja danych wejściowych — reguły we wspólnym helperze.
            foreach ([
                validationFirstName($firstName),
                validationLastName($lastName),
                validationEmail($email),
                validationPhone($phone),
                validationPassword($password),
            ] as $error) {
                if ($error !== null) {
                    $errors[] = $error;
                }
            }
            if ($password !== $passwordConfirmation) {
                $errors[] = 'Hasła nie są takie same.';
            }

            if ($errors === []) {
                $users = userRepository($this->config);
                // Sprawdzenie, czy podany adres e-mail już istnieje w bazie (zapobieganie błędom SQL)
                if ($users->findByEmail($email)) {
                    $errors[] = 'Konto z tym adresem e-mail już istnieje.';
                } else {
                    try {
                        // Zapis hasła do bazy wyłącznie przy użyciu password_hash(), z rolą klient
                        $userId = $users->create(
                            $firstName,
                            $lastName,
                            $email,
                            $phone,
                            password_hash($password, PASSWORD_DEFAULT),
                            'klient'
                        );
                        // Token zapisujemy PRZED próbą wysyłki — dzięki temu fallback
                        // w trybie lokalnym ma czym potwierdzić konto.
                        $token = issueEmailVerificationToken($this->config, $userId);
                        $sent = sendVerificationEmail($this->config, $email, $firstName, $token);

                        if ($sent) {
                            $message = 'Konto utworzone. Sprawdź skrzynkę e-mail i potwierdź adres, '
                                . 'aby się zalogować.';
                        } elseif (($this->config['app']['env'] ?? 'local') === 'local') {
                            // Brak SMTP w trybie lokalnym: pokazujemy link na ekranie
                            // (ten sam wzorzec co przy resecie hasła).
                            $verificationLink = absoluteUrl('/?page=verify-email&token=' . $token);
                            $message = 'Konto utworzone. Tryb lokalny (brak wysyłki e-mail) — '
                                . 'potwierdź adres linkiem poniżej.';
                        } else {
                            // prod: SMTP ma działać. Szczegóły tylko do logu, bez obietnic wobec użytkownika.
                            error_log('Weryfikacja e-mail: wysyłka nie powiodła się dla user_id=' . $userId);
                            $message = 'Konto utworzone. Jeśli nie otrzymasz wiadomości, '
                                . 'użyj opcji ponownej wysyłki.';
                        }

                        // Wyczyszczenie formularza po pomyślnym utworzeniu konta
                        $firstName = '';
                        $lastName = '';
                        $email = '';
                        $phone = '';
                    } catch (PDOException $exception) {
                        // Bezpieczna obsługa naruszenia unikalności (np. wyścig zapytań).
                        if ($exception->getCode() === '23000') {
                            $errors[] = 'Konto z tym adresem e-mail już istnieje.';
                        } else {
                            // Szczegóły lądują w logu, użytkownik widzi tylko ogólny komunikat.
                            error_log('Rejestracja nie powiodła się: ' . $exception->getMessage());
                            $errors[] = 'Wystąpił błąd serwera. Spróbuj ponownie później.';
                        }
                    }
                }
            }
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/auth/register.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    // Czy konto ma aktywną blokadę (locked_until w przyszłości)?
    private function isLocked(array $user): bool
    {
        return !empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time();
    }

    // Ile pełnych minut zostało do końca blokady (co najmniej 1).
    private function lockMinutesLeft(array $user): int
    {
        $seconds = strtotime((string) $user['locked_until']) - time();

        return max(1, (int) ceil($seconds / 60));
    }

    // Adres startowy po zalogowaniu, zależny od roli użytkownika.
    private function homeForRole(string $role): string
    {
        return $role === 'klient' ? '/?page=profile' : '/?page=dashboard';
    }
}
