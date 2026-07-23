<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\VerificationServiceInterface;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Admin\Application\Service\SettingsService; 

class DashboardController extends BaseController
{
    private UserAuthenticator $authenticator;
    private VerificationServiceInterface $verificationService;
    private UserRepositoryInterface $userRepository;
    private LoanRepositoryInterface $loanRepository;
    private BookRepositoryInterface $bookRepository;
    private SettingsService $settingsService; 

    public function __construct(
        UserAuthenticator $authenticator,
        VerificationServiceInterface $verificationService,
        UserRepositoryInterface $userRepository,
        LoanRepositoryInterface $loanRepository,
        BookRepositoryInterface $bookRepository,
        SettingsService $settingsService
    ) {
        parent::__construct();
        $this->authenticator = $authenticator;
        $this->verificationService = $verificationService;
        $this->userRepository = $userRepository;
        $this->loanRepository = $loanRepository;
        $this->bookRepository = $bookRepository;
        $this->settingsService = $settingsService; 
    }

    public function userDashboard(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $_SESSION['login_errors'] = ['Please login to access the dashboard'];
            $this->redirect(BASE_URL . '/home');
            return;
        }

        $user = $this->authenticator->getCurrentUser();

        if ($user) {
            $method = $user->getLoginMethod();

            if ($method === 'email' && !$user->isEmailVerified()) {
                $token = $this->verificationService->generateVerificationToken($user);
                $code = $this->verificationService->generateVerificationCode();

                $user->setVerificationToken($token);
                $user->setVerificationCode($code);
                $this->userRepository->save($user);

                $this->verificationService->sendVerificationEmail($user, $token, $code);

                $_SESSION['warning_message'] = 'Please verify your email address. A new verification email has been sent.';
                $this->redirect(BASE_URL . '/verify');
                return;
            }

            if ($method === 'phone' && !$user->isPhoneVerified()) {
                $_SESSION['warning_message'] = 'Please verify your phone number before accessing the dashboard.';
                $this->redirect(BASE_URL . '/verify-phone');
                return;
            }
        }

        $loans = [];
        $books = [];
        $enrichedLoans = [];

        if ($user) {
            $userId = $user->getId();
            $loans = $this->loanRepository->findByUserId($userId) ?? [];

            $finePerDay = $this->settingsService->getFinePerDay();
            $gracePeriod = $this->settingsService->getGracePeriodDays();

            $booksById = [];
            foreach ($loans as $loan) {
                $bookId = $loan->getBookId();
                if (!isset($booksById[$bookId])) {
                    $book = $this->bookRepository->findById($bookId);
                    if ($book) {
                        $booksById[$bookId] = $book;
                    }
                }
            }
            $books = $booksById;

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
            $_SESSION['login_errors'] = ['Please login to access the dashboard'];
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
}
