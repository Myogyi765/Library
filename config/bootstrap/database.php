<?php
use App\Shared\Core\ErrorHandler;

require_once __DIR__ . '/../connection.php';

try {
    $pdo = DatabaseConnection::getConnection();
    ErrorHandler::log('✅ Database connected successfully', 'INFO');
} catch (\Exception $e) {
    ErrorHandler::log('❌ DB connection failed: ' . $e->getMessage(), 'ERROR');
    
    try {
        $config = require __DIR__ . '/../database.php';
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );
        $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        ErrorHandler::log('⚠️ DB connected with fallback settings', 'WARNING');
    } catch (\Exception $e2) {
        ErrorHandler::log('❌ Fallback DB connection also failed: ' . $e2->getMessage(), 'ERROR');
        $pdo = new PDO('sqlite::memory:');
        ErrorHandler::log('⚠️ Using SQLite in-memory database as last resort', 'WARNING');
    }
}