<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

define('BASE_PATH', dirname(__DIR__));

// ---------- BASE_URL ----------
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptDir = rtrim(dirname($scriptName), '/');
define('BASE_URL', $protocol . '://' . $host . $scriptDir);

// ---------- Log directory ----------
$logDir = BASE_PATH . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('error_log', $logDir . '/error.log');

// ---------- Autoloader ----------
require_once BASE_PATH . '/vendor/autoload.php';

use App\Shared\Core\ErrorHandler;
ErrorHandler::initialize();

try {
    // ---------- Container ----------
    $container = require BASE_PATH . '/config/bootstrap.php';

    // ---------- Router ----------
    $router = new App\Shared\Core\Router($container);

    // ---------- Load routes (fluent style) ----------
    require BASE_PATH . '/routes/web.php';

    // ---------- Clean request URI ----------
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

    // Remove base directory if installed in a subfolder
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $baseDir = dirname(dirname($scriptName));
    if ($baseDir !== '/' && $baseDir !== '.') {
        if (strpos($path, $baseDir) === 0) {
            $path = substr($path, strlen($baseDir));
            if ($path === '' || $path === false) {
                $path = '/';
            }
            if ($path[0] !== '/') {
                $path = '/' . $path;
            }
        }
    }

    // Remove '/public' prefix if present
    if (strpos($path, '/public') === 0) {
        $path = substr($path, 7) ?: '/';
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
    }

    // ---------- Dispatch ----------
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $router->dispatch($path, $method);

} catch (\Throwable $e) {
    ErrorHandler::exception($e);

    if (ini_get('display_errors')) {
        echo '<pre>' . $e . '</pre>';
    } else {
        http_response_code(500);
        echo 'An error occurred. Please check the error log.';
    }
}