<?php

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Shared\Helpers\Logger;
use App\User\Application\Request\LoginRequest;
use App\User\Application\Request\RegisterRequest;
use App\User\Application\UseCase\LoginUser;
use App\User\Application\UseCase\LogoutUser;
use App\User\Application\UseCase\RegisterUser;
use App\User\Exception\DuplicateEmailException;
use App\User\Exception\DuplicatePhoneException;
use App\User\Exception\UserNotFoundException;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\User\Infrastructure\Persistence\UserRepository;
use App\User\Domain\Service\UserDomainService;
use App\User\Domain\Service\VerificationServiceInterface;
use App\User\Presentation\Validator\UserValidator;

use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;

class AuthController extends BaseController
{
    private RegisterUser $registerUser;
    private LoginUser $loginUser;
        private LogoutUser $logoutUser;
    private UserAuthenticator $authenticator;
    private ?string $verificationToken = null;
    
    private LoanRepositoryInterface $loanRepository;
    private BookRepositoryInterface $bookRepository;
    
    public function __construct($container = null)
    {
        parent::__construct($container);
        
        try {
            Logger::info('Initializing AuthController');
            
            $userRepository = $this->container->get('user.repository');
            
            $authenticator = $this->container->get('user.authenticator');
            
            $domainService = $this->container->get('user.domain.service');
            
            $verificationService = $this->container->get('verification.service');
            
            // Loan & Book Repositories
            $this->loanRepository = $this->container->get('loan.repository');
            $this->bookRepository = $this->container->get('book.repository');
            
            $this->registerUser = new RegisterUser(
                $userRepository,
                $domainService,
                $verificationService
            );
            $this->loginUser = new LoginUser(
                $userRepository,
                $domainService,
                $authenticator
            );
            $this->logoutUser = new LogoutUser($authenticator);
            $this->authenticator = $authenticator;
            
            Logger::info('AuthController initialized successfully');
        } catch (\Exception $e) {
            Logger::exception($e);
            throw $e;
        }
    }
    
