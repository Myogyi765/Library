<?php
namespace App\Shared\Core\Middleware;

use App\User\Infrastructure\Security\UserAuthenticator;

class AuthMiddleware implements MiddlewareInterface
{
    private UserAuthenticator $userAuth;

    public function __construct(UserAuthenticator $userAuth)
    {
        $this->userAuth = $userAuth;
    }

    public function handle(): bool
    {
        if ($this->userAuth->isLoggedIn()) {
            return true;
        }
        header('Location: ' . BASE_URL . '/login');
        return false;
    }
}
