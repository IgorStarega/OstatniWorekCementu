<?php
// Relacja N:M między pracownikami a usługami (tabela employee_services).
class EmployeeService
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ID usług przypisanych do pracownika (jedno zapytanie, bez SELECT-a per checkbox).
    public function serviceIdsForEmployee(int $employeeId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT service_id FROM employee_services WHERE employee_id = :id ORDER BY service_id'
        );
        $statement->execute(['id' => $employeeId]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    // Podgląd odwrotny — użyty w kolejnym etapie przy rezerwacjach.
    public function employeeIdsForService(int $serviceId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT employee_id FROM employee_services WHERE service_id = :id ORDER BY employee_id'
        );
        $statement->execute(['id' => $serviceId]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    // Zastępuje komplet przypisań pracownika nowym zestawem w jednej transakcji.
    public function syncForEmployee(int $employeeId, array $serviceIds): void
    {
        $serviceIds = array_values(array_unique(array_map('intval', $serviceIds)));

        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM employee_services WHERE employee_id = :id'
            );
            $statement->execute(['id' => $employeeId]);

            $statement = $this->pdo->prepare(
                'INSERT INTO employee_services (employee_id, service_id)
                 VALUES (:employee_id, :service_id)'
            );

            foreach ($serviceIds as $serviceId) {
                if ($serviceId < 1) {
                    continue;
                }

                $statement->execute([
                    'employee_id' => $employeeId,
                    'service_id'  => $serviceId,
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
