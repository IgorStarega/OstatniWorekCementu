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
            'SELECT id, first_name, last_name, email, password, role, is_active,
                    failed_attempts, locked_until
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

    // Pobiera konto razem z hashem — używane wyłącznie przy zmianie hasła.
    public function findWithPassword(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, email, password
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

    // Ustawia nowy hash hasła. ID pochodzi wyłącznie z sesji lub z ważnego tokenu resetu.
    public function updatePassword(int $id, string $passwordHash): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET password = :password WHERE id = :id'
        );
        $statement->execute([
            'password' => $passwordHash,
            'id'       => $id,
        ]);
    }

    // Zwiększa licznik nieudanych logowań o jeden.
    public function incrementFailedAttempts(int $id): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    // Blokuje konto na 15 minut i zeruje licznik prób.
    public function lockAccount(int $id): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users
             SET failed_attempts = 0, locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
             WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    // Zeruje licznik i zdejmuje blokadę po udanym logowaniu.
    public function clearFailedLogins(int $id): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }
}
