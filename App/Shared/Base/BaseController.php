<?php

namespace App\Shared\Base;

use App\Notification\Application\Service\NotificationService;

class BaseController
{
    protected $container;
    protected string $basePath = '';
    protected string $viewPath = '';
    protected ?string $viewBasePath = null;      
    private bool $initialized = false;
    
    public function __construct($container = null)
    {
        $this->initialize($container);
    }

    protected function initialize($container = null): void
    {
        if ($this->initialized) {
            return;
        }

        if ($container) {
            $this->container = $container;
        } else {
            global $container;
            $this->container = $container ?? null;
        }

        if (defined('BASE_PATH')) {
            $this->basePath = BASE_PATH;
        } else {
            $this->basePath = dirname(__DIR__, 4);
        }

        $this->viewPath = $this->basePath . '/view/';
        $this->initialized = true;
    }

    protected function setViewBasePath(string $path): void
    {
        $this->viewBasePath = rtrim($path, '/') . '/';
    }
    
    protected function view(string $view, array $data = []): void
    {
        $this->initialize();
        extract($data);

        $view = ltrim($view, '/');
        $possiblePaths = [];

        if ($this->viewBasePath !== null) {
            $possiblePaths[] = $this->viewBasePath . $view . '.php';
        }

        $possiblePaths[] = $this->basePath . '/view/' . $view . '.php';
        $possiblePaths[] = $this->basePath . '/view/layout/' . $view . '.php';
        $possiblePaths[] = $this->basePath . '/view/pages/' . $view . '.php';

        $possiblePaths[] = __DIR__ . '/../../../view/' . $view . '.php';
        $possiblePaths[] = __DIR__ . '/../../view/' . $view . '.php';
        $possiblePaths[] = __DIR__ . '/../view/' . $view . '.php';
        $possiblePaths[] = dirname(__DIR__, 4) . '/view/' . $view . '.php';
        $possiblePaths[] = dirname(__DIR__, 3) . '/view/' . $view . '.php';
        
        foreach ($possiblePaths as $viewPath) {
            if (file_exists($viewPath)) {
                require_once $viewPath;
                return;
            }
        }
        
        throw new \RuntimeException("View not found: {$view}");
    }
    
    protected function buildRedirectUrl(string $url): string
    {
        $this->initialize();

        if ($url === '') {
            return defined('BASE_URL') ? BASE_URL : '/';
        }

        if (preg_match('#^https?://#i', $url) === 1 || str_starts_with($url, '//')) {
            return $url;
        }

        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        if ($baseUrl === '') {
            return $url;
        }

        if ($url[0] === '/') {
            return $baseUrl . $url;
        }

        return $baseUrl . '/' . $url;
    }

    protected function redirect(string $url): void
    {
        $redirectUrl = $this->buildRedirectUrl($url);
        header('Location: ' . $redirectUrl);
        exit;
    }

    protected function createNotification(
        ?int $userId,
        string $role,
        string $type,
        string $title,
        string $message,
        ?string $link = null
    ): void {
        if ($userId !== null && $userId <= 0) {
            return;
        }

        $service = null;
        $notificationServiceClass = NotificationService::class;

        if ($this->container && $this->container->has($notificationServiceClass)) {
            $service = $this->container->get($notificationServiceClass);
        } elseif (isset($GLOBALS['container']) && $GLOBALS['container']->has($notificationServiceClass)) {
            $service = $GLOBALS['container']->get($notificationServiceClass);
        }

        if ($service instanceof NotificationService) {
            try {
                $service->createNotification($userId, $role, $type, $title, $message, $link);
            } catch (\Throwable $e) {
                error_log('❌ Notification creation failed: ' . $e->getMessage());
            }
        }
    }
    
    protected function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    
    protected function ensureAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $role = $_SESSION['user_role'] ?? null;

        if ($role !== 'admin') {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>Admin access required.</p>';
            exit;
        }
    }

    
    protected function ensureLibrarian(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $role = $_SESSION['user_role'] ?? null;

        if ($role !== 'librarian' && $role !== 'admin') {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>Librarian access required.</p>';
            exit;
        }
    }

    
    protected function ensureUser(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $role = $_SESSION['user_role'] ?? null;

        if ($role !== 'user' && $role !== 'librarian' && $role !== 'admin') {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>User access required.</p>';
            exit;
        }
    }

    
    protected function ensureAuthenticated(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
            header('Location: ' . $this->buildRedirectUrl('/login'));
            exit;
        }
    }
}
