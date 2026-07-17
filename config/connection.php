<?php

class DatabaseConnection
{
    private static ?PDO $instance = null;
    private static array $config = [];
    
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/database.php';
            self::$config = $config;
            
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );
            
            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                throw new RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
    
    public static function getConfig(): array
    {
        if (empty(self::$config)) {
            self::$config = require __DIR__ . '/database.php';
        }
        return self::$config;
    }
}