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

// 4️⃣ Load Database Connection
require_once __DIR__ . '/bootstrap/database.php';

// 5️⃣ Start Session
require_once __DIR__ . '/bootstrap/session.php';

// 6️⃣ Create Service Container
require_once __DIR__ . '/bootstrap/container.php';

// 7️⃣ Register Services
require_once __DIR__ . '/services/user.php';
require_once __DIR__ . '/services/admin.php';
require_once __DIR__ . '/services/book.php';
require_once __DIR__ . '/services/loan.php';
require_once __DIR__ . '/services/payment.php';
require_once __DIR__ . '/services/notification.php';
require_once __DIR__ . '/services/librarian.php';

// 8️⃣ Register Controllers
require_once __DIR__ . '/controllers/user.php';
require_once __DIR__ . '/controllers/admin.php';
require_once __DIR__ . '/controllers/librarian.php';
require_once __DIR__ . '/controllers/payment.php';
require_once __DIR__ . '/controllers/book.php';

// 9️⃣ Register Middleware
require_once __DIR__ . '/middleware.php';

// 🔟 Verify Critical Services
require_once __DIR__ . '/bootstrap/verify.php';

// 1️⃣1️⃣ Make Container Globally Accessible
$GLOBALS['container'] = $container;

ErrorHandler::log('✅ Bootstrap completed successfully', 'INFO');

return $container;