<?php
namespace App\Admin\Presentation\Controller;

use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;

class AdminRoleController extends BaseController
{
    private UserRepositoryInterface $userRepository;
    private UserAuthenticator $userAuth;

    public function __construct(UserRepositoryInterface $userRepository, UserAuthenticator $userAuth)
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
            $this->redirect('/login');
        }

        $users = $this->userRepository->findAll();
        $roles = $this->userRepository->getAllRoles();

        $this->view('admin-dashboard', [
            'pageTitle' => 'Manage Roles',
            'content' => BASE_PATH . '/view/admin/roles/index.php',
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function edit(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/admin/roles');
        }

        $roles = $this->userRepository->getAllRoles();

        $this->view('admin-dashboard', [
            'pageTitle' => 'Edit User Role',
            'content' => BASE_PATH . '/view/admin/roles/edit.php',
            'user' => $user,
            'roles' => $roles
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/admin/roles');
        }

        $newRole = $_POST['role'] ?? '';
        if (empty($newRole)) {
            $_SESSION['error_message'] = 'Please select a role.';
            $this->redirect('/admin/roles/edit/' . $id);
        }

        $roleId = $this->userRepository->getRoleIdByName($newRole);
        if (!$roleId) {
            $_SESSION['error_message'] = 'Invalid role.';
            $this->redirect('/admin/roles/edit/' . $id);
        }

        $user->setRoleId($roleId);
        $this->userRepository->save($user);

        $_SESSION['success_message'] = 'User role updated successfully.';
        $this->redirect('/admin/roles');
    }
}