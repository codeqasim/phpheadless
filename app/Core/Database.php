<?php
// app/Core/Database.php

namespace App\Core;

use Medoo\Medoo;
use PDO;
use Exception;

class Database
{
    private $connection;
    private static $instance = null;

    public function __construct()
    {
        $this->connect();
        self::$instance = $this;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect()
    {
        $config = $this->getDatabaseConfig();
        
        try {
            $this->connection = new Medoo($config);
            // Test connection
            $this->connection->query("SELECT 1");
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function getDatabaseConfig()
    {
        return [
            'database_type' => 'mysql',
            'database_name' => $_ENV['DB_NAME'] ?? '',
            'server' => $_ENV['DB_HOST'] ?? 'localhost',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4',
            'port' => (int)($_ENV['DB_PORT'] ?? 3306),
            'option' => [
                PDO::ATTR_CASE => PDO::CASE_NATURAL,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        ];
    }

    // Medoo wrapper methods
    public function select($table, $columns = "*", $where = null)
    {
        return $this->connection->select($table, $columns, $where);
    }

    public function get($table, $columns = "*", $where = null)
    {
        return $this->connection->get($table, $columns, $where);
    }

    public function insert($table, $data)
    {
        $result = $this->connection->insert($table, $data);
        return $result->rowCount() > 0 ? $this->connection->id() : false;
    }

    public function update($table, $data, $where)
    {
        $result = $this->connection->update($table, $data, $where);
        return $result->rowCount();
    }

    public function delete($table, $where)
    {
        $result = $this->connection->delete($table, $where);
        return $result->rowCount();
    }

    public function has($table, $where)
    {
        return $this->connection->has($table, $where);
    }

    public function count($table, $where = null)
    {
        return $this->connection->count($table, $where);
    }

    public function query($sql, $params = [])
    {
        return $this->connection->query($sql, $params);
    }

    public function lastInsertId()
    {
        return $this->connection->id();
    }

    public function beginTransaction()
    {
        return $this->connection->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->connection->pdo->commit();
    }

    public function rollback()
    {
        return $this->connection->pdo->rollback();
    }

    // Schema methods for installation
    public function createTable($tableName, $schema)
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` ({$schema}) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return $this->query($sql);
    }

    public function dropTable($tableName)
    {
        $sql = "DROP TABLE IF EXISTS `{$tableName}`";
        return $this->query($sql);
    }

    public function tableExists($tableName)
    {
        try {
            $result = $this->query("SHOW TABLES LIKE '{$tableName}'");
            return $result !== false && count($result) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function runMigrations()
    {
        $migrationsPath = __DIR__ . '/../../database/migrations/';
        if (!is_dir($migrationsPath)) {
            return;
        }

        // Create migrations table if it doesn't exist
        $this->createTable('migrations', '
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ');

        // Get executed migrations
        $executed = [];
        try {
            $result = $this->select('migrations', 'migration');
            $executed = array_column($result, 'migration');
        } catch (Exception $e) {
            // Table might not exist yet
        }

        // Run pending migrations
        $files = glob($migrationsPath . '*.php');
        sort($files);

        foreach ($files as $file) {
            $migrationName = basename($file, '.php');
            
            if (!in_array($migrationName, $executed)) {
                echo "Running migration: {$migrationName}\n";
                
                try {
                    require_once $file;
                    $this->insert('migrations', ['migration' => $migrationName]);
                    echo "Migration {$migrationName} completed successfully\n";
                } catch (Exception $e) {
                    echo "Migration {$migrationName} failed: " . $e->getMessage() . "\n";
                    throw $e;
                }
            }
        }
    }

    // Raw Medoo instance access
    public function getMedoo()
    {
        return $this->connection;
    }

    // Connection info for installer
    public static function testConnection($host, $username, $password, $database = null, $port = 3306)
    {
        try {
            $dsn = "mysql:host={$host};port={$port}";
            if ($database) {
                $dsn .= ";dbname={$database}";
            }
            
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            return ['success' => true, 'message' => 'Connection successful'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function createDatabase($host, $username, $password, $database, $port = 3306)
    {
        try {
            $dsn = "mysql:host={$host};port={$port}";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $sql = "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $pdo->exec($sql);
            
            return ['success' => true, 'message' => 'Database created successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>