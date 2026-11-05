<?php
// Liczniki pulpitu administratora — jedno zapytanie z podzapytaniami.
class Stats
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function counters(): array
    {
        $row = $this->pdo->query(
            "SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'klient' AND is_active = 1)   AS clients,
                (SELECT COUNT(*) FROM employees WHERE is_active = 1)                    AS employees,
                (SELECT COUNT(*) FROM services WHERE is_active = 1)                     AS services,
                (SELECT COUNT(*) FROM reservations)                                     AS reservations_total,
                (SELECT COUNT(*) FROM reservations WHERE reservation_date = CURDATE())   AS reservations_today"
        )->fetch();

        if (!is_array($row)) {
            return [
                'clients'            => 0,
                'employees'          => 0,
                'services'           => 0,
                'reservations_total' => 0,
                'reservations_today' => 0,
            ];
        }

        // PDO z MySQL zwraca liczby jako stringi — ujednolicamy typy.
        return [
            'clients'            => (int) $row['clients'],
            'employees'          => (int) $row['employees'],
            'services'           => (int) $row['services'],
            'reservations_total' => (int) $row['reservations_total'],
            'reservations_today' => (int) $row['reservations_today'],
        ];
    }

    // Rezerwacje w podziale na status — zawsze cztery pozycje, także dla zera.
    public function byStatus(): array
    {
        $statement = $this->pdo->query(
            'SELECT status, COUNT(*) AS total
             FROM reservations
             GROUP BY status'
        );

        $counts = [];
        foreach ($statement->fetchAll() as $row) {
            $counts[(string) $row['status']] = (int) $row['total'];
        }

        $result = [];
        foreach (['oczekująca', 'potwierdzona', 'zrealizowana', 'anulowana'] as $status) {
            $result[] = [
                'status' => $status,
                'total'  => $counts[$status] ?? 0,
            ];
        }

        return $result;
    }

    // Przychód zrealizowanych rezerwacji (SUM final_price).
    public function revenueCompleted(): float
    {
        return (float) $this->pdo->query(
            "SELECT COALESCE(SUM(final_price), 0)
             FROM reservations
             WHERE status = 'zrealizowana'"
        )->fetchColumn();
    }

    // TOP najczęściej rezerwowanych usług.
    public function topServices(int $limit = 5): array
    {
        $statement = $this->pdo->prepare(
            'SELECT s.id, s.name, COUNT(r.id) AS total
             FROM services s
             JOIN reservations r ON r.service_id = s.id
             GROUP BY s.id, s.name
             ORDER BY total DESC, s.name ASC
             LIMIT :limit'
        );
        $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    // Obłożenie pracowników: liczba rezerwacji per pracownik (TOP 5).
    public function workload(int $limit = 5): array
    {
        $statement = $this->pdo->prepare(
            "SELECT e.id, CONCAT(u.first_name, ' ', u.last_name) AS employee_name,
                    COUNT(r.id) AS total
             FROM employees e
             JOIN users u ON u.id = e.user_id
             LEFT JOIN reservations r ON r.employee_id = e.id
             GROUP BY e.id, u.first_name, u.last_name
             ORDER BY total DESC, employee_name ASC
             LIMIT :limit"
        );
        $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
