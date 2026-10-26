<?php
// Pracownicy: konto w tabeli users (rola "pracownik") i profil w tabeli employees.
class Employee
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Lista pracowników z danymi konta i liczbą przypisanych usług.
    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT e.id, e.user_id, e.description, e.is_active,
                    u.first_name, u.last_name, u.email, u.phone,
                    (SELECT COUNT(*) FROM employee_services es WHERE es.employee_id = e.id) AS services_count
             FROM employees e
             JOIN users u ON u.id = e.user_id
             ORDER BY u.last_name ASC, u.first_name ASC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT e.id, e.user_id, e.description, e.is_active,
                    u.first_name, u.last_name, u.email, u.phone,
                    (SELECT COUNT(*) FROM employee_services es WHERE es.employee_id = e.id) AS services_count
             FROM employees e
             JOIN users u ON u.id = e.user_id
             WHERE e.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $employee = $statement->fetch();

        return $employee ?: null;
    }

    // Tworzy konto i profil pracownika w jednej transakcji.
    // Bez transakcji błąd drugiego INSERT-a zostawiłby konto bez profilu.
    public function createWithUser(array $user, string $description): int
    {
        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO users
                    (first_name, last_name, email, phone, password, role, is_active, email_verified_at)
                 VALUES
                    (:first_name, :last_name, :email, :phone, :password, :role, 1, NOW())'
            );
            $statement->execute([
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'email'      => $user['email'],
                'phone'      => $user['phone'],
                'password'   => $user['password'],
                'role'       => 'pracownik',
            ]);
            $userId = (int) $this->pdo->lastInsertId();

            $statement = $this->pdo->prepare(
                'INSERT INTO employees (user_id, description, is_active)
                 VALUES (:user_id, :description, 1)'
            );
            $statement->execute([
                'user_id'     => $userId,
                'description' => $description,
            ]);
            $employeeId = (int) $this->pdo->lastInsertId();

            $this->pdo->commit();

            return $employeeId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    // Aktualizuje dane konta oraz opis profilu (bez hasła).
    public function update(int $employeeId, array $user, string $description): void
    {
        $employee = $this->find($employeeId);
        if ($employee === null) {
            throw new RuntimeException('Nie znaleziono pracownika.');
        }

        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                'UPDATE users
                 SET first_name = :first_name, last_name = :last_name,
                     email = :email, phone = :phone
                 WHERE id = :user_id'
            );
            $statement->execute([
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'email'      => $user['email'],
                'phone'      => $user['phone'],
                'user_id'    => $employee['user_id'],
            ]);

            $statement = $this->pdo->prepare(
                'UPDATE employees SET description = :description WHERE id = :id'
            );
            $statement->execute([
                'description' => $description,
                'id'          => $employeeId,
            ]);

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    // Dezaktywacja/aktywacja konta i profilu jednocześnie.
    public function toggleActive(int $employeeId): void
    {
        $employee = $this->find($employeeId);
        if ($employee === null) {
            throw new RuntimeException('Nie znaleziono pracownika.');
        }

        $newValue = (int) $employee['is_active'] === 1 ? 0 : 1;

        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                'UPDATE employees SET is_active = :is_active WHERE id = :id'
            );
            $statement->execute([
                'is_active' => $newValue,
                'id'        => $employeeId,
            ]);

            // Dezaktywacja blokuje logowanie, bo AuthController sprawdza users.is_active.
            $statement = $this->pdo->prepare(
                'UPDATE users SET is_active = :is_active WHERE id = :user_id'
            );
            $statement->execute([
                'is_active' => $newValue,
                'user_id'   => $employee['user_id'],
            ]);

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    // Usuwa konto użytkownika; profil pracownika znika kaskadowo (FK user_id).
    public function delete(int $employeeId): void
    {
        $employee = $this->find($employeeId);
        if ($employee === null) {
            return;
        }

        $statement = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $statement->execute(['id' => $employee['user_id']]);
    }

    // Liczba rezerwacji pracownika — blokuje usunięcie.
    public function countReservations(int $employeeId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM reservations WHERE employee_id = :id'
        );
        $statement->execute(['id' => $employeeId]);

        return (int) $statement->fetchColumn();
    }
}
