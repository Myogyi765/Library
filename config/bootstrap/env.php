<?php
use App\Shared\Core\ErrorHandler;

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    ErrorHandler::log('✅ Environment variables loaded', 'INFO');
} catch (\Exception $e) {
    ErrorHandler::log('⚠️ Failed to load .env: ' . $e->getMessage(), 'WARNING');
}