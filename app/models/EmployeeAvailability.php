<?php
// Tygodniowa dostępność pracownika (godziny pracy w poszczególnych dniach).
// day_of_week: 1 = poniedziałek, 7 = niedziela (konwencja projektu).
class EmployeeAvailability
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Okna pracownika kluczowane po day_of_week (1..7) — wygodne dla widoku.
    public function forEmployee(int $employeeId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT day_of_week, start_time, end_time
             FROM employee_availability
             WHERE employee_id = :employee_id
             ORDER BY day_of_week ASC'
        );
        $statement->execute(['employee_id' => $employeeId]);

        $availability = [];
        foreach ($statement->fetchAll() as $row) {
            $availability[(int) $row['day_of_week']] = $row;
        }

        return $availability;
    }

    // Wstawia albo aktualizuje okno. UNIQUE (employee_id, day_of_week)
    // gwarantuje jeden wiersz na dzień.
    public function upsert(int $employeeId, int $dayOfWeek, string $startTime, string $endTime): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO employee_availability (employee_id, day_of_week, start_time, end_time)
             VALUES (:employee_id, :day_of_week, :start_time, :end_time)
             ON DUPLICATE KEY UPDATE
                 start_time = VALUES(start_time),
                 end_time = VALUES(end_time)'
        );
        $statement->execute([
            'employee_id' => $employeeId,
            'day_of_week' => $dayOfWeek,
            'start_time'  => $startTime,
            'end_time'    => $endTime,
        ]);
    }

    public function delete(int $employeeId, int $dayOfWeek): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM employee_availability
             WHERE employee_id = :employee_id AND day_of_week = :day_of_week'
        );
        $statement->execute([
            'employee_id' => $employeeId,
            'day_of_week' => $dayOfWeek,
        ]);
    }
}
