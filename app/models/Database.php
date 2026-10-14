<?php
// Tworzy połączenie PDO na żądanie kontrolera.
class Database
{
    // Jedno połączenie PDO na żądanie — kolejne wywołania zwracają ten sam obiekt.
    private static ?PDO $connection = null;

    public static function connect(array $settings): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $settings['host'],
            $settings['port'],
            $settings['name'],
            $settings['charset']
        );

        self::$connection = new PDO($dsn, $settings['user'], $settings['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }
}
