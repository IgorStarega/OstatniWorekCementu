<?php
// Tokeny jednorazowe dla weryfikacji adresu e-mail.
// W bazie trzymamy wyłącznie hash SHA-256 — surowy token istnieje tylko w linku.

// Tworzy token weryfikacji e-maila (jeden aktywny na konto: usuwa poprzednie),
// zapisuje w bazie TYLKO hash SHA-256 i zwraca surowy token.
function issueEmailVerificationToken(array $config, int $userId): string
{
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 24 * 3600);

    $verifications = new EmailVerification(Database::connect($config['db']));
    $verifications->deleteByUserId($userId);
    $verifications->create($userId, hash('sha256', $token), $expiresAt);

    return $token;
}
