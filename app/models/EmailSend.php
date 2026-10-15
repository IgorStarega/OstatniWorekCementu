<?php
// Dzienniki wysyłek i limity (cooldown + maksymalna liczba na godzinę).
// Liczniki trzymamy w bazie, nie w sesji — sesję łatwo wyczyścić, limit ma chronić serwer.
class EmailSend
{
    // Minimalny odstęp między wysyłkami na ten sam adres i cel (sekundy).
    private const COOLDOWN_SECONDS = 60;
    // Maksymalna liczba wysyłek na adres i cel w ciągu godziny.
    private const MAX_PER_HOUR = 3;

    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Czy wolno teraz wysłać wiadomość o danym celu na dany adres?
    public function isAllowed(string $email, string $purpose): bool
    {
        // Ta sama wartość liczby wstawiona na sztywno (stała, nie dane użytkownika).
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM email_sends
             WHERE email = :email AND purpose = :purpose
               AND created_at > DATE_SUB(NOW(), INTERVAL ' . self::COOLDOWN_SECONDS . ' SECOND)'
        );
        $statement->execute(['email' => $email, 'purpose' => $purpose]);
        if ((int) $statement->fetchColumn() > 0) {
            return false;
        }

        $statement = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM email_sends
             WHERE email = :email AND purpose = :purpose
               AND created_at > DATE_SUB(NOW(), INTERVAL 3600 SECOND)'
        );
        $statement->execute(['email' => $email, 'purpose' => $purpose]);

        return (int) $statement->fetchColumn() < self::MAX_PER_HOUR;
    }

    // Zapisuje próbę wysyłki, żeby kolejne żądania respektowały limity.
    // Przy okazji sprząta stare wpisy tego adresu i celu (retencja 24 h) — DELETE
    // korzysta z indeksu email_purpose_created i nie rusza innych wierszy.
    public function record(string $email, string $purpose): void
    {
        $delete = $this->pdo->prepare(
            'DELETE FROM email_sends
             WHERE email = :email AND purpose = :purpose
               AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)'
        );
        $delete->execute(['email' => $email, 'purpose' => $purpose]);

        $statement = $this->pdo->prepare(
            'INSERT INTO email_sends (email, purpose) VALUES (:email, :purpose)'
        );
        $statement->execute(['email' => $email, 'purpose' => $purpose]);
    }
}
