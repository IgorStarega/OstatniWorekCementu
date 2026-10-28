<?php
// Operacje bazodanowe dotyczące kont użytkowników.
class User
{
    // Jedyne dozwolone role — zgodne z enumem w bazie.
    public const ROLES = ['klient', 'pracownik', 'admin'];

    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Lista użytkowników z opcjonalnymi filtrami. Walidacja whitelisty ról w modelu.
    public function allWithFilters(?string $role, ?int $active): array
    {
        $conditions = [];
        $params = [];

        if ($role !== null && in_array($role, self::ROLES, true)) {
            $conditions[] = 'role = :role';
            $params['role'] = $role;
        }

        if ($active !== null) {
            $conditions[] = 'is_active = :active';
            $params['active'] = $active === 1 ? 1 : 0;
        }

        $statement = $this->pdo->prepare(
            'SELECT id, first_name, last_name, email, phone, role, is_active, created_at
             FROM users'
            . ($conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions))
            . ' ORDER BY created_at DESC'
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    // Zmiana roli — whitelist ['klient','pracownik','admin'] W MODELU.
    // Zwraca false, gdy rola spoza listy.
    public function updateRole(int $id, string $role): bool
    {
        if (!in_array($role, self::ROLES, true)) {
            return false;
        }

        $statement = $this->pdo->prepare(
            'UPDATE users SET role = :role WHERE id = :id'
        );
        $statement->execute([
            'role' => $role,
            'id'   => $id,
        ]);

        return true;
    }

    public function setActive(int $id, bool $active): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET is_active = :is_active WHERE id = :id'
        );
        $statement->execute([
            'is_active' => $active ? 1 : 0,
            'id'        => $id,
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, first_name, last_name, email, password, role, is_active,
                    failed_attempts, locked_until, email_verified_at
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
            'SELECT id, first_name, last_name, email, phone, role, is_active, email_verified_at
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

    // Oznacza adres e-mail jako potwierdzony (data tylko przy pierwszym potwierdzeniu).
    public function markEmailVerified(int $id): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET email_verified_at = NOW() WHERE id = :id AND email_verified_at IS NULL'
        );
        $statement->execute(['id' => $id]);
    }
}
