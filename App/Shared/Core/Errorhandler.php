<?php

namespace App\Shared\Core;

class ErrorHandler
{
    private static string $logFile;
    private static bool $debug;
    
    public static function initialize(): void
    {
        self::$logFile = __DIR__ . '/../../../storage/logs/error.log';
        self::$debug = $_ENV['APP_DEBUG'] ?? false;
        
        // Ensure log directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        // Ensure log file exists
        if (!file_exists(self::$logFile)) {
            touch(self::$logFile);
            chmod(self::$logFile, 0666);
        }
        
        // Set error handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleError(int $level, string $message, string $file, int $line): bool
    {
        $errorType = self::getErrorType($level);
        $logMessage = "[{$errorType}] {$message} in {$file} on line {$line}";
        self::writeLog($logMessage);
        
        if (self::$debug) {
            echo "<pre style='color: red;'><strong>Error:</strong> {$message}<br><strong>File:</strong> {$file}<br><strong>Line:</strong> {$line}</pre>";
        }
        
        return true;
    }
    
    public static function handleException(\Throwable $exception): void
    {
        $logMessage = sprintf(
            "[EXCEPTION] %s in %s on line %d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        self::writeLog($logMessage);
        
        if (self::$debug) {
            echo "<pre style='color: red;'><strong>Exception:</strong> " . $exception->getMessage() . 
                 "<br><strong>File:</strong> " . $exception->getFile() . 
                 "<br><strong>Line:</strong> " . $exception->getLine() . 
                 "<br><strong>Trace:</strong><br>" . nl2br($exception->getTraceAsString()) . "</pre>";
        } else {
            http_response_code(500);
            echo "An error occurred. Please check the error log for details.";
        }
    }
    
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $logMessage = sprintf(
                "[FATAL] %s in %s on line %d",
                $error['message'],
                $error['file'],
                $error['line']
            );
            self::writeLog($logMessage);
            
            if (self::$debug) {
                echo "<pre style='color: red;'><strong>Fatal Error:</strong> " . $error['message'] . 
                     "<br><strong>File:</strong> " . $error['file'] . 
                     "<br><strong>Line:</strong> " . $error['line'] . "</pre>";
            }
        }
    }
    
    public static function writeLog(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}" . PHP_EOL;
        error_log($logEntry, 3, self::$logFile);
    }
    
    // Alias for writeLog for backward compatibility
    public static function log(string $message, string $level = 'INFO'): void
    {
        $logMessage = "[{$level}] {$message}";
        self::writeLog($logMessage);
    }
    
    // Handle exception with logging
    public static function exception(\Throwable $exception): void
    {
        $logMessage = sprintf(
            "[EXCEPTION] %s in %s on line %d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        self::writeLog($logMessage);
        
        // Also log to error log for PHP's built-in logging
        error_log($exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine());
    }
    
    private static function getErrorType(int $level): string
    {
        return match ($level) {
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED',
            default => 'UNKNOWN'
        };
    }
}