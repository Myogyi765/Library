<?php


use App\Shared\Core\ErrorHandler;

require_once __DIR__ . '/../App/Shared/Core/ErrorHandler.php';
ErrorHandler::initialize();

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    ErrorHandler::log('Autoloader not found. Please run composer install', 'ERROR');
    die('Autoloader not found. Please run <code>composer install</code>');
}

require_once __DIR__ . '/bootstrap/env.php';

require_once __DIR__ . '/bootstrap/session.php';

require_once __DIR__ . '/bootstrap/container.php';

require_once __DIR__ . '/middleware.php';

require_once __DIR__ . '/bootstrap/verify.php';

$GLOBALS['container'] = $container;

ErrorHandler::log('✅ Bootstrap completed successfully', 'INFO');

return $container;