<?php
// Wysyłka poczty i treści wiadomości.
// PHP mail() przekazuje wiadomość do lokalnego msmtp (patrz docker/php/entrypoint.sh),
// więc aplikacja nie potrzebuje Composera ani biblioteki SMTP.

// Zakodowanie nazwy nadawcy w nagłówku (polskie znaki w "Ostatni Worek Cementu").
function mailHeaderName(string $name): string
{
    return '=?UTF-8?B?' . base64_encode($name) . '?=';
}

// Wysyła prosty tekstowy e-mail. Zwraca true/false i nigdy nie rzuca wyjątkiem —
// awaria SMTP ma dawać ten sam neutralny komunikat użytkownikowi co sukces.
function sendMail(array $config, string $to, string $subject, string $body): bool
{
    $mail = $config['mail'] ?? [];

    // Brak skonfigurowanego relay (np. local) — nie ma czego wysyłać.
    if (empty($mail['host']) || empty($mail['from'])) {
        return false;
    }

    $headers = implode("\r\n", [
        'From: ' . mailHeaderName((string) ($mail['from_name'] ?? '')) . ' <' . $mail['from'] . '>',
        'Reply-To: ' . $mail['from'],
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ]);

    try {
        return @mail(
            $to,
            '=?UTF-8?B?' . base64_encode($subject) . '?=',
            $body,
            $headers
        );
    } catch (Throwable $exception) {
        error_log('Wysyłka e-mail nie powiodła się: ' . $exception->getMessage());

        return false;
    }
}

// Tworzy jednorazowy token weryfikacji e-maila (jeden aktywny na konto),
// zapisuje w bazie tylko jego SHA-256 i wysyła link z APP_URL.
function sendVerificationEmail(array $config, int $userId, string $email, string $firstName): bool
{
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 24 * 3600);

    $verifications = new EmailVerification(Database::connect($config['db']));
    $verifications->deleteByUserId($userId);
    $verifications->create($userId, hash('sha256', $token), $expiresAt);

    $link = absoluteUrl('/?page=verify-email&token=' . $token);
    $body = 'Dzień dobry' . ($firstName !== '' ? ', ' . $firstName : '') . ',' . "\r\n\r\n"
        . 'dziękujemy za rejestrację w Systemie Rezerwacji Usług Budowlanych.' . "\r\n"
        . 'Aby potwierdzić adres e-mail, otwórz link (ważny 24 godziny):' . "\r\n\r\n"
        . $link . "\r\n\r\n"
        . 'Jeśli to nie Ty zakładałeś konto, zignoruj tę wiadomość.' . "\r\n";

    return sendMail($config, $email, 'Potwierdź adres e-mail', $body);
}
