<?php
// Historia zmian statusów rezerwacji — kto, kiedy i z jakiego statusu.
class StatusHistory
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Zapisuje zmianę statusu. Wywołujący odpowiada za transakcję — dzięki temu
    // UPDATE rezerwacji i wpis historii są atomowe.
    public function record(int $reservationId, ?string $oldStatus, string $newStatus, ?int $changedBy): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO status_history (reservation_id, old_status, new_status, changed_by)
             VALUES (:reservation_id, :old_status, :new_status, :changed_by)'
        );
        $statement->bindValue(':reservation_id', $reservationId, PDO::PARAM_INT);
        $statement->bindValue(
            ':old_status',
            $oldStatus,
            $oldStatus === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $statement->bindValue(':new_status', $newStatus, PDO::PARAM_STR);
        $statement->bindValue(
            ':changed_by',
            $changedBy,
            $changedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );
        $statement->execute();
    }

    // Historia jednej rezerwacji z nazwiskiem autora zmiany (NULL = system).
    public function forReservation(int $reservationId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT h.id, h.old_status, h.new_status, h.changed_at,
                    CONCAT(u.first_name, ' ', u.last_name) AS changed_by_name
             FROM status_history h
             LEFT JOIN users u ON u.id = h.changed_by
             WHERE h.reservation_id = :id
             ORDER BY h.changed_at DESC, h.id DESC"
        );
        $statement->execute(['id' => $reservationId]);

        return $statement->fetchAll();
    }
}
