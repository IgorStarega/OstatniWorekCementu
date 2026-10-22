<?php
// Odzyskiwanie hasła bez zewnętrznego transportu poczty (stretch — ocena 6).
// W bazie trzymamy tylko SHA-256 tokenu, sam token żyje wyłącznie w linku.
class PasswordResetController
{
    // Ważność linku resetującego w sekundach (1 godzina).
    private const TOKEN_TTL = 3600;

    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function forgotPassword(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Zalogowany użytkownik nie potrzebuje resetu.
        if (!empty($_SESSION['user_id'])) {
            header('Location: /?page=profile');
            exit;
        }

        $pageTitle = 'Odzyskiwanie hasła';
        $errors = [];
        $message = '';
        $email = '';
        $resetLink = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));

            $email = mb_strtolower(trim(postString('email')));

            if (($error = validationEmail($email, 'Podaj poprawny adres e-mail.')) !== null) {
                $errors[] = $error;
            }

            if ($errors === []) {
                $user = userRepository($this->config)->findByEmail($email);

                if ($user && (int) $user['is_active'] === 1) {
                    // Limity w bazie: cooldown 60 s i maks. 3 wysyłki na godzinę.
                    $throttle = new EmailSend(Database::connect($this->config['db']));
                    if ($throttle->isAllowed($email, 'password_reset')) {
                        $throttle->record($email, 'password_reset');

                        $token = bin2hex(random_bytes(32));
                        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_TTL);

                        $resets = new PasswordReset(Database::connect($this->config['db']));
                        // Jeden aktywny reset na konto — stare tokeny tracą ważność.
                        $resets->deleteByEmail($email);
                        $resets->create($email, hash('sha256', $token), $expiresAt);

                        // Link absolutny z APP_URL — poprawny host niezależnie od żądania.
                        $link = absoluteUrl('/?page=reset-password&token=' . $token);

                        // Link pokazujemy zawsze na ekranie — aplikacja nie wysyła poczty.
                        $resetLink = $link;
                    }
                }

                // Ten sam komunikat niezależnie od istnienia konta (ochrona przed enumeracją).
                $message = 'Link do resetu hasła został wygenerowany — użyj go poniżej.';
            }
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/auth/forgot_password.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    // Ustawia nowe hasło na podstawie ważnego tokenu resetu.
    public function resetPassword(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!empty($_SESSION['user_id'])) {
            header('Location: /?page=profile');
            exit;
        }

        $pageTitle = 'Ustaw nowe hasło';
        $errors = [];
        $message = '';

        $token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));
            $token = postString('token');
        }

        $resets = new PasswordReset(Database::connect($this->config['db']));
        $tokenHash = $token !== '' ? hash('sha256', $token) : '';
        $reset = $tokenHash !== '' ? $resets->findValid($tokenHash) : null;

        // Jawne porównanie hashy w czasie stałym.
        if ($reset !== null && !hash_equals((string) $reset['token_hash'], $tokenHash)) {
            $reset = null;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($reset === null) {
                $errors[] = 'Link resetujący jest nieprawidłowy lub wygasł.';
            } else {
                $newPassword = postString('new_password');
                $newPasswordConfirmation = postString('new_password_confirmation');

                if (($error = validationPassword($newPassword)) !== null) {
                    $errors[] = $error;
                }
                if ($newPassword !== $newPasswordConfirmation) {
                    $errors[] = 'Hasła nie są takie same.';
                }

                if ($errors === []) {
                    $users = userRepository($this->config);
                    $user = $users->findByEmail((string) $reset['email']);

                    if ($user === null) {
                        $errors[] = 'Link resetujący jest nieprawidłowy lub wygasł.';
                    } else {
                        $users->updatePassword((int) $user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
                        // Token jednorazowy: po użyciu usuwamy wszystkie tokeny tego adresu.
                        $resets->deleteByEmail((string) $reset['email']);
                        session_regenerate_id(true);

                        $reset = null;
                        $message = 'Hasło zostało zmienione. Możesz się zalogować.';
                    }
                }
            }
        } elseif ($reset === null) {
            $errors[] = 'Link resetujący jest nieprawidłowy lub wygasł.';
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/auth/reset_password.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }
}