    public function home(): void
    {
        try {
            Logger::debug('Loading home page');
            
            if (!defined('BASE_PATH')) {
                define('BASE_PATH', $this->basePath);
            }
            
            $data = [
                'pageTitle' => 'Welcome to Library Management System',
                'basePath' => $this->basePath,
                'baseUrl' => BASE_URL ?? '/Library/public'
            ];
            
            $this->view('home', $data);
        } catch (\Exception $e) {
            Logger::exception($e);
            throw $e;
        }
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
            Logger::info('Processing registration request');
            
            $validator = new UserValidator();
            if (!$validator->validateRegister($_POST)) {
                $_SESSION['register_errors'] = $validator->getErrors();
                $_SESSION['register_old'] = $_POST;
                $this->redirect(BASE_URL . '/home');
                return;
            }
            
            $request = RegisterRequest::fromArray($_POST);
            
            $method = $request->getMethod();
            $email = $request->getEmail();
            $phone = $request->getPhone();
            
            if ($method === 'phone' && !empty($phone)) {
                if (!preg_match('/^\+95[0-9]{7,10}$/', $phone)) {
                    throw new \InvalidArgumentException('Phone number must be in format +95XXXXXXXXX');
                }
            }
            
            if ($method === 'email' && empty($email)) {
                throw new \InvalidArgumentException('Email is required for email registration');
            }
            
            if ($method === 'phone' && empty($phone)) {
                throw new \InvalidArgumentException('Phone number is required for phone registration');
            }
            
            $userDTO = $this->registerUser->execute($request);
            
            $_SESSION['register_success'] = 'Account created successfully!';
            
            $verificationService = $this->container->get('verification.service');
            
            $userRepository = $this->container->get('user.repository');
            $user = $userRepository->findById($userDTO->id);
            
            if ($user) {
                $token = $verificationService->generateVerificationToken($user);
                $code = $verificationService->generateVerificationCode();
                $expiresAt = (new \DateTime('+15 minutes'))->format('Y-m-d H:i:s');
                
                $user->setVerificationToken($token);
                $user->setVerificationCode($code);
                $user->setVerificationExpiresAt($expiresAt);
                $userRepository->save($user);
                
                $_SESSION['verification_token'] = $token;
                
                if ($request->getMethod() === 'email') {
                    $verificationService->sendVerificationEmail($user, $token, $code);
                    $_SESSION['verification_message'] = 'A verification email has been sent to ' . $userDTO->email . '. Please check your inbox and enter the 6-digit code.';
                } else {
                    $verificationService->sendVerificationSMS($user, $code);
                    $_SESSION['verification_message'] = 'A verification code has been sent to ' . $userDTO->phone . '. Please enter the code to verify your phone.';
                }
            }

            Logger::info('User registered successfully');
            $_SESSION['just_registered'] = true;
            $this->redirect(BASE_URL . '/verify');
            
        } catch (DuplicateEmailException $e) {
            Logger::warning('Duplicate email registration attempt: ' . $e->getMessage());
            $_SESSION['register_errors']['email'] = $e->getMessage();
            $_SESSION['register_old'] = $_POST;
            $this->redirect(BASE_URL . '/home');
            
        } catch (DuplicatePhoneException $e) {
            Logger::warning('Duplicate phone registration attempt: ' . $e->getMessage());
            $_SESSION['register_errors']['phone'] = $e->getMessage();
            $_SESSION['register_old'] = $_POST;
            $this->redirect(BASE_URL . '/home');
            
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Invalid input: ' . $e->getMessage());
            $_SESSION['register_errors']['general'] = $e->getMessage();
            $_SESSION['register_old'] = $_POST;
            $this->redirect(BASE_URL . '/home');
            
        } catch (\Exception $e) {
            Logger::exception($e);
            $_SESSION['register_errors']['general'] = 'Registration failed: ' . $e->getMessage();
            $_SESSION['register_old'] = $_POST;
            $this->redirect(BASE_URL . '/home');
        }
    }
    
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/home');
            return;
        }
        
        try {
            Logger::info('Processing login request');
            
            $validator = new UserValidator();
            if (!$validator->validateLogin($_POST)) {
                $_SESSION['login_errors'] = $validator->getErrors();
                $_SESSION['login_old'] = $_POST;
                $this->redirect(BASE_URL . '/home');
                return;
            }
            
            $request = LoginRequest::fromArray($_POST);
            $userDTO = $this->loginUser->execute($request->toDTO());
            
            $user = $this->authenticator->getCurrentUser();
            if ($user) {
                if ($request->getMethod() === 'email' && !$user->isEmailVerified()) {
                    Logger::warning('Unverified email login attempt');
                    $_SESSION['warning_message'] = 'Please verify your email address first. Check your email or resend verification.';
                    $this->redirect(BASE_URL . '/verify');
                    return;
                }
                
                if ($request->getMethod() === 'phone' && !$user->isPhoneVerified()) {
                    Logger::warning('Unverified phone login attempt');
                    $_SESSION['warning_message'] = 'Please verify your phone number first. Enter the code we sent to your phone.';
                    $this->redirect(BASE_URL . '/verify-phone');
                    return;
                }
            }
            
            $_SESSION['login_success'] = 'Welcome back, ' . $userDTO->name . '!';
            Logger::info('User logged in successfully');
            $this->redirect(BASE_URL . '/user-dashboard');
            
        } catch (UserNotFoundException $e) {
            Logger::warning('Login attempt with non-existent user: ' . $e->getMessage());
            $_SESSION['login_errors']['general'] = 'No account found with this email/phone';
            $_SESSION['login_old'] = $_POST;
            $this->redirect(BASE_URL . '/home');
            
        } catch (\Exception $e) {
            Logger::exception($e);
            $_SESSION['login_errors']['general'] = 'Login failed: ' . $e->getMessage();
            $_SESSION['login_old'] = $_POST;
            $this->redirect(BASE_URL . '/home');
        }
    }
    
    public function logout(): void
    {
        try {
            Logger::info('Processing logout request');
            $this->logoutUser->execute();
            $_SESSION['logout_success'] = 'You have been logged out successfully.';
            Logger::info('User logged out successfully');
            $this->redirect(BASE_URL . '/home');
        } catch (\Exception $e) {
            Logger::exception($e);
            $this->redirect(BASE_URL . '/home');
        }
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
                /** @var VerificationServiceInterface $verificationService */
                $verificationService = $this->container->get('verification.service');
                $token = $verificationService->generateVerificationToken($user);
                $code = $verificationService->generateVerificationCode();
                $user->setVerificationToken($token);
                $user->setVerificationCode($code);
                
                /** @var UserRepository $userRepository */
                $userRepository = $this->container->get('user.repository');
                $userRepository->save($user);
                
                $verificationService->sendVerificationEmail($user, $token, $code);
                
                $_SESSION['warning_message'] = 'Please verify your email address. A new verification email has been sent.';
                $_SESSION['warning_action'] = '/resend-verification';
                $_SESSION['warning_action_text'] = 'Resend Verification Email';
                $this->redirect(BASE_URL . '/home');
                return;
            }
            
            if ($method === 'phone' && !$user->isPhoneVerified()) {
                $this->redirect(BASE_URL . '/verify-phone');
                return;
            }
        }
        
        $userId = $_SESSION['user_id'] ?? $user->getId();
        
        $loans = $this->loanRepository->findActiveByUserId($userId);
        
        $books = [];
        foreach ($loans as $loan) {
            $books[$loan->getBookId()] = $this->bookRepository->findById($loan->getBookId());
        }
        
        
        $pageTitle = 'My Dashboard';
        $viewData = [
            'user' => $user,
            'loans' => $loans,
            'books' => $books,
        ];
        $content = BASE_PATH . '/view/user/dashboard-content.php';
        include BASE_PATH . '/view/user-dashboard.php';
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