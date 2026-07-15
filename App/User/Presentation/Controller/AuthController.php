<?php

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\User\Application\DTO\LoginDTO;
use App\User\Application\DTO\RegisterDTO;
use App\User\Application\Request\RegisterRequest; 
use App\User\Application\UseCase\LoginUser;
use App\User\Application\UseCase\LogoutUser;
use App\User\Application\UseCase\RegisterUser;
use App\User\Exception\DuplicateEmailException;
use App\User\Exception\DuplicatePhoneException;
use App\User\Exception\UserNotFoundException;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;

class AuthController extends BaseController
{
    private RegisterUser $registerUser;
    private LoginUser $loginUser;
    private LogoutUser $logoutUser;
    private UserAuthenticator $authenticator;
    private LoanRepositoryInterface $loanRepository;
    private BookRepositoryInterface $bookRepository;

    
    public function __construct(
        RegisterUser $registerUser,
        LoginUser $loginUser,
        LogoutUser $logoutUser,
        UserAuthenticator $authenticator,
        LoanRepositoryInterface $loanRepository,
        BookRepositoryInterface $bookRepository
    ) {
        parent::__construct(null);

        $this->registerUser = $registerUser;
        $this->loginUser = $loginUser;
        $this->logoutUser = $logoutUser;
        $this->authenticator = $authenticator;
        $this->loanRepository = $loanRepository;
        $this->bookRepository = $bookRepository;
    }


    public function home(): void
    {
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 4));
        }

        $this->view('home', [
            'pageTitle' => 'Welcome to Library Management System',
            'basePath' => BASE_PATH,
            'baseUrl' => BASE_URL ?? '/Library/public'
        ]);
    }

    public function showLogin(): void
    {
        if ($this->authenticator->isAuthenticated()) {
            $this->redirect(BASE_URL . '/user-dashboard');
            return;
        }
        $this->view('auth/login');
    }

    public function showRegister(): void
    {
        if ($this->authenticator->isAuthenticated()) {
            $this->redirect(BASE_URL . '/user-dashboard');
            return;
        }
        $this->view('auth/register');
    }


    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/home');
            return;
        }

        try {
            
            $request = RegisterRequest::fromArray($_POST);
            $userDTO = $this->registerUser->execute($request); 

            $_SESSION['register_success'] = 'Account created successfully! Please check your email/phone for verification.';
            $_SESSION['just_registered'] = true;

            $this->redirect(BASE_URL . '/verify');

        } catch (DuplicateEmailException $e) {
            $_SESSION['register_errors']['email'] = $e->getMessage();
            $_SESSION['register_old'] = $_POST;
            $this->redirect(BASE_URL . '/register');

        } catch (DuplicatePhoneException $e) {
            $_SESSION['register_errors']['phone'] = $e->getMessage();
            $_SESSION['register_old'] = $_POST;
            $this->redirect(BASE_URL . '/register');

        } catch (\InvalidArgumentException $e) {
            $_SESSION['register_errors']['general'] = $e->getMessage();
            $_SESSION['register_old'] = $_POST;
            $this->redirect(BASE_URL . '/register');

        } catch (\Exception $e) {
            $_SESSION['register_errors']['general'] = 'Registration failed: ' . $e->getMessage();
            $_SESSION['register_old'] = $_POST;
            $this->redirect(BASE_URL . '/register');
        }
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/home');
            return;
        }

        try {
            $dto = LoginDTO::fromArray($_POST);
            $userDTO = $this->loginUser->execute($dto);

            $_SESSION['login_success'] = 'Welcome back, ' . $userDTO->name . '!';
            $this->redirect(BASE_URL . '/user-dashboard');

        } catch (UserNotFoundException $e) {
            $_SESSION['login_errors']['general'] = 'No account found with this email/phone';
            $_SESSION['login_old'] = $_POST;
            $this->redirect(BASE_URL . '/login');

        } catch (\RuntimeException $e) {
            $_SESSION['login_errors']['general'] = $e->getMessage();

            if (strpos($e->getMessage(), 'verify') !== false) {
                $_SESSION['warning_message'] = $e->getMessage();
                $this->redirect(BASE_URL . '/verify');
                return;
            }

            $_SESSION['login_old'] = $_POST;
            $this->redirect(BASE_URL . '/login');

        } catch (\Exception $e) {
            $_SESSION['login_errors']['general'] = 'Login failed: ' . $e->getMessage();
            $_SESSION['login_old'] = $_POST;
            $this->redirect(BASE_URL . '/login');
        }
    }

    public function logout(): void
    {
        try {
            $this->logoutUser->execute();
            $_SESSION['logout_success'] = 'You have been logged out successfully.';
        } catch (\Exception $e) {
        }
        $this->redirect(BASE_URL . '/home');
    }


    public function userDashboard(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $_SESSION['login_errors']['general'] = 'Please login to access the dashboard';
            $this->redirect(BASE_URL . '/home');
            return;
        }

        $user = $this->authenticator->getCurrentUser();

        if ($user) {
            $method = $user->getLoginMethod();
            if ($method === 'email' && !$user->isEmailVerified()) {
                $_SESSION['warning_message'] = 'Please verify your email address.';
                $this->redirect(BASE_URL . '/verify');
                return;
            }

            if ($method === 'phone' && !$user->isPhoneVerified()) {
                $_SESSION['warning_message'] = 'Please verify your phone number.';
                $this->redirect(BASE_URL . '/verify-phone');
                return;
            }
        }

        $userId = $_SESSION['user_id'] ?? $user->getId();
        $loans = $this->loanRepository->findActiveByUserId($userId);

        $books = [];
        foreach ($loans as $loan) {
            $book = $this->bookRepository->findById($loan->getBookId());
            if ($book) {
                $books[$loan->getBookId()] = $book;
            }
        }

        $this->view('user-dashboard', [
            'user' => $user,
            'loans' => $loans,
            'books' => $books,
            'pageTitle' => 'My Dashboard'
        ]);
    }

    public function adminDashboard(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $_SESSION['login_errors']['general'] = 'Please login to access the dashboard';
            $this->redirect(BASE_URL . '/home');
            return;
        }

        $user = $this->authenticator->getCurrentUser();

        if ($user && $user->getRole() !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access the admin dashboard.';
            $this->redirect(BASE_URL . '/user-dashboard');
            return;
        }

        $this->view('admin-dashboard');
    }


    public function checkAuth(): array
    {
        return [
            'authenticated' => $this->authenticator->isAuthenticated(),
            'user' => $this->authenticator->getCurrentUser()
        ];
    }
}