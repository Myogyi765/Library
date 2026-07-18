<?php

namespace App\Librarian\Presentation\Controller;

use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Shared\Base\BaseController;
use App\Circulation\Domain\Entity\Loan;
use App\Circulation\Domain\ValueObject\LoanStatus;
use PDO;

class LoanController extends BaseController
{
    private LoanRepositoryInterface $loanRepo;
    private BookRepositoryInterface $bookRepo;
    private UserRepositoryInterface $userRepo;
    private PDO $db;

    public function __construct(
        LoanRepositoryInterface $loanRepo,
        BookRepositoryInterface $bookRepo,
        UserRepositoryInterface $userRepo,
        PDO $db
    ) {
        parent::__construct();
        $this->loanRepo = $loanRepo;
        $this->bookRepo = $bookRepo;
        $this->userRepo = $userRepo;
        $this->db = $db;
    }

    public function index(): void
    {
        $loans = $this->loanRepo->findAll();
        $allUsers = $this->userRepo->findAll();
        $allBooks = $this->bookRepo->findAll();

        $users = [];
        foreach ($allUsers as $user) {
            $users[$user->getId()] = $user;
        }

        $books = [];
        foreach ($allBooks as $book) {
            $books[$book->getId()] = $book;
        }

        $pageTitle = 'Loan Records';
        $viewData = [
            'loans' => $loans,
            'users' => $users,
            'books' => $books,
        ];
        $content = BASE_PATH . '/view/librarian/loans/index.php';
        include BASE_PATH . '/view/librarian-dashboard.php';
    }

    public function create(): void
    {
        $users = $this->userRepo->findAll();
        $books = $this->bookRepo->findAll();
        $this->view('librarian/loans/create', [
            'users' => $users,
            'books' => $books,
        ]);
    }

    public function store(): void
    {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $bookId = (int) ($_POST['book_id'] ?? 0);
        
        if (!$userId || !$bookId) {
            $_SESSION['error_message'] = 'Please select a user and a book.';
            $this->redirect('/librarian/loans/create');
            return;
        }
        
        $book = $this->bookRepo->findById($bookId);
        if (!$book || $book->getAvailableQuantity() <= 0) {
            $_SESSION['error_message'] = 'Book is not available for borrowing.';
            $this->redirect('/librarian/loans/create');
            return;
        }
        
        $existingLoan = $this->loanRepo->findActiveOrPendingByUserAndBook($userId, $bookId);
        if ($existingLoan) {
            $_SESSION['error_message'] = 'User already has an active or pending loan for this book.';
            $this->redirect('/librarian/loans/create');
            return;
        }
        
        try {
            $loan = new Loan($userId, $bookId);
            $this->loanRepo->save($loan);

            $this->createNotification(
                $userId,
                'user',
                'loan_created',
                'Loan request created',
                'A librarian has created a loan request for you.',
                '/user-dashboard' 
            );

            $_SESSION['success_message'] = 'Loan request created successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to create loan: ' . $e->getMessage();
        }
        
        $this->redirect('/librarian/loans');
    }

    public function edit($id): void
    {
        $loanId = (int) $id;
        $loan = $this->loanRepo->findById($loanId);
        
        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect('/librarian/loans');
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
        $loanId = (int) $id;
        $loan = $this->loanRepo->findById($loanId);
        
        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect('/librarian/loans');
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
        
        $this->redirect('/librarian/loans');
    }

    
    public function confirm($id): void
    {
        $loan = $this->loanRepo->findById((int)$id);
        if (!$loan || !$loan->getStatus()->isPending()) {
            $_SESSION['error_message'] = 'Invalid loan or not pending.';
            $this->redirect('/librarian/loans');
            return;
        }

        try {
            $this->db->beginTransaction();

            $borrowedAt = new \DateTimeImmutable();
            $dueDate = $borrowedAt->modify('+14 days');

            $loan->setBorrowedAt($borrowedAt);
            $loan->setDueDate($dueDate);
            $loan->setStatus(LoanStatus::AWAITING_PAYMENT());
            $this->loanRepo->save($loan);

            $this->db->commit();

            // ✅ Send notification with a link to the book details page
            $bookId = $loan->getBookId();
            $userId = $loan->getUserId();
            $this->createNotification(
                $userId,
                'user',
                'loan_confirmed',
                'Loan confirmed',
                'Your loan request has been confirmed. Please complete payment.',
                BASE_URL .'/books/' . $bookId  
            );

            $_SESSION['success_message'] = 'Loan confirmed. User must pay now.';
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Failed to confirm loan: ' . $e->getMessage();
        }

        $this->redirect('/librarian/dashboard?page=loans');
    }

    public function reject($id): void
    {
        $loanId = (int) $id;
        $loan = $this->loanRepo->findById($loanId);

        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect('/librarian/dashboard?page=loans');
            return;
        }
        
        if (!$loan->getStatus()->isPending()) {
            $_SESSION['error_message'] = 'Only pending loans can be rejected. Current status: ' . $loan->getStatus()->getValue();
            $this->redirect('/librarian/dashboard?page=loans');
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
                '/user-dashboard'
            );

            $_SESSION['success_message'] = 'Loan request rejected successfully.';
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Failed to reject loan: ' . $e->getMessage();
        }

        $this->redirect('/librarian/dashboard?page=loans');
    }

    public function returnBook($id): void
    {
        $loanId = (int) $id;
        $loan = $this->loanRepo->findById($loanId);

        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect('/librarian/loans');
            return;
        }
        
        if (!$loan->getStatus()->isActive()) {
            $_SESSION['error_message'] = 'Only active loans can be returned. Current status: ' . $loan->getStatus()->getValue();
            $this->redirect('/librarian/loans');
            return;
        }

        try {
            $this->db->beginTransaction();
            $loan->returnBook();
            $this->loanRepo->save($loan);

            $book = $this->bookRepo->findById($loan->getBookId());
            if ($book) {
                $book->setAvailableQuantity($book->getAvailableQuantity() + 1);
                $this->bookRepo->save($book);
            }

            $this->db->commit();

            $userId = $loan->getUserId();
            $this->createNotification(
                $userId,
                'user',
                'loan_returned',
                'Book returned',
                'Your borrowed book has been returned successfully.',
                '/user-dashboard'
            );

            $_SESSION['success_message'] = 'Book returned successfully.';
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Failed to return book: ' . $e->getMessage();
        }

        $this->redirect('/librarian/loans');
    }

    public function delete($id): void
    {
        $loanId = (int) $id;
        $loan = $this->loanRepo->findById($loanId);

        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect('/librarian/dashboard?page=loans');
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

        $this->redirect('/librarian/dashboard?page=loans');
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
