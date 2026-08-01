<?php

namespace App\Librarian\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Circulation\Domain\ValueObject\LoanStatus;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Admin\Application\Service\SettingsService;

class ScanController extends BaseController
{
    private LoanRepositoryInterface $loanRepo;
    private BookRepositoryInterface $bookRepo;
    private UserRepositoryInterface $userRepo;
    private UserAuthenticator $authenticator;
    private SettingsService $settingsService;

    public function __construct(
        LoanRepositoryInterface $loanRepo,
        BookRepositoryInterface $bookRepo,
        UserRepositoryInterface $userRepo,
        UserAuthenticator $authenticator,
        SettingsService $settingsService
    ) {
        parent::__construct();
        $this->loanRepo = $loanRepo;
        $this->bookRepo = $bookRepo;
        $this->userRepo = $userRepo;
        $this->authenticator = $authenticator;
        $this->settingsService = $settingsService;
    }

    
    public function scan(): void
    {
        if (!$this->authenticator->isAuthenticated() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $loanId = (int) ($_GET['loan_id'] ?? 0);
        if ($loanId <= 0) {
            $this->view('librarian/scan/error', ['message' => 'Invalid QR Code – Loan ID not found.']);
            return;
        }

        $loan = $this->loanRepo->findById($loanId);
        if (!$loan) {
            $this->view('librarian/scan/error', ['message' => 'Loan record not found.']);
            return;
        }

        $user = $this->userRepo->findById($loan->getUserId());
        $book = $this->bookRepo->findById($loan->getBookId());

        // ✅ Calculate fine and overdue days
        $finePerDay = $this->settingsService->getFinePerDay();
        $gracePeriod = $this->settingsService->getGracePeriodDays();
        $fine = $loan->calculateFine($finePerDay, $gracePeriod);
        $overdueDays = $loan->getOverdueDays();
        $isOverdue = $loan->isOverdue();

        $this->view('librarian/scan/result', [
            'loan'        => $loan,
            'user'        => $user,
            'book'        => $book,
            'fine'        => $fine,
            'overdueDays' => $overdueDays,
            'isOverdue'   => $isOverdue,
        ]);
    }

    
    public function returnBook(): void
    {
        if (!$this->authenticator->isAuthenticated() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $loanId = (int) ($_POST['loan_id'] ?? 0);
        if ($loanId <= 0) {
            $_SESSION['error_message'] = 'Invalid loan ID.';
            $this->redirect('/librarian/scan?loan_id=' . $loanId);
            return;
        }

        try {
            $loan = $this->loanRepo->findById($loanId);
            if (!$loan) {
                throw new \Exception('Loan not found.');
            }

            $status = $loan->getStatus()->getValue();
            if ($status === 'returned') {
                throw new \Exception('This book has already been returned.');
            }

            if ($status !== 'active') {
                throw new \Exception('This loan is not active. Current status: ' . $status);
            }

            // Check if there is a fine – require payment first
            $finePerDay = $this->settingsService->getFinePerDay();
            $gracePeriod = $this->settingsService->getGracePeriodDays();
            $fine = $loan->calculateFine($finePerDay, $gracePeriod);

            if ($fine > 0) {
                // Mark as awaiting payment instead of returning directly
                $loan->setStatus(LoanStatus::AWAITING_PAYMENT());
                $this->loanRepo->save($loan);

                // Create notification for user
                $this->createNotification(
                    $loan->getUserId(),
                    'user',
                    'fine_due',
                    '⚠️ Overdue Fine Due',
                    "Your borrowed book is overdue. Please pay a fine of {$fine} MMK before returning.",
                    BASE_URL . '/payment/submit/' . $loan->getId() . '?type=fine'
                );

                $_SESSION['warning_message'] = "Book is overdue. Fine of {$fine} MMK must be paid first.";
                $this->redirect('/librarian/scan?loan_id=' . $loanId);
                return;
            }

            // No fine – return normally
            $loan->setReturnedAt(new \DateTimeImmutable());
            $loan->setStatus(LoanStatus::RETURNED());
            $this->loanRepo->save($loan);

            $book = $this->bookRepo->findById($loan->getBookId());
            if ($book) {
                $book->setAvailableQuantity($book->getAvailableQuantity() + 1);
                $this->bookRepo->save($book);
            }

            // Create notification for user
            $this->createNotification(
                $loan->getUserId(),
                'user',
                'loan_returned',
                'Book returned',
                'Your borrowed book has been returned successfully.',
                BASE_URL . '/user-dashboard'
            );

            $_SESSION['success_message'] = 'Book returned successfully!';
            $this->redirect('/librarian/scan?loan_id=' . $loanId);

        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to return book: ' . $e->getMessage();
            $this->redirect('/librarian/scan?loan_id=' . $loanId);
        }
    }

    
    public function scanner(): void
    {
        if (!$this->authenticator->isAuthenticated() || ($_SESSION['user_role'] ?? '') !== 'librarian') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->view('librarian/scan/scanner', [
            'page' => 'scanner'
        ]);
    }
}
