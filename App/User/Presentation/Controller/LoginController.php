<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Shared\Core\Authorization\Authorization;
use App\User\Infrastructure\Security\UserAuthenticator;

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
            $redirect = $this->getRedirectUrlByRole($role);
            $this->redirect($redirect);
            return;
        }

        $this->view('auth/login');
    }

    public function login(): void
    {
        $identifier = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $method = $_POST['login_method'] ?? 'email'; // ✅ Get the selected tab (email or phone)

        if (empty($identifier) || empty($password)) {
            $_SESSION['login_errors']['general'] = 'Email/Phone and password are required.';
            $this->redirect(BASE_URL . '/login');
            return;
        }

        // ✅ Pass the method to the authenticator
        $success = $this->userAuth->authenticate($identifier, $password, $method);

        if ($success) {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                $this->authorization->loadUserPermissions($userId);
            }

            $role = $_SESSION['user_role'] ?? 'user';
            $redirect = $this->getRedirectUrlByRole($role);

            $_SESSION['success'] = 'Login successful.';
            $this->redirect($redirect);
        } else {
            // Check if the user is pending and redirect to verification page
            if (isset($_SESSION['pending_user_id']) && isset($_SESSION['pending_method'])) {
                $method = $_SESSION['pending_method'];
                if ($method === 'phone') {
                    $this->redirect(BASE_URL . '/verify-phone');
                } else {
                    $this->redirect(BASE_URL . '/verify');
                }
                return;
            }

            // Fallback: keep the generic error
            if (!isset($_SESSION['login_errors']['general'])) {
                $_SESSION['login_errors']['general'] = 'Login failed. Please check your credentials.';
            }
            $this->redirect(BASE_URL . '/login');
        }
    }

    public function logout(): void
    {
        $this->userAuth->logout();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        $this->redirect(BASE_URL . '/login');
    }

    private function getRedirectUrlByRole(string $role): string
    {
        switch ($role) {
            case 'admin':
                return BASE_URL . '/admin/dashboard';
            case 'librarian':
                return BASE_URL . '/librarian/dashboard';
            default:
                return BASE_URL . '/user-dashboard';
        }
    }
}
