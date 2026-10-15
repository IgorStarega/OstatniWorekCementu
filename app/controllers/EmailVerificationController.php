<?php
// Potwierdzanie adresu e-mail po rejestracji (link z tokenem + ponowna wysyłka).
class EmailVerificationController
{
    // Cel wysyłki używany w dzienniku limitów (email_sends).
    private const PURPOSE = 'verification';

    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function verify(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!empty($_SESSION['user_id'])) {
            $current = userRepository($this->config)->findById((int) $_SESSION['user_id']);
            if ($current !== null && !empty($current['email_verified_at'])) {
                // Sesje sprzed wdrożenia weryfikacji nie mają klucza email_verified —
                // stan rozstrzygamy z bazy i uzupełniamy sesję na przyszłość.
                $_SESSION['email_verified'] = true;
                header('Location: /?page=home');
                exit;
            }
        }

        $pageTitle = 'Potwierdzenie e-maila';
        $status = '';   // 'success' albo 'error' — steruje widokiem
        $message = '';
        $errors = [];
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));

            $email = mb_strtolower(trim(postString('email')));
            if (($error = validationEmail($email)) !== null) {
                $errors[] = $error;
            }

            if ($errors === []) {
                $user = userRepository($this->config)->findByEmail($email);

                if ($user && (int) $user['is_active'] === 1 && empty($user['email_verified_at'])) {
                    $throttle = new EmailSend(Database::connect($this->config['db']));
                    if ($throttle->isAllowed($email, self::PURPOSE)) {
                        $throttle->record($email, self::PURPOSE);
                        $token = issueEmailVerificationToken($this->config, (int) $user['id']);
                        sendVerificationEmail($this->config, $email, (string) $user['first_name'], $token);
                    }
                }

                // Ten sam komunikat niezależnie od istnienia konta i stanu licznika
                // (ochrona przed ujawnianiem, które adresy są w systemie).
                $message = 'Jeśli konto z tym adresem wymaga potwierdzenia, wysłaliśmy nowy link.';
            }
        } else {
            $token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';

            if ($token !== '') {
                $verifications = new EmailVerification(Database::connect($this->config['db']));
                $tokenHash = hash('sha256', $token);
                $verification = $verifications->findValid($tokenHash);

                // Jawne porównanie hashy w czasie stałym.
                if ($verification !== null && hash_equals((string) $verification['token_hash'], $tokenHash)) {
                    $users = userRepository($this->config);
                    $users->markEmailVerified((int) $verification['user_id']);
                    // Token jednorazowy: po użyciu kasujemy wszystkie tokeny konta.
                    $verifications->deleteByUserId((int) $verification['user_id']);

                    $status = 'success';
                    $message = 'Adres e-mail został potwierdzony. Możesz się teraz zalogować.';
                } else {
                    $status = 'error';
                    $message = 'Link potwierdzający jest nieprawidłowy, wygasł lub został już użyty.';
                }
            }

            // Zalogowany, niezweryfikowany użytkownik: podpowiedz jego własny adres.
            if ($email === '' && !empty($_SESSION['user_id'])) {
                $current = userRepository($this->config)->findById(currentUserId());
                $email = $current ? (string) $current['email'] : '';
            }
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/auth/verify_email.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }
}
