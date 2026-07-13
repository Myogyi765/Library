<?php

namespace App\Shared\Helpers;

use App\Shared\Core\ErrorHandler;

class Logger
{
    public static function info(string $message): void
    {
        ErrorHandler::log($message, 'INFO');
    }
    
    public static function error(string $message): void
    {
        ErrorHandler::log($message, 'ERROR');
    }
    
    public static function warning(string $message): void
    {
        ErrorHandler::log($message, 'WARNING');
    }
    
    public static function debug(string $message): void
    {
        if ($_ENV['APP_DEBUG'] ?? false) {
            ErrorHandler::log($message, 'DEBUG');
        }
    }
    
    public static function exception(\Throwable $exception): void
    {
        ErrorHandler::exception($exception);
    }
}