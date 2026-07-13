<?php
namespace App\Librarian\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;  
use App\Shared\Base\BaseController;

class LibrarianAuthController extends BaseController
{
    private UserAuthenticator $authenticator;  

    public function __construct(UserAuthenticator $authenticator) 
    {
        $this->authenticator = $authenticator;
    }

    public function login(): void
    {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function logout(): void
    {
        $this->authenticator->logout();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}