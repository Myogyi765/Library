<?php

namespace App\Librarian\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Circulation\Domain\ValueObject\LoanStatus;
use App\User\Infrastructure\Security\UserAuthenticator;

class ScanController extends BaseController
{
    private LoanRepositoryInterface $loanRepo;
    private BookRepositoryInterface $bookRepo;
    private UserRepositoryInterface $userRepo;
    private UserAuthenticator $authenticator;

    public function __construct(
        LoanRepositoryInterface $loanRepo,
        BookRepositoryInterface $bookRepo,
        UserRepositoryInterface $userRepo,
        UserAuthenticator $authenticator
    ) {
        parent::__construct();
        $this->loanRepo = $loanRepo;
        $this->bookRepo = $bookRepo;
        $this->userRepo = $userRepo;
        $this->authenticator = $authenticator;
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

        $this->view('librarian/scan/result', [
            'loan' => $loan,
            'user' => $user,
            'book' => $book,
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

            $loan->setReturnedAt(new \DateTimeImmutable());
            $loan->setStatus(LoanStatus::returned());
            $this->loanRepo->save($loan);

            $book = $this->bookRepo->findById($loan->getBookId());
            if ($book) {
                $book->setAvailableQuantity($book->getAvailableQuantity() + 1);
                $this->bookRepo->save($book);
            }

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
