<?php

class Database
{
    private string $host;
    private string $dbname;
    private string $username;
    private string $password;

    public function __construct()
    {
        $localConfigPath = __DIR__ . '/database.local.php';
        $localConfig = is_file($localConfigPath) ? require $localConfigPath : [];

        $this->host = getenv('DB_HOST') ?: ($localConfig['host'] ?? 'localhost');
        $this->dbname = getenv('DB_NAME') ?: ($localConfig['dbname'] ?? '');
        $this->username = getenv('DB_USER') ?: ($localConfig['username'] ?? '');
        $this->password = getenv('DB_PASSWORD') ?: ($localConfig['password'] ?? '');
    }

    public function connect(): PDO
    {
        if ($this->dbname === '' || $this->username === '') {
            throw new RuntimeException(
                'Configurez DB_NAME et DB_USER, ou créez config/database.local.php.'
            );
        }

        try {
            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return $pdo;
        } catch (PDOException $exception) {
            error_log('Database connection failed: ' . $exception->getMessage());
            http_response_code(500);
            exit('Erreur de connexion à la base de données. Consultez les journaux du serveur.');
        }
    }
}
