<?php

// ================================================================
// 🚀 Bootstrap – Main Entry Point
// ================================================================

use App\Shared\Core\ErrorHandler;

// 1️⃣ Initialize Error Handler
require_once __DIR__ . '/../App/Shared/Core/ErrorHandler.php';
ErrorHandler::initialize();

// 2️⃣ Load Autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    ErrorHandler::log('Autoloader not found. Please run composer install', 'ERROR');
    die('Autoloader not found. Please run <code>composer install</code>');
}

// 3️⃣ Load Environment Variables
require_once __DIR__ . '/bootstrap/env.php';

// 4️⃣ Start Session (Database is now bootstrapped inside container.php via 'db' singleton)
require_once __DIR__ . '/bootstrap/session.php';

// 5️⃣ Create Service Container (ALL repositories, services, handlers & controllers are defined here)
require_once __DIR__ . '/bootstrap/container.php';

// 6️⃣ Register Middleware
require_once __DIR__ . '/middleware.php';

// 7️⃣ Verify Critical Services
require_once __DIR__ . '/bootstrap/verify.php';

// 8️⃣ Make Container Globally Accessible (for legacy BaseController)
$GLOBALS['container'] = $container;

ErrorHandler::log('✅ Bootstrap completed successfully', 'INFO');

return $container;