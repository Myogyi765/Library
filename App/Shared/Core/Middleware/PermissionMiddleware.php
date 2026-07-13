<?php
namespace App\Shared\Core\Middleware;

use App\Shared\Core\Authorization\Authorization;
use App\User\Infrastructure\Security\UserAuthenticator;

class PermissionMiddleware implements MiddlewareInterface
{
    private Authorization $authorization;
    private UserAuthenticator $authenticator;
    private string $permission;

    public function __construct(Authorization $authorization, UserAuthenticator $authenticator, string $permission)
    {
        $this->authorization = $authorization;
        $this->authenticator = $authenticator;
        $this->permission = $permission;
    }

    public function handle(): bool
    {
        // 1. Check if user is logged in
        if (!$this->authenticator->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // 2. Refresh permissions from database to get latest changes (fixes the user not getting updated permissions)
        $user = $this->authenticator->getCurrentUser();
        if ($user && isset($_SESSION['user_id'])) {
            $this->authorization->loadUserPermissions($_SESSION['user_id']);
        }

        // 3. Check specific permission
        if (!$this->authorization->hasPermission($this->permission)) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission: ' . htmlspecialchars($this->permission) . '</p>';
            exit;
        }
        return true;
    }
}