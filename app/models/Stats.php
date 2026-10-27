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
}
