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

        $page = (int) ($_GET['page_num'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 10;

        $search = trim($_GET['search'] ?? '');

        if (!empty($search)) {
            $totalUsers = $this->userRepository->countByRoleWithSearch('user', $search);
        } else {
            $totalUsers = $this->userRepository->countByRole('user');
        }
        $totalPages = (int) ceil($totalUsers / $perPage);
        
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        if (!empty($search)) {
            $users = $this->userRepository->findByRoleWithSearchPaginated('user', $search, $offset, $perPage);
        } else {
            $users = $this->userRepository->findByRolePaginated('user', $offset, $perPage);
        }

        $page = 'users';
        $pageTitle = 'Manage Users';

        $viewData = [
            'users'        => $users,
            'currentPage'  => (int) $page,
            'totalPages'   => (int) $totalPages,
            'totalUsers'   => (int) $totalUsers,
            'perPage'      => (int) $perPage,
            'search'       => $search,
        ];

        $content = BASE_PATH . '/view/librarian/users/index.php';
        extract($viewData);
        include BASE_PATH . '/view/librarian-dashboard.php';
    }


    public function create(): void
    {
        if (!$this->authenticator->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $page = 'users';
        $pageTitle = 'Add User';
        $content = BASE_PATH . '/view/librarian/users/create.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }


    public function store(): void
    {
        $_SESSION['success_message'] = 'User created successfully.';
        header('Location: ' . BASE_URL . '/librarian/users');
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
            header('Location: ' . BASE_URL . '/librarian/users');
            exit;
        }

        $page = 'users';
        $pageTitle = 'Edit User';
        $viewData = ['user' => $user];
        $content = BASE_PATH . '/view/librarian/users/edit.php';
        extract($viewData);
        include BASE_PATH . '/view/librarian-dashboard.php';
    }


    public function update(int $id): void
    {
        $_SESSION['success_message'] = 'User updated successfully.';
        header('Location: ' . BASE_URL . '/librarian/users');
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
        header('Location: ' . BASE_URL . '/librarian/users');
        exit;
    }
}