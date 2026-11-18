<?php
// Operacje bazodanowe na usługach.
class Service
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Wszystkie usługi z nazwą kategorii (JOIN) i liczbą rezerwacji.
    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT s.id, s.category_id, s.name, s.description, s.duration_minutes,
                    s.price, s.is_active, s.created_at, c.name AS category_name,
                    (SELECT COUNT(*) FROM reservations r WHERE r.service_id = s.id) AS reservations_count
             FROM services s
             JOIN service_categories c ON c.id = s.category_id
             ORDER BY c.name ASC, s.name ASC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT s.id, s.category_id, s.name, s.description, s.duration_minutes,
                    s.price, s.is_active, s.created_at, c.name AS category_name
             FROM services s
             JOIN service_categories c ON c.id = s.category_id
             WHERE s.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $service = $statement->fetch();

        return $service ?: null;
    }

    // Aktywne usługi dla oferty publicznej; opcjonalnie zawężone
    // do jednej kategorii (filtr ?category=X na stronie oferty).
    public function allActive(?int $categoryId = null): array
    {
        $sql = 'SELECT s.id, s.category_id, s.name, s.description, s.duration_minutes,
                       s.price, c.name AS category_name
                FROM services s
                JOIN service_categories c ON c.id = s.category_id
                WHERE s.is_active = 1';

        $params = [];
        if ($categoryId !== null) {
            $sql .= ' AND s.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $sql .= ' ORDER BY c.name ASC, s.name ASC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    // Aktywna usługa po ID — nieaktywna (choćby istniała w bazie)
    // dla klienta jest jakby jej nie było, więc zwracamy null.
    public function findActive(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT s.id, s.category_id, s.name, s.description, s.duration_minutes,
                    s.price, c.name AS category_name
             FROM services s
             JOIN service_categories c ON c.id = s.category_id
             WHERE s.id = :id AND s.is_active = 1
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $service = $statement->fetch();

        return $service ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO services
                (category_id, name, description, duration_minutes, price, is_active)
             VALUES
                (:category_id, :name, :description, :duration_minutes, :price, :is_active)'
        );
        $statement->execute([
            'category_id'      => $data['category_id'],
            'name'             => $data['name'],
            'description'      => $data['description'],
            'duration_minutes' => $data['duration_minutes'],
            'price'            => $data['price'],
            'is_active'        => $data['is_active'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE services
             SET category_id = :category_id, name = :name, description = :description,
                 duration_minutes = :duration_minutes, price = :price, is_active = :is_active
             WHERE id = :id'
        );
        $statement->execute([
            'category_id'      => $data['category_id'],
            'name'             => $data['name'],
            'description'      => $data['description'],
            'duration_minutes' => $data['duration_minutes'],
            'price'            => $data['price'],
            'is_active'        => $data['is_active'],
            'id'               => $id,
        ]);
    }

    // Przełącza aktywność usługi (1 -> 0, 0 -> 1).
    public function toggleActive(int $id): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE services SET is_active = 1 - is_active WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM services WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    // Liczba rezerwacji — usługi z rezerwacjami nie usuwamy.
    public function countReservations(int $id): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM reservations WHERE service_id = :id'
        );
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn();
    }
}
