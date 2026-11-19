<?php
// Globalny komunikat jednorazowy (flash) dla całej aplikacji.
// AdminController ma własny, prywatny mechanizm (admin_flash) i zostaje bez zmian.

// Zapisuje komunikat na czas jednego przekierowania.
// $type: 'status' (sukces) albo 'alert' (błąd); inne wartości traktujemy jak alert.
function flash_set(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION['flash'] = [
        'type'    => $type === 'status' ? 'status' : 'alert',
        'message' => $message,
    ];
}

// Zwraca komunikat i natychmiast go usuwa, żeby nie pojawił się drugi raz.
function flash_pull(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    if (!is_array($flash)
        || !in_array($flash['type'] ?? '', ['status', 'alert'], true)
        || !is_string($flash['message'] ?? null)) {
        return null;
    }

    return $flash;
}
