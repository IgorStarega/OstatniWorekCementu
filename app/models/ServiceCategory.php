<?php
// Operacje bazodanowe na kategoriach usług.
class ServiceCategory
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Wszystkie kategorie razem z liczbą przypisanych usług.
    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT c.id, c.name, c.description, COUNT(s.id) AS services_count
             FROM service_categories c
             LEFT JOIN services s ON s.category_id = c.id
             GROUP BY c.id, c.name, c.description
             ORDER BY c.name ASC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, description FROM service_categories WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $category = $statement->fetch();

        return $category ?: null;
    }

    public function create(string $name, string $description): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO service_categories (name, description) VALUES (:name, :description)'
        );
        $statement->execute([
            'name'        => $name,
            'description' => $description,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $name, string $description): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE service_categories SET name = :name, description = :description WHERE id = :id'
        );
        $statement->execute([
            'name'        => $name,
            'description' => $description,
            'id'          => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM service_categories WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    // Liczba usług w kategorii — sprawdzana przed usunięciem (nie polegamy na FK).
    public function countServices(int $id): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM services WHERE category_id = :id'
        );
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn();
    }
}
