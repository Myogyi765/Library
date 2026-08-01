<?php

namespace App\Librarian\Presentation\Controller;

use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Shared\Base\BaseController;
use App\Circulation\Domain\Entity\Loan;
use App\Circulation\Domain\ValueObject\LoanStatus;
use App\Circulation\Application\Command\BorrowBookCommand;
use App\Circulation\Application\Handler\BorrowBookHandler;
use App\Admin\Application\Service\SettingsService;
use PDO;

class LoanController extends BaseController
{
    private LoanRepositoryInterface $loanRepo;
    private BookRepositoryInterface $bookRepo;
    private UserRepositoryInterface $userRepo;
    private BorrowBookHandler $borrowBookHandler;
    private SettingsService $settingsService;
    private PDO $db;

    public function __construct(
        LoanRepositoryInterface $loanRepo,
        BookRepositoryInterface $bookRepo,
        UserRepositoryInterface $userRepo,
        BorrowBookHandler $borrowBookHandler,
        SettingsService $settingsService,
        PDO $db
    ) {
        parent::__construct();
        $this->loanRepo = $loanRepo;
        $this->bookRepo = $bookRepo;
        $this->userRepo = $userRepo;
        $this->borrowBookHandler = $borrowBookHandler;
        $this->settingsService = $settingsService;
        $this->db = $db;
    }

    
    public function index(): void
    {
        // ✅ Ensure librarian access
        $this->ensureLibrarian();

        // ✅ Pagination parameters
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20; // adjustable
        $offset = ($page - 1) * $perPage;

        // ✅ Get total count and paginated data
        $total = $this->loanRepo->count();
        $loanData = $this->loanRepo->findAllWithDetailsPaginated($perPage, $offset);
        $totalPages = ceil($total / $perPage);

        // ✅ Enrich loans with fine/overdue info
        $finePerDay = $this->settingsService->getFinePerDay();
        $gracePeriod = $this->settingsService->getGracePeriodDays();

        $enrichedLoans = [];
        foreach ($loanData as $row) {
            $loan = $this->loanRepo->findById((int)$row['id']);
            if (!$loan) {
                continue;
            }

            $fine = $loan->calculateFine($finePerDay, $gracePeriod);
            $overdueDays = $loan->getOverdueDays();
            $isOverdue = $loan->isOverdue();

            $enrichedLoans[] = [
                'loan'          => $loan,
                'user_name'     => $row['user_name'] ?? 'Unknown',
                'user_email'    => $row['user_email'] ?? '',
                'book_title'    => $row['book_title'] ?? 'Unknown',
                'book_author'   => $row['book_author'] ?? 'Unknown',
                'book_cover'    => $row['book_cover'] ?? null,
                'fine'          => $fine,
                'overdue_days'  => $overdueDays,
                'is_overdue'    => $isOverdue,
            ];
        }

            $page = 'loans';  
        $pageTitle = 'Loan Records';
        $viewData = [
            'loans'        => $enrichedLoans,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
            'total'        => $total,
            'perPage'      => $perPage,
        ];
        $content = BASE_PATH . '/view/librarian/loans/index.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }

    public function create(): void
    {
        $this->ensureLibrarian();
        $users = $this->userRepo->findAll();
        $books = $this->bookRepo->findAll();
        
        $maxBorrowDays = $this->settingsService->getMaxBorrowDays();
        
        $this->view('librarian/loans/create', [
            'users' => $users,
            'books' => $books,
            'maxBorrowDays' => $maxBorrowDays
        ]);
    }

    public function store(): void
    {
        $this->ensureLibrarian();
        $userId = (int) ($_POST['user_id'] ?? 0);
        $bookId = (int) ($_POST['book_id'] ?? 0);
        
        if (!$userId || !$bookId) {
            $_SESSION['error_message'] = 'Please select a user and a book.';
            $this->redirect(BASE_URL . '/librarian/loans/create');
            return;
        }
        
        $book = $this->bookRepo->findById($bookId);
        if (!$book || $book->getAvailableQuantity() <= 0) {
            $_SESSION['error_message'] = 'Book is not available for borrowing.';
            $this->redirect(BASE_URL . '/librarian/loans/create');
            return;
        }
        
        $existingLoan = $this->loanRepo->findActiveOrPendingByUserAndBook($userId, $bookId);
        if ($existingLoan) {
            $_SESSION['error_message'] = 'User already has an active or pending loan for this book.';
            $this->redirect(BASE_URL . '/librarian/loans/create');
            return;
        }
        
        try {
            $command = new BorrowBookCommand($userId, $bookId);
            $this->borrowBookHandler->handle($command);

            $this->createNotification(
                $userId,
                'user',
                'loan_created',
                'Loan request created',
                'A librarian has created a loan request for you.',
                BASE_URL . '/user-dashboard'
            );

            $_SESSION['success_message'] = 'Loan request created successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to create loan: ' . $e->getMessage();
        }
        
        $this->redirect(BASE_URL . '/librarian/loans');
    }

    public function edit($id): void
    {
        $this->ensureLibrarian();
        $loanId = (int) $id;
        $loan = $this->loanRepo->findById($loanId);
        
        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect(BASE_URL . '/librarian/loans');
            return;
        }
        
        $users = $this->userRepo->findAll();
        $books = $this->bookRepo->findAll();
        
        $this->view('librarian/loans/edit', [
            'loan' => $loan,
            'users' => $users,
            'books' => $books,
        ]);
    }

    public function update($id): void
    {
        $this->ensureLibrarian();
        $loanId = (int) $id;
        $loan = $this->loanRepo->findById($loanId);
        
        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect(BASE_URL . '/librarian/loans');
            return;
        }
        
        $status = $_POST['status'] ?? 'pending';
        
        try {
            $loan->setStatus($this->getLoanStatusFromString($status));
            $this->loanRepo->save($loan);
            $_SESSION['success_message'] = 'Loan updated successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to update loan: ' . $e->getMessage();
        }
        
        $this->redirect(BASE_URL . '/librarian/loans');
    }

    public function confirm($id): void
    {
        $this->ensureLibrarian();
        $loan = $this->loanRepo->findById((int)$id);
        if (!$loan || !$loan->getStatus()->isPending()) {
            $_SESSION['error_message'] = 'Invalid loan or not pending.';
            $this->redirect(BASE_URL . '/librarian/loans');
            return;
        }

        try {
            $this->db->beginTransaction();

            $borrowedAt = new \DateTimeImmutable();
            
            $maxDays = $this->settingsService->getMaxBorrowDays();
            $dueDate = $borrowedAt->modify("+{$maxDays} days");

            $loan->setBorrowedAt($borrowedAt);
            $loan->setDueDate($dueDate);
            $loan->setStatus(LoanStatus::AWAITING_PAYMENT());
            $this->loanRepo->save($loan);

            $this->db->commit();

            $book = $this->bookRepo->findById($loan->getBookId());
            $bookTitle = $book ? $book->getTitle() : 'Unknown Book';
            $bookId = $loan->getBookId();
            $userId = $loan->getUserId();
            $this->createNotification(
                $userId,
                'user',
                'loan_confirmed',
                '✅ Loan Confirmed - ' . $bookTitle,  
                'Your loan for book: "' . $bookTitle . '" has been confirmed. Please complete payment.',
                BASE_URL . '/books/' . $bookId
            );

            $_SESSION['success_message'] = 'Loan confirmed. User must pay now.';
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Failed to confirm loan: ' . $e->getMessage();
        }

        $this->redirect(BASE_URL . '/librarian/dashboard?page=loans');
    }

    public function reject($id): void
    {
        $this->ensureLibrarian();
        $loanId = (int) $id;
        $loan = $this->loanRepo->findById($loanId);

        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect(BASE_URL . '/librarian/dashboard?page=loans');
            return;
        }
        
        if (!$loan->getStatus()->isPending()) {
            $_SESSION['error_message'] = 'Only pending loans can be rejected. Current status: ' . $loan->getStatus()->getValue();
            $this->redirect(BASE_URL . '/librarian/dashboard?page=loans');
            return;
        }

        try {
            $this->db->beginTransaction();
            $loan->reject();
            $this->loanRepo->save($loan);
            $this->db->commit();

            $userId = $loan->getUserId();
            $this->createNotification(
                $userId,
                'user',
                'loan_rejected',
                'Loan rejected',
                'Your loan request has been rejected by the librarian.',
                BASE_URL . '/user-dashboard'
            );

            $_SESSION['success_message'] = 'Loan request rejected successfully.';
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Failed to reject loan: ' . $e->getMessage();
        }

        $this->redirect(BASE_URL . '/librarian/dashboard?page=loans');
    }

    
    public function returnBook($id): void
    {
        $this->ensureLibrarian();
        $loanId = (int) $id;
        $loan = $this->loanRepo->findById($loanId);

        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect(BASE_URL . '/librarian/loans');
            return;
        }
        
        if (!$loan->getStatus()->isActive()) {
            $_SESSION['error_message'] = 'Only active loans can be returned. Current status: ' . $loan->getStatus()->getValue();
            $this->redirect(BASE_URL . '/librarian/loans');
            return;
        }

        $finePerDay = $this->settingsService->getFinePerDay();
        $gracePeriod = $this->settingsService->getGracePeriodDays();
        $fine = $loan->calculateFine($finePerDay, $gracePeriod);

        try {
            $this->db->beginTransaction();

            if ($fine > 0) {
                $loan->markAwaitingPayment();
                $this->loanRepo->save($loan);

                $_SESSION['pending_fine_loan_id'] = $loan->getId();
                $_SESSION['pending_fine_amount'] = $fine;

                $this->createNotification(
                    $loan->getUserId(),
                    'user',
                    'fine_due',
                    '⚠️ Overdue Fine Due',
                    "Your borrowed book is overdue. Please pay a fine of {$fine} MMK.",
                    BASE_URL . '/payment/submit/' . $loan->getId() . '?type=fine'
                );

                $_SESSION['warning_message'] = "Book is overdue. Fine of {$fine} MMK must be paid first.";
            } else {
                $loan->returnBook();
                $this->loanRepo->save($loan);

                $book = $this->bookRepo->findById($loan->getBookId());
                if ($book) {
                    $book->setAvailableQuantity($book->getAvailableQuantity() + 1);
                    $this->bookRepo->save($book);
                }

                $this->createNotification(
                    $loan->getUserId(),
                    'user',
                    'loan_returned',
                    'Book returned',
                    'Your borrowed book has been returned successfully.',
                    BASE_URL . '/user-dashboard'
                );

                $_SESSION['success_message'] = 'Book returned successfully.';
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Failed to process return: ' . $e->getMessage();
        }

        $this->redirect(BASE_URL . '/librarian/loans');
    }

    public function delete($id): void
    {
        $this->ensureLibrarian();
        $loanId = (int) $id;
        $loan = $this->loanRepo->findById($loanId);

        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect(BASE_URL . '/librarian/dashboard?page=loans');
            return;
        }

        try {
            $this->db->beginTransaction();
            if ($loan->getStatus()->isActive()) {
                $book = $this->bookRepo->findById($loan->getBookId());
                if ($book) {
                    $book->setAvailableQuantity($book->getAvailableQuantity() + 1);
                    $this->bookRepo->save($book);
                }
            }
            $this->loanRepo->delete($loanId);
            $this->db->commit();
            $_SESSION['success_message'] = 'Loan deleted successfully.';
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Failed to delete loan: ' . $e->getMessage();
        }

        $this->redirect(BASE_URL . '/librarian/dashboard?page=loans');
    }

    private function getLoanStatusFromString(string $status): LoanStatus
    {
        switch (strtolower($status)) {
            case 'pending': return LoanStatus::PENDING();
            case 'active': return LoanStatus::ACTIVE();
            case 'returned': return LoanStatus::RETURNED();
            case 'rejected': return LoanStatus::REJECTED();
            case 'awaiting_payment': return LoanStatus::AWAITING_PAYMENT();
            default: throw new \InvalidArgumentException('Invalid loan status: ' . $status);
        }
    }
}
