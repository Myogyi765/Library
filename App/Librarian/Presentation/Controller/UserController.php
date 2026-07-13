<?php
namespace App\Librarian\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;

class UserController extends BaseController
{
    private UserAuthenticator $authenticator;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->authenticator = $container->get('user.authenticator');
    }

    public function index(): void
    {
        if (!$this->authenticator->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Fetch users data (replace with actual service)
        $users = [];

        $pageTitle = 'Manage Users';
        $viewData = ['users' => $users];
        $content = BASE_PATH . '/view/librarian/users/index.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }

    public function create(): void
    {
        if (!$this->authenticator->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $pageTitle = 'Add User';
        $content = BASE_PATH . '/view/librarian/users/create.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }

    // Add store(), edit(), update(), delete() methods as needed
}