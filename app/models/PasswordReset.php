<?php
// Operacje bazodanowe dotyczące jednorazowych tokenów resetu hasła.
// W bazie trzymamy wyłącznie SHA-256 tokenu — nigdy samego tokenu z linku.
class PasswordReset
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Usuwa poprzednie tokeny danego adresu (jeden aktywny reset na konto).
    public function deleteByEmail(string $email): void
    {
        $statement = $this->pdo->prepare('DELETE FROM password_resets WHERE email = :email');
        $statement->execute(['email' => $email]);
    }

    public function create(string $email, string $tokenHash, string $expiresAt): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO password_resets (email, token_hash, expires_at)
             VALUES (:email, :token_hash, :expires_at)'
        );
        $statement->execute([
            'email'      => $email,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    // Zwraca ważny (nieprzeterminowany) wpis dla danego hasha tokenu albo null.
    public function findValid(string $tokenHash): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, email, token_hash, expires_at
             FROM password_resets
             WHERE token_hash = :token_hash AND expires_at > NOW()
             LIMIT 1'
        );
        $statement->execute(['token_hash' => $tokenHash]);
        $reset = $statement->fetch();

        return $reset ?: null;
    }
}
