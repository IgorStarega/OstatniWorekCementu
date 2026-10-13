<?php
// Ochrona formularzy przed atakami CSRF (Cross-Site Request Forgery).

// Zwraca token CSRF bieżącej sesji; tworzy go bezpiecznym losowaniem przy pierwszym użyciu.
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Weryfikuje token przesłany w formularzu. Przy niezgodności kończy żądanie kodem 403.
function csrf_verify(?string $token): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $expected = $_SESSION['csrf_token'] ?? '';

    if ($token === null || $token === '' || !is_string($expected) || $expected === ''
        || !hash_equals($expected, $token)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="pl"><head><meta charset="UTF-8">'
            . '<title>Brak dostępu</title></head><body>'
            . '<h1>403 — brak dostępu</h1>'
            . '<p>Nieprawidłowy lub wygasły token formularza. Wróć i spróbuj ponownie.</p>'
            . '</body></html>';
        exit;
    }
}
