<?php
// Panel klienta: podgląd i edycja danych profilu oraz zmiana hasła.
class ProfileController
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function index(): void
    {
        requireLogin();

        $userId = currentUserId();
        $user = userRepository($this->config)->findById($userId);

        // Konto mogło zostać usunięte w międzyczasie — czyścimy sesję.
        if ($user === null) {
            $_SESSION = [];
            session_destroy();
            header('Location: /?page=login');
            exit;
        }

        $pageTitle = 'Mój profil';
        $errors = [];
        $message = '';
        $firstName = (string) $user['first_name'];
        $lastName = (string) $user['last_name'];
        $phone = (string) ($user['phone'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));

            if (postString('form') === 'password') {
                $this->changePassword($userId, $errors, $message);
            } else {
                $firstName = trim(postString('first_name'));
                $lastName = trim(postString('last_name'));
                $phone = trim(postString('phone'));

                // Walidacja serwerowa identyczna jak przy rejestracji (wspólny helper).
                $errors = array_merge($errors, collectErrors([
                    validationFirstName($firstName),
                    validationLastName($lastName),
                    validationPhone($phone),
                ]));

                if ($errors === []) {
                    // ID wyłącznie z sesji (currentUserId) — żadnego ID z POST/GET (ochrona przed IDOR).
                    userRepository($this->config)->updateProfile($userId, $firstName, $lastName, $phone);
                    $message = 'Dane profilu zostały zapisane.';
                }
            }
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/profile/index.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    // Zmiana hasła po podaniu obecnego. Weryfikacja chroni przed przejęciem cudzej sesji.
    private function changePassword(int $userId, array &$errors, string &$message): void
    {
        $currentPassword = postString('current_password');
        $newPassword = postString('new_password');
        $newPasswordConfirmation = postString('new_password_confirmation');

        $users = userRepository($this->config);
        $user = $users->findWithPassword($userId);

        if ($user === null) {
            $errors[] = 'Nie znaleziono konta.';

            return;
        }

        if (!password_verify($currentPassword, (string) $user['password'])) {
            $errors[] = 'Obecne hasło jest nieprawidłowe.';
        }

        $passwordError = validationPassword($newPassword);
        if ($passwordError !== null) {
            $errors[] = $passwordError;
        } elseif ($newPassword === $currentPassword) {
            $errors[] = 'Nowe hasło musi różnić się od obecnego.';
        }

        if ($newPassword !== $newPasswordConfirmation) {
            $errors[] = 'Nowe hasła nie są takie same.';
        }

        if ($errors !== []) {
            return;
        }

        // ID pochodzi wyłącznie z sesji; hasło zapisujemy jako hash.
        $users->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));

        // Unieważniamy stary identyfikator sesji po zmianie poświadczeń.
        session_regenerate_id(true);
        $message = 'Hasło zostało zmienione.';
    }
}
