<?php
// Przerwy w godzinach pracy pracownika (np. obiad). Osobna tabela, bo
// employee_availability ma UNIQUE (employee_id, day_of_week) i nie pozwala
// dopisać drugiego okna w tym samym dniu.
class EmployeeBreak
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Wszystkie przerwy pracownika, uporządkowane po dniu i godzinie.
    public function forEmployee(int $employeeId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, employee_id, day_of_week, start_time, end_time, label
             FROM employee_breaks
             WHERE employee_id = :employee_id
             ORDER BY day_of_week ASC, start_time ASC, id ASC'
        );
        $statement->execute(['employee_id' => $employeeId]);

        return $statement->fetchAll();
    }

    // Przerwy pracownika w konkretnym dniu tygodnia (1 = poniedziałek).
    public function forEmployeeDay(int $employeeId, int $dayOfWeek): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, employee_id, day_of_week, start_time, end_time, label
             FROM employee_breaks
             WHERE employee_id = :employee_id AND day_of_week = :day_of_week
             ORDER BY start_time ASC, id ASC'
        );
        $statement->execute([
            'employee_id' => $employeeId,
            'day_of_week' => $dayOfWeek,
        ]);

        return $statement->fetchAll();
    }

    // Zapis nowej przerwy. Walidacja należy do kontrolera — model nie może
    // zakładać, że przyszły poprawnie sformatowane godziny i dzień.
    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO employee_breaks
                (employee_id, day_of_week, start_time, end_time, label)
             VALUES
                (:employee_id, :day_of_week, :start_time, :end_time, :label)'
        );
        $statement->execute([
            'employee_id' => $data['employee_id'],
            'day_of_week' => $data['day_of_week'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'label'       => $data['label'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM employee_breaks WHERE id = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
