<?php
// Operacje bazodanowe dotyczące jednorazowych tokenów potwierdzenia e-maila.
// W bazie trzymamy wyłącznie SHA-256 tokenu — nigdy samego tokenu z linku.
class EmailVerification
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Usuwa poprzednie tokeny konta (jeden aktywny token na konto).
    public function deleteByUserId(int $userId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM email_verifications WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);
    }

    public function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO email_verifications (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, :expires_at)'
        );
        $statement->execute([
            'user_id'    => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    // Zwraca ważny (nieprzeterminowany) wpis dla danego hasha tokenu albo null.
    public function findValid(string $tokenHash): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, user_id, token_hash, expires_at
             FROM email_verifications
             WHERE token_hash = :token_hash AND expires_at > NOW()
             LIMIT 1'
        );
        $statement->execute(['token_hash' => $tokenHash]);
        $verification = $statement->fetch();

        return $verification ?: null;
    }
}
