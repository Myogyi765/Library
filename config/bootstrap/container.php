<?php

declare(strict_types=1);

use App\Shared\Core\ErrorHandler;
use App\Shared\Core\Authorization\Authorization;

// ================================================================
// 🚀 CONTAINER DEFINITION
// ================================================================

$container = new class {
    private array $services = [];
    private array $singletons = [];

    public function set(string $key, $service): void
    {
        $this->services[$key] = $service;
    }

    public function singleton(string $key, callable $factory): void
    {
        $this->singletons[$key] = $factory;
    }

    public function get(string $key): mixed
    {
        if (isset($this->singletons[$key])) {
            if (!isset($this->services[$key])) {
                $this->services[$key] = ($this->singletons[$key])($this);
            }
            return $this->services[$key];
        }

        if (!isset($this->services[$key])) {
            ErrorHandler::log("❌ Service not found: {$key}", 'ERROR');
            throw new \RuntimeException("Service not found: {$key}");
        }

        $service = $this->services[$key];
        if ($service instanceof \Closure) {
            $resolved = $service($this);
            $this->services[$key] = $resolved;
            return $resolved;
        }

        return $service;
    }

    public function has(string $key): bool
    {
        return isset($this->services[$key]) || isset($this->singletons[$key]);
    }
};

// ================================================================
// 1️⃣ CORE SERVICES
// ================================================================

$container->singleton('db', function () {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'library';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
    $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    return new \PDO($dsn, $user, $pass, $options);
});

// Register Authorization with multiple key aliases
$container->singleton(Authorization::class, function ($c) {
    return new Authorization($c->get('db'));
});
$container->set('authorization', fn($c) => $c->get(Authorization::class));
$container->set('Authorization', fn($c) => $c->get(Authorization::class));

// ✅ ADD DUMMY admin.service TO AVOID MISSING SERVICE ERRORS
$container->singleton('admin.service', function ($c) {
    // This is a dummy service – admin functionality is now handled by User module
    return new class {
        public function __call($name, $args) {
            return null;
        }
    };
});

// ================================================================
// 2️⃣ LOAD MODULES IN CORRECT ORDER
// ================================================================

$moduleFiles = [
    __DIR__ . '/../../config/services/user.php',
    __DIR__ . '/../../config/services/book.php',
    __DIR__ . '/../../config/services/circulation.php',
    __DIR__ . '/../../config/services/payment.php',
    __DIR__ . '/../../config/services/invoice.php',     
    __DIR__ . '/../../config/services/librarian.php',
    __DIR__ . '/../../config/services/admin.php',
    __DIR__ . '/../../config/services/notification.php',
];

foreach ($moduleFiles as $path) {
    if (file_exists($path)) {
        $definitions = require $path;
        if (is_callable($definitions)) {
            $definitions($container);
        }
    }
}

// ================================================================
// ✅ DONE
// ================================================================

ErrorHandler::log('📦 Service Container created (explicit order, no prefixes)', 'INFO');

return $container;