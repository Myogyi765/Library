<?php

namespace App\Admin\Presentation\Controller;

use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;
use App\Admin\Application\Service\UserManagementService;
use App\User\Domain\ValueObject\UserStatus;

class AdminUserController extends BaseController
{
    private UserRepositoryInterface $userRepository;
    private UserAuthenticator $userAuth;
    private UserManagementService $userService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserAuthenticator $userAuth,
        UserManagementService $userService
    ) {
        $this->userRepository = $userRepository;
        $this->userAuth = $userAuth;
        $this->userService = $userService;
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

        $users = $this->userRepository->findByRole('user');

        $this->view('admin-dashboard', [
            'pageTitle' => 'Manage Users',
            'content' => BASE_PATH . '/view/admin/users/index.php',
            'users' => $users
        ]);
    }

    public function create(): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }

        $this->view('admin-dashboard', [
            'pageTitle' => 'Create User',
            'content' => BASE_PATH . '/view/admin/users/create.php'
        ]);
    }

    public function store(): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }

        $data = $_POST;

        try {
            $this->userService->createUser($data);
            $this->createNotification(
                (int) ($_SESSION['user_id'] ?? 0),
                'admin',
                'user_created',
                'User created',
                'A new user account was created successfully.',
                '/admin/users'
            );
            $_SESSION['success_message'] = 'User created successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
            $this->redirect('/admin/users/create');
        }

        $this->redirect('/admin/users');
    }

    public function edit(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/admin/users');
        }

        $this->view('admin-dashboard', [
            'pageTitle' => 'Edit User',
            'content' => BASE_PATH . '/view/admin/users/edit.php',
            'user' => $user
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }

        $data = $_POST;

        try {
            $this->userService->updateUser($id, $data);
            $this->createNotification(
                (int) ($_SESSION['user_id'] ?? 0),
                'admin',
                'user_updated',
                'User updated',
                'A user account was updated successfully.',
                '/admin/users'
            );
            $_SESSION['success_message'] = 'User updated successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
            $this->redirect('/admin/users/edit/' . $id);
        }

        $this->redirect('/admin/users');
    }

    public function delete(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }

        try {
            $this->userRepository->delete($id);
            $this->createNotification(
                (int) ($_SESSION['user_id'] ?? 0),
                'admin',
                'user_deleted',
                'User deleted',
                'A user account was deleted successfully.',
                '/admin/users'
            );
            $_SESSION['success_message'] = 'User deleted successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
        }

        $this->redirect('/admin/users');
    }
    public function toggleStatus(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }

        try {
            $user = $this->userRepository->findById($id);
            if (!$user) {
                throw new \Exception('User not found.');
            }

            $currentStatus = $user->getStatus(); 
            if ($currentStatus->getValue() === 'active') {
                $newStatusString = 'inactive';
            } else {
                $newStatusString = 'active';
            }

            $newStatus = new UserStatus($newStatusString);

            $user->setStatus($newStatus);

            $this->userRepository->save($user);

            $this->createNotification(
                (int) ($_SESSION['user_id'] ?? 0),
                'admin',
                'user_status_toggled',
                'User status updated',
                'A user account status was changed successfully.',
                '/admin/users'
            );
            $_SESSION['success_message'] = 'User status updated to ' . ucfirst($newStatusString) . '.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to toggle status: ' . $e->getMessage();
        }

        $this->redirect('/admin/users');
    }
}