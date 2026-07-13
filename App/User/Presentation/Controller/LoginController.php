<?php

namespace App\User\Presentation\Controller;

use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Base\BaseController;
use App\Shared\Core\Authorization\Authorization;

class LoginController extends BaseController
{
    private UserAuthenticator $userAuth;
    private Authorization $authorization;

    public function __construct(UserAuthenticator $userAuth, Authorization $authorization)
    {
        parent::__construct();
        $this->userAuth = $userAuth;
        $this->authorization = $authorization;
    }

    public function showLogin(): void
    {
        if ($this->userAuth->isLoggedIn()) {
            $role = $_SESSION['user_role'] ?? 'user';
            $redirect = match($role) {
                'admin'     => BASE_URL . '/admin/dashboard',
                'librarian' => BASE_URL . '/librarian/dashboard',
                default     => BASE_URL . '/user-dashboard'
            };
            header('Location: ' . $redirect);
            exit;
        }
        $this->view('auth/login');
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $requestedRole = $_POST['role'] ?? 'user';

        if (empty($email) || empty($password)) {
            $_SESSION['login_errors']['general'] = 'Email and password are required.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $success = $this->userAuth->authenticate($email, $password, $requestedRole);

        if ($success) {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                $this->authorization->loadUserPermissions($userId);
            }

            $role = $_SESSION['user_role'] ?? 'user';
            $redirect = match($role) {
                'admin'     => BASE_URL . '/admin/dashboard',
                'librarian' => BASE_URL . '/librarian/dashboard',
                default     => BASE_URL . '/user-dashboard'
            };
            $_SESSION['success'] = 'Login successful.';
            header('Location: ' . $redirect);
        } else {
            if (!isset($_SESSION['login_errors']['general'])) {
                $_SESSION['login_errors']['general'] = 'Login failed. Please check your credentials.';
            }
            header('Location: ' . BASE_URL . '/login');
        }
        exit;
    }

    public function logout(): void
    {
        $this->userAuth->logout();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}