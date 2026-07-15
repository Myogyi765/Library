<?php

namespace App\Admin\Presentation\Controller;

use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;
use App\Admin\Application\Service\UserManagementService;
use App\User\Domain\ValueObject\UserStatus; // ✅ Import the value object

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
            $_SESSION['success_message'] = 'User deleted successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
        }

        $this->redirect('/admin/users');
    }

    /**
     * Toggle user status (Enable / Disable)
     */
    public function toggleStatus(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }

        try {
            // 1. Fetch the user
            $user = $this->userRepository->findById($id);
            if (!$user) {
                throw new \Exception('User not found.');
            }

            // 2. Get the current status (a UserStatus value object)
            $currentStatus = $user->getStatus(); // e.g., UserStatus::ACTIVE

            // 3. Determine the new status string based on the current one
            // Assuming UserStatus has a method getValue() or you can compare directly
            // If you have constants: UserStatus::ACTIVE and UserStatus::INACTIVE
            if ($currentStatus->getValue() === 'active') {
                $newStatusString = 'inactive';
            } else {
                $newStatusString = 'active';
            }

            // 4. Create a new UserStatus value object with the new status
            $newStatus = new UserStatus($newStatusString);

            // 5. Set the new status on the user entity
            $user->setStatus($newStatus);

            // 6. Save via repository (make sure your repository has a save() method)
            $this->userRepository->save($user);

            // 7. Set success message
            $_SESSION['success_message'] = 'User status updated to ' . ucfirst($newStatusString) . '.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to toggle status: ' . $e->getMessage();
        }

        // 8. Redirect back to the user list
        $this->redirect('/admin/users');
    }
}