<?php
namespace App\Admin\Presentation\Controller;

use App\User\Infrastructure\Persistence\UserRepository;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;

class AdminRoleController extends BaseController
{
    private UserRepository $userRepository;
    private UserAuthenticator $userAuth;

    public function __construct(UserRepository $userRepository, UserAuthenticator $userAuth)
    {
        $this->userRepository = $userRepository;
        $this->userAuth = $userAuth;
    }

    private function isAdmin(): bool
    {
        return $this->userAuth->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public function index(): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $users = $this->userRepository->findAll();
        $roles = $this->userRepository->getAllRoles();

        $pageTitle = 'Manage Roles';
        $content = BASE_PATH . '/view/admin/roles/index.php';
        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function edit(int $id): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            header('Location: ' . BASE_URL . '/admin/roles');
            exit;
        }

        $roles = $this->userRepository->getAllRoles();

        $pageTitle = 'Edit User Role';
        $content = BASE_PATH . '/view/admin/roles/edit.php';
        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function update(int $id): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            header('Location: ' . BASE_URL . '/admin/roles');
            exit;
        }

        $newRole = $_POST['role'] ?? '';
        if (empty($newRole)) {
            $_SESSION['error_message'] = 'Please select a role.';
            header('Location: ' . BASE_URL . '/admin/roles/edit/' . $id);
            exit;
        }

        // Get role ID
        $roleId = $this->userRepository->getRoleIdByName($newRole);
        if (!$roleId) {
            $_SESSION['error_message'] = 'Invalid role.';
            header('Location: ' . BASE_URL . '/admin/roles/edit/' . $id);
            exit;
        }

        // Update user's role
        $user->setRoleId($roleId);
        $this->userRepository->save($user);

        $_SESSION['success_message'] = 'User role updated successfully.';
        header('Location: ' . BASE_URL . '/admin/roles');
        exit;
    }
}