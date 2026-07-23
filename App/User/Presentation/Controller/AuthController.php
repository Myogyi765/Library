<?php

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\User\Application\DTO\LoginDTO;
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
use App\Admin\Application\Service\SettingsService; 

class AuthController extends BaseController
{
    private RegisterUser $registerUser;
    private LoginUser $loginUser;
    private LogoutUser $logoutUser;
    private UserAuthenticator $authenticator;
    private LoanRepositoryInterface $loanRepository;
    private BookRepositoryInterface $bookRepository;
    private SettingsService $settingsService;

    public function __construct(
        RegisterUser $registerUser,
        LoginUser $loginUser,
        LogoutUser $logoutUser,
        UserAuthenticator $authenticator,
        LoanRepositoryInterface $loanRepository,
        BookRepositoryInterface $bookRepository,
        SettingsService $settingsService 
    ) {
        parent::__construct(null);

        $this->registerUser = $registerUser;
        $this->loginUser = $loginUser;
        $this->logoutUser = $logoutUser;
        $this->authenticator = $authenticator;
        $this->loanRepository = $loanRepository;
        $this->bookRepository = $bookRepository;
        $this->settingsService = $settingsService;
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

            // ✅ Store user ID for verification page (even if not logged in)
            // The DTO may have a public 'id' property, or you may need to add a getId() method.
            $_SESSION['register_user_id'] = $userDTO->id ?? null;

            // ✅ Redirect based on registration method
            $method = $request->getMethod(); // 'email' or 'phone'
            if ($method === 'phone') {
                $this->redirect(BASE_URL . '/verify-phone');
            } else {
                $this->redirect(BASE_URL . '/verify');
            }

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

        $userId = $_SESSION['user_id'] ?? ($user ? $user->getId() : null);
        if (!$userId) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $loans = $this->loanRepository->findActiveByUserId($userId);

        $finePerDay = $this->settingsService->getFinePerDay();
        $gracePeriod = $this->settingsService->getGracePeriodDays();

        $books = [];
        foreach ($loans as $loan) {
            $bookId = $loan->getBookId();
            if (!isset($books[$bookId])) {
                $book = $this->bookRepository->findById($bookId);
                if ($book) {
                    $books[$bookId] = $book;
                }
            }
        }

        $enrichedLoans = [];
        foreach ($loans as $loan) {
            $fine = $loan->calculateFine($finePerDay, $gracePeriod);
            $overdueDays = $loan->getOverdueDays();
            $isOverdue = $loan->isOverdue();

            $enrichedLoans[] = [
                'loan' => $loan,
                'fine' => $fine,
                'overdue_days' => $overdueDays,
                'is_overdue' => $isOverdue,
            ];
        }

        $this->view('user-dashboard', [
            'user' => $user,
            'loans' => $enrichedLoans,
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
