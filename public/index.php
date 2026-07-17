<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

define('BASE_PATH', dirname(__DIR__));


$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptDir = rtrim(dirname($scriptName), '/');
define('BASE_URL', $protocol . '://' . $host . $scriptDir);


$logDir = BASE_PATH . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('error_log', $logDir . '/error.log');

require_once BASE_PATH . '/vendor/autoload.php';

use App\Shared\Core\ErrorHandler;
ErrorHandler::initialize();

try {
    
    $container = require BASE_PATH . '/config/bootstrap.php';
   
    $GLOBALS['container'] = $container;

    $router = new App\Shared\Core\Router($container);

    require BASE_PATH . '/routes/web.php';

    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

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

    if (strpos($path, '/public') === 0) {
        $path = substr($path, 7) ?: '/';
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
    }

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