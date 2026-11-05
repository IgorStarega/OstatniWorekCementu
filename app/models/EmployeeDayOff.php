<?php
// Dni wolne i urlopy pracownika — jeden wpis na pracownika i dzień.
class EmployeeDayOff
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function forEmployee(int $employeeId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, employee_id, date, reason
             FROM employee_days_off
             WHERE employee_id = :employee_id
             ORDER BY date ASC'
        );
        $statement->execute(['employee_id' => $employeeId]);

        return $statement->fetchAll();
    }

    // Najbliższe dni wolne (od dziś), używane m.in. w podglądzie pracownika.
    public function upcoming(int $employeeId, int $limit = 10): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, employee_id, date, reason
             FROM employee_days_off
             WHERE employee_id = :employee_id AND date >= CURDATE()
             ORDER BY date ASC
             LIMIT :limit'
        );
        $statement->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    // Duplikat (employee_id, date) zgłasza PDOException o kodzie 23000.
    public function add(int $employeeId, string $date, string $reason): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO employee_days_off (employee_id, date, reason)
             VALUES (:employee_id, :date, :reason)'
        );
        $statement->execute([
            'employee_id' => $employeeId,
            'date'        => $date,
            'reason'      => $reason,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM employee_days_off WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
