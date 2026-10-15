<?php
// Pomocnicze funkcje dostępu oparte na sesji użytkownika.

// ID zalogowanego użytkownika albo null dla gościa.
function currentUserId(): ?int
{
    if (!empty($_SESSION['user_id'])) {
        return (int) $_SESSION['user_id'];
    }

    return null;
}

// Rola zalogowanego użytkownika albo null dla gościa.
function currentUserRole(): ?string
{
    if (!empty($_SESSION['role']) && is_string($_SESSION['role'])) {
        return $_SESSION['role'];
    }

    return null;
}

// Czy zalogowany użytkownik potwierdził adres e-mail? (false dla gościa)
function currentUserEmailVerified(): bool
{
    return !empty($_SESSION['email_verified']);
}

// Wymaga zalogowania; gościa odsyła na stronę logowania.
function requireLogin(): void
{
    if (currentUserId() === null) {
        header('Location: /?page=login');
        exit;
    }
}

// Wymaga zalogowania i jednej z podanych ról; w razie braku uprawnień kończy widokiem 403.
function requireRole(string ...$roles): void
{
    requireLogin();

    if (!in_array(currentUserRole(), $roles, true)) {
        http_response_code(403);
        $pageTitle = 'Brak dostępu';
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/errors/403.php';
        require BASE_PATH . '/app/views/layout/footer.php';
        exit;
    }
}

// Jedno miejsce tworzenia repozytorium użytkowników (współdzieli połączenie PDO).
function userRepository(array $config): User
{
    return new User(Database::connect($config['db']));
}

// Imię zalogowanego użytkownika pobierane z bazy (sesja trzyma tylko user_id i role).
function currentUserFirstName(): string
{
    $userId = currentUserId();
    if ($userId === null) {
        return 'Użytkownik';
    }

    $config = $GLOBALS['config'] ?? [];
    if (empty($config['db'])) {
        return 'Użytkownik';
    }

    try {
        $user = userRepository($config)->findById($userId);
    } catch (Throwable $exception) {
        return 'Użytkownik';
    }

    return (string) ($user['first_name'] ?? 'Użytkownik');
}
