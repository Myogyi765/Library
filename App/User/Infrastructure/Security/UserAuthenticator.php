<?php

namespace App\User\Infrastructure\Security;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Phone;
use App\Shared\Core\Authorization\Authorization;

class UserAuthenticator
{
    private UserRepositoryInterface $userRepository;
    private string $pepper;
    private Authorization $authorization;

    public function __construct(UserRepositoryInterface $userRepository, Authorization $authorization)
    {
        $this->userRepository = $userRepository;
        $this->pepper = $_ENV['PEPPER'] ?? '';
        $this->authorization = $authorization;
    }

    
    public function authenticate(string $identifier, string $password, string $requestedRole = 'user'): bool
    {
        $user = $this->userRepository->findByEmail(new Email($identifier));

        if (!$user && $this->isPhoneNumber($identifier)) {
            try {
                $user = $this->userRepository->findByPhone(new Phone($identifier));
            } catch (\Exception $e) {
                $user = null;
            }
        }

        // 1. Check if user exists and password is valid
        if (!$user || !$user->getPassword()->verify($password)) {
            $_SESSION['login_errors']['general'] = 'Invalid email/phone or password.';
            return false;
        }

        // 2. Role validation: compare actual role with requested role
        $actualRole = $user->getRole() ?? 'user';
        if ($actualRole !== $requestedRole) {
            // Build user-friendly role names
            $roleNames = [
                'admin'     => 'Admin',
                'librarian' => 'Librarian',
                'user'      => 'User',
            ];
            $displayRequested = $roleNames[$requestedRole] ?? ucfirst($requestedRole);
            $displayActual = $roleNames[$actualRole] ?? ucfirst($actualRole);

            $_SESSION['login_errors']['general'] = 
                "You selected '{$displayRequested}' but your account is '{$displayActual}'. Please select '{$displayActual}' to login.";
            return false;
        }

        // 3. All good – perform login
        $this->login($user);
        return true;
    }

    /**
     * Check if string looks like a phone number
     */
    private function isPhoneNumber(string $value): bool
    {
        return preg_match('/^\+?[0-9]{7,15}$/', $value) === 1;
    }

    public function login(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear old authentication flags
        unset(
            $_SESSION['admin_logged_in'],
            $_SESSION['librarian_logged_in'],
            $_SESSION['admin_id'],
            $_SESSION['admin_name'],
            $_SESSION['librarian_id'],
            $_SESSION['librarian_name'],
            $_SESSION['librarian_department']
        );

        // Set unified session variables
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_email'] = $user->getEmail()->getValue();
        $_SESSION['user_phone'] = $user->getPhone()?->getValue();
        $_SESSION['user_role'] = $user->getRole() ?? 'user';
        $_SESSION['user_status'] = $user->getStatus()?->getValue() ?? 'active';
        $_SESSION['user_login_method'] = $user->getLoginMethod() ?? 'email';
        $_SESSION['user_identifier'] = $user->getIdentifier() ?? $user->getEmail()->getValue();
        $_SESSION['user_authenticated'] = true;
        $_SESSION['logged_in'] = true;

        // Load permissions via Authorization
        $this->authorization->loadUserPermissions($user->getId());

        session_regenerate_id(true);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            $this->userRepository->updateRememberToken($_SESSION['user_id'], null);
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        return $this->isAuthenticated();
    }

    public function isAuthenticated(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->isAuthenticated() || !isset($_SESSION['user_id'])) {
            return null;
        }
        return $this->userRepository->findById((int)$_SESSION['user_id']);
    }

    public function getCurrentUserId(): ?int
    {
        if (!$this->isAuthenticated() || !isset($_SESSION['user_id'])) {
            return null;
        }
        return (int)$_SESSION['user_id'];
    }

    public function generateRememberToken(User $user): string
    {
        $data = $user->getId() . $user->getEmail()->getValue() . $this->pepper . time();
        return hash('sha256', $data) . bin2hex(random_bytes(16));
    }

    public function loginWithRememberToken(string $token): ?User
    {
        $user = $this->userRepository->findByRememberToken($token);
        if ($user && $user->canLogin()) {
            $this->login($user);
            return $user;
        }
        return null;
    }
}