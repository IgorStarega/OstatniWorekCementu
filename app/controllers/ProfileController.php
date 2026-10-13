<?php
// Panel klienta: podgląd i edycja danych profilu.
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
        $user = $this->getUsers()->findById($userId);

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
            csrf_verify($this->postString('csrf_token'));

            $firstName = trim($this->postString('first_name'));
            $lastName = trim($this->postString('last_name'));
            $phone = trim($this->postString('phone'));

            // Walidacja serwerowa identyczna jak przy rejestracji.
            if ($firstName === '' || mb_strlen($firstName) < 2 || mb_strlen($firstName) > 50) {
                $errors[] = 'Imię jest wymagane (od 2 do 50 znaków).';
            }
            if ($lastName === '' || mb_strlen($lastName) < 2 || mb_strlen($lastName) > 50) {
                $errors[] = 'Nazwisko jest wymagane (od 2 do 50 znaków).';
            }
            if ($phone === '' || !preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone)) {
                $errors[] = 'Podaj poprawny numer telefonu (od 7 do 20 znaków, dopuszczalne cyfry, spacje, myślniki i znak +).';
            }

            if ($errors === []) {
                // ID wyłącznie z sesji (currentUserId) — żadnego ID z POST/GET (ochrona przed IDOR).
                $this->getUsers()->updateProfile($userId, $firstName, $lastName, $phone);
                $message = 'Dane profilu zostały zapisane.';
            }
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/profile/index.php';
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
