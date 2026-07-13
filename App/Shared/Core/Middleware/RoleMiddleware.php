<?php
namespace App\Shared\Core\Middleware;

use App\User\Infrastructure\Security\UserAuthenticator;

class RoleMiddleware implements MiddlewareInterface
{
    private UserAuthenticator $userAuth;
    private string $role;

    public function __construct(UserAuthenticator $userAuth, string $role)
    {
        $this->userAuth = $userAuth;
        $this->role = $role;
    }

    public function handle(): bool
    {
        if ($this->userAuth->isLoggedIn() && ($_SESSION['user_role'] ?? '') === $this->role) {
            return true;
        }
        http_response_code(403);
        echo '403 Forbidden';
        return false;
    }
}