<?php

namespace App\Admin\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\User\Infrastructure\Persistence\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Phone;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\UserStatus;
use DateTime;

class AdminUserController
{
    private UserRepository $userRepository;
    private UserAuthenticator $userAuth;

    public function __construct(UserRepository $userRepository, UserAuthenticator $userAuth)
    {
        $this->userRepository = $userRepository;
        $this->userAuth = $userAuth;
    }

    /**
     * ✅ Check if current user is admin
     */
    private function isAdmin(): bool
    {
        return $this->userAuth->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    /**
     * ✅ Display list of users (only role = 'user')
     */
    public function index(): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // ✅ Only fetch users with role = 'user'
        $users = $this->userRepository->findByRole('user');
        $pageTitle = 'Manage Users';
        $content = BASE_PATH . '/view/admin/users/index.php';

        // AJAX request – return partial view only
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            include $content;
            return;
        }

        // Full page load – use single layout
        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function create(): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $pageTitle = 'Create User';
        $content = BASE_PATH . '/view/admin/users/create.php';

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            include $content;
            return;
        }

        include BASE_PATH . '/view/admin-dashboard.php';
    }

    public function store(): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? null;
        $password = $_POST['password'] ?? '';
        $status = $_POST['status'] ?? 'active';

        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error_message'] = 'Name, email, and password are required.';
            header('Location: ' . BASE_URL . '/admin/users/create');
            exit;
        }

        try {
            // Check if email already exists
            if ($this->userRepository->findByEmailString($email)) {
                $_SESSION['error_message'] = 'Email already registered.';
                header('Location: ' . BASE_URL . '/admin/users/create');
                exit;
            }

            // Check if phone already exists (if provided)
            if ($phone && $this->userRepository->findByPhoneString($phone)) {
                $_SESSION['error_message'] = 'Phone number already registered.';
                header('Location: ' . BASE_URL . '/admin/users/create');
                exit;
            }

            // ✅ Get role ID for 'user'
            $roleId = $this->userRepository->getRoleIdByName('user');
            if (!$roleId) {
                throw new \RuntimeException('Default role "user" not found in database.');
            }

            $emailVO = new Email($email);
            $passwordVO = new Password($password);
            $phoneVO = $phone ? new Phone($phone) : null;
            $statusVO = UserStatus::fromString($status);

            // ✅ Correct constructor with all 20 arguments
            $user = new User(
                null,               // id
                $name,              // name
                $emailVO,           // email
                $phoneVO,           // phone
                $passwordVO,        // password
                $statusVO,          // status
                $roleId,            // roleId (int)
                'user',             // roleName (string)
                false,              // emailVerified
                false,              // phoneVerified
                'email',            // loginMethod
                null,               // rememberToken
                new DateTime(),     // createdAt
                new DateTime(),     // updatedAt
                null,               // lastLoginAt
                null,               // verificationToken
                null,               // verificationCode
                null,               // verificationExpiresAt
                null,               // emailVerifiedAt
                null                // phoneVerifiedAt
            );

            $this->userRepository->save($user);

            $_SESSION['success_message'] = 'User created successfully.';
            header('Location: ' . BASE_URL . '/admin/users');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to create user: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/users/create');
        }
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
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }

        $pageTitle = 'Edit User';
        $content = BASE_PATH . '/view/admin/users/edit.php';

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            include $content;
            return;
        }

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
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }

        $name = $_POST['name'] ?? $user->getName();
        $email = $_POST['email'] ?? $user->getEmail()->getValue();
        $phone = $_POST['phone'] ?? ($user->getPhone() ? $user->getPhone()->getValue() : null);
        $status = $_POST['status'] ?? $user->getStatus()->getValue();
        $password = $_POST['password'] ?? null;

        try {
            // If email changed, check uniqueness
            if ($email !== $user->getEmail()->getValue()) {
                if ($this->userRepository->findByEmailString($email)) {
                    $_SESSION['error_message'] = 'Email already taken.';
                    header('Location: ' . BASE_URL . '/admin/users/edit/' . $id);
                    exit;
                }
                $user->setEmail(new Email($email));
            }

            // If phone changed, check uniqueness
            $currentPhone = $user->getPhone() ? $user->getPhone()->getValue() : null;
            if ($phone !== $currentPhone) {
                if ($phone && $this->userRepository->findByPhoneString($phone)) {
                    $_SESSION['error_message'] = 'Phone already taken.';
                    header('Location: ' . BASE_URL . '/admin/users/edit/' . $id);
                    exit;
                }
                $user->setPhone($phone ? new Phone($phone) : null);
            }

            $user->setName($name);
            $user->setStatus(UserStatus::fromString($status));

            if (!empty($password)) {
                $user->setPassword(new Password($password));
            }

            $this->userRepository->save($user);

            $_SESSION['success_message'] = 'User updated successfully.';
            header('Location: ' . BASE_URL . '/admin/users');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to update user: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/users/edit/' . $id);
        }
    }

    public function delete(int $id): void
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        try {
            $this->userRepository->delete($id);
            $_SESSION['success_message'] = 'User deleted successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to delete user: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/users');
    }
}