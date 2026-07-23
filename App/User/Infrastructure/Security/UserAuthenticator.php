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

    public function canLogin(User $user): bool
    {
        $status = $user->getStatus()->getValue();
        return $status === 'active';
    }

    public function getLoginError(User $user): ?string
    {
        $status = $user->getStatus()->getValue();
        
        return match($status) {
            'pending'   => 'Your account is pending verification. Please check your email or verify your phone.',
            'inactive'  => 'Your account has been deactivated. Please contact the administrator.',
            'banned'    => 'Your account has been banned. Please contact the administrator for support.',
            'suspended' => 'Your account has been suspended. Please contact the administrator.',
            'active'    => null,
            default     => 'Your account is not active. Please contact support.'
        };
    }

    public function authenticate(string $identifier, string $password): bool
    {
        $user = null;

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            try {
                $user = $this->userRepository->findByEmail(new Email($identifier));
            } catch (\Exception $e) {
                $user = null;
            }
        }
        elseif ($this->isPhoneNumber($identifier)) {
            try {
                $user = $this->userRepository->findByPhone(new Phone($identifier));
            } catch (\Exception $e) {
                $user = null;
            }
        }

        if (!$user) {
            $_SESSION['login_errors']['general'] = 'Invalid email/phone or password.';
            return false;
        }

        if (!$user->getPassword()->verify($password)) {
            $_SESSION['login_errors']['general'] = 'Invalid email/phone or password.';
            return false;
        }

        if (!$this->canLogin($user)) {
            $error = $this->getLoginError($user);
            $_SESSION['login_errors']['general'] = $error ?? 'Your account is not active.';
            return false;
        }

        $this->login($user);
        return true;
    }

    private function isPhoneNumber(string $value): bool
    {
        return preg_match('/^(09|\+95)[0-9]{7,10}$/', $value) === 1;
    }

    public function login(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset(
            $_SESSION['admin_logged_in'],
            $_SESSION['librarian_logged_in'],
            $_SESSION['admin_id'],
            $_SESSION['admin_name'],
            $_SESSION['librarian_id'],
            $_SESSION['librarian_name'],
            $_SESSION['librarian_department']
        );

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_email'] = $user->getEmail()->getValue();
        $_SESSION['user_phone'] = $user->getPhone()?->getValue();
        $_SESSION['user_role'] = $user->getRole() ?? 'user';
        $_SESSION['user_status'] = $user->getStatus()->getValue();
        $_SESSION['user_login_method'] = $user->getLoginMethod() ?? 'email';
        $_SESSION['user_identifier'] = $user->getIdentifier() ?? $user->getEmail()->getValue();
        $_SESSION['user_authenticated'] = true;
        $_SESSION['logged_in'] = true;

        $_SESSION['user_profile_image'] = $user->getProfileImage();

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
        if ($user && $this->canLogin($user)) {
            $this->login($user);
            return $user;
        }
        return null;
    }
}