<?php
// Log operacji administratora — krótki ślad bez danych wrażliwych.
class AdminLog
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Zapisuje udaną operację. details nie może zawierać haseł ani tokenów.
    public function record(?int $userId, string $action, string $entity, ?int $entityId, string $details = ''): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_log (user_id, action, entity, entity_id, details)
             VALUES (:user_id, :action, :entity, :entity_id, :details)'
        );
        $statement->bindValue(
            ':user_id',
            $userId,
            $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );
        $statement->bindValue(':action', $action, PDO::PARAM_STR);
        $statement->bindValue(':entity', $entity, PDO::PARAM_STR);
        $statement->bindValue(
            ':entity_id',
            $entityId,
            $entityId === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );
        $statement->bindValue(':details', $details, PDO::PARAM_STR);
        $statement->execute();
    }

    // Ostatnie wpisy z nazwiskiem autora (NULL = konto usunięte/system).
    public function recent(int $limit = 100): array
    {
        $statement = $this->pdo->prepare(
            "SELECT l.id, l.user_id, l.action, l.entity, l.entity_id, l.details, l.created_at,
                    CONCAT(u.first_name, ' ', u.last_name) AS user_name
             FROM admin_log l
             LEFT JOIN users u ON u.id = l.user_id
             ORDER BY l.created_at DESC, l.id DESC
             LIMIT :limit"
        );
        $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
