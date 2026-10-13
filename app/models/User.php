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

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, first_name, last_name, email, phone, role, is_active
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function create(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $passwordHash,
        string $role = 'klient'
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (first_name, last_name, email, phone, password, role)
             VALUES (:first_name, :last_name, :email, :phone, :password, :role)'
        );
        $statement->execute([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'phone'      => $phone,
            'password'   => $passwordHash,
            'role'       => $role,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    // Aktualizuje dane profilu. ID pochodzi wyłącznie z sesji — nigdy z formularza.
    public function updateProfile(int $id, string $firstName, string $lastName, string $phone): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE users
             SET first_name = :first_name, last_name = :last_name, phone = :phone
             WHERE id = :id'
        );
        $statement->execute([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'phone'      => $phone,
            'id'         => $id,
        ]);

        return true;
    }
}
