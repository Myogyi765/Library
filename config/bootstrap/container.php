<?php

use App\Shared\Core\ErrorHandler;
use App\Shared\Core\Authorization\Authorization;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Payment\Infrastructure\Repository\PaymentRepository;
use App\Payment\Infrastructure\Mapper\PaymentMapper;

use App\Admin\Infrastructure\Persistence\SettingRepository;
use App\Admin\Application\Service\SettingsService;
use App\Book\Application\UseCase\GetBook;

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

$container->singleton(Authorization::class, function($c) {
    return new Authorization($c->get('db'));
});
$container->set('Authorization', fn($c) => $c->get(Authorization::class));
$container->set('authorization', fn($c) => $c->get(Authorization::class));

$container->singleton(SettingRepository::class, function($c) {
    return new SettingRepository($c->get('db'));
});

$container->singleton(SettingsService::class, function($c) {
    return new SettingsService($c->get(SettingRepository::class));
});

$container->set('admin.settings.service', fn($c) => $c->get(SettingsService::class));

$container->singleton(PaymentMapper::class, fn($c) => new PaymentMapper());
$container->singleton(PaymentRepositoryInterface::class, function($c) {
    return new PaymentRepository($c->get('db'), $c->get(PaymentMapper::class));
});
$container->set('payment.repository', fn($c) => $c->get(PaymentRepositoryInterface::class));

use App\Book\Infrastructure\Persistence\BookRepository;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Application\UseCase\GetBooks;
use App\Admin\Presentation\Controller\AdminBookController;

$container->singleton(BookRepository::class, function($c) {
    return new BookRepository($c->get('db'));
});

if (!$container->has(BookRepositoryInterface::class)) {
    $container->set(BookRepositoryInterface::class, function($c) {
        return $c->get(BookRepository::class);
    });
}

$container->singleton(GetBooks::class, function($c) {
    return new GetBooks(
        $c->get(BookRepositoryInterface::class)
    );
});

$container->singleton(AdminBookController::class, function($c) {
    return new AdminBookController(
        $c->get(GetBooks::class),
        $c->get(GetBook::class)   
    );
});

$container->singleton(GetBook::class, function($c) {
    return new GetBook($c->get(BookRepositoryInterface::class));
});

require __DIR__ . '/../services/user.php';
require __DIR__ . '/../services/admin.php';
require __DIR__ . '/../services/book.php';
require __DIR__ . '/../services/loan.php';
require __DIR__ . '/../services/payment.php';
require __DIR__ . '/../services/notification.php';
require __DIR__ . '/../services/librarian.php';
require __DIR__ . '/../services/invoice.php'; 

ErrorHandler::log('📦 Service container created', 'INFO');
ErrorHandler::log('✅ Database service (db) registered', 'DEBUG');
ErrorHandler::log('✅ Authorization service registered with aliases', 'DEBUG');
ErrorHandler::log('✅ Payment Repository registered', 'DEBUG');
ErrorHandler::log('✅ Admin Settings Service & Repository registered', 'DEBUG');
