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
            return;
        }

        $users = $this->userRepository->findByRole('user');

        $this->view('admin-dashboard', [
            'pageTitle' => 'Manage Users',
            'content'   => BASE_PATH . '/view/admin/users/index.php',
            'users'     => $users
        ]);
    }


    public function create(): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $this->view('admin-dashboard', [
            'pageTitle' => 'Create User',
            'content'   => BASE_PATH . '/view/admin/users/create.php'
        ]);
    }


    public function store(): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
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
            $_SESSION['error_message'] = 'Failed to create user: ' . $e->getMessage();
            $this->redirect('/admin/users/create');
            return;
        }

        $this->redirect('/admin/users');
    }


    
    public function showUser(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/admin/users');
            return;
        }

        $this->view('admin-dashboard', [
            'pageTitle' => 'User Profile',
            'content'   => BASE_PATH . '/view/admin/users/view.php',
            'user'      => $user
        ]);
    }


    public function edit(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/admin/users');
            return;
        }

        $this->view('admin-dashboard', [
            'pageTitle' => 'Edit User',
            'content'   => BASE_PATH . '/view/admin/users/edit.php',
            'user'      => $user
        ]);
    }


    public function update(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
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
            $_SESSION['error_message'] = 'Failed to update user: ' . $e->getMessage();
            $this->redirect('/admin/users/edit/' . $id);
            return;
        }

        $this->redirect('/admin/users');
    }


    public function delete(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
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
            $_SESSION['error_message'] = 'Failed to delete user: ' . $e->getMessage();
        }

        $this->redirect('/admin/users');
    }


    public function toggleStatus(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        try {
            $user = $this->userRepository->findById($id);
            if (!$user) {
                throw new \Exception('User not found.');
            }

            $currentStatus = $user->getStatus()->getValue();

            if ($currentStatus === 'active') {
                $newStatusString = 'suspended';
            } elseif ($currentStatus === 'suspended') {
                $newStatusString = 'active';
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
                'User account status was changed to ' . ucfirst($newStatusString) . '.',
                '/admin/users'
            );
            $_SESSION['success_message'] = 'User status updated to ' . ucfirst($newStatusString) . '.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to toggle status: ' . $e->getMessage();
        }

        $this->redirect('/admin/users');
    }
}
