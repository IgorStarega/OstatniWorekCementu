<?php
// Operacje bazodanowe dotyczące kont użytkowników.
class User
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, first_name, last_name, email, password, role, is_active
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function create(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $passwordHash
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (first_name, last_name, email, phone, password)
             VALUES (:first_name, :last_name, :email, :phone, :password)'
        );
        $statement->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'password' => $passwordHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
