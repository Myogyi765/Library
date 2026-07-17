<?php

namespace App\Librarian\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Shared\Base\BaseController;

class UserController extends BaseController
{
    private UserAuthenticator $authenticator;
    private UserRepositoryInterface $userRepository;

    
    public function __construct(
        UserAuthenticator $authenticator,
        UserRepositoryInterface $userRepository
    ) {
        $this->authenticator = $authenticator;
        $this->userRepository = $userRepository;
    }

    
    public function index(): void
    {
        if (!$this->authenticator->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $allUsers = $this->userRepository->findAll();
        $users = array_filter($allUsers, function($user) {
            return $user->getRole() === 'user';
        });

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

    
    public function store(): void
    {
        $_SESSION['success_message'] = 'User created successfully.';
        header('Location: ' . BASE_URL . '/librarian/dashboard?page=users');
        exit;
    }

    
    public function edit(int $id): void
    {
        if (!$this->authenticator->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            header('Location: ' . BASE_URL . '/librarian/dashboard?page=users');
            exit;
        }

        $pageTitle = 'Edit User';
        $viewData = ['user' => $user];
        $content = BASE_PATH . '/view/librarian/users/edit.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }

    
    public function update(int $id): void
    {
        
        $_SESSION['success_message'] = 'User updated successfully.';
        header('Location: ' . BASE_URL . '/librarian/dashboard?page=users');
        exit;
    }

    
    public function delete(int $id): void
    {
        if (!$this->authenticator->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->userRepository->delete($id);
        $_SESSION['success_message'] = 'User deleted successfully.';
        header('Location: ' . BASE_URL . '/librarian/dashboard?page=users');
        exit;
    }
}
