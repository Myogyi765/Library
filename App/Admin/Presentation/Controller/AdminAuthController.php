<?php
namespace App\Admin\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;
use App\Shared\Core\Authorization\Authorization;

class AdminAuthController extends BaseController
{
    private UserAuthenticator $authenticator;
    private Authorization $authorization; 

    public function __construct(UserAuthenticator $authenticator, Authorization $authorization = null)
    {
        $this->authenticator = $authenticator;
        $this->authorization = $authorization;
    }

    public function logout(): void
    {
        $this->authenticator->logout();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

       
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

   
    public function isAdminLoggedIn(): bool
    {
        return $this->authenticator->isLoggedIn() 
            && ($_SESSION['user_role'] ?? '') === 'admin';
    }
}