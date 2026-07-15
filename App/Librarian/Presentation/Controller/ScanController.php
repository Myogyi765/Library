<?php

namespace App\Librarian\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Circulation\Domain\ValueObject\LoanStatus;

class ScanController extends BaseController
{
    private LoanRepositoryInterface $loanRepo;
    private BookRepositoryInterface $bookRepo;
    private UserRepositoryInterface $userRepo;

    public function __construct(
        LoanRepositoryInterface $loanRepo,
        BookRepositoryInterface $bookRepo,
        UserRepositoryInterface $userRepo
    ) {
        parent::__construct();
        $this->loanRepo = $loanRepo;
        $this->bookRepo = $bookRepo;
        $this->userRepo = $userRepo;
    }

    public function show(): void
    {
        $loanId = (int) ($_GET['loan_id'] ?? 0);
        if (!$loanId) {
            $this->view('librarian/scan/error', ['message' => 'Invalid QR Code - Loan ID not found.']);
            return;
        }

        $loan = $this->loanRepo->findById($loanId);
        if (!$loan) {
            $this->view('librarian/scan/error', ['message' => 'Loan not found.']);
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
        $loanId = (int) ($_POST['loan_id'] ?? 0);
        $loan = $this->loanRepo->findById($loanId);

        if (!$loan) {
            $_SESSION['error_message'] = 'Loan not found.';
            $this->redirect('/librarian/scan?loan_id=' . $loanId);
            return;
        }

        if (!$loan->getStatus()->isActive()) {
            $_SESSION['error_message'] = 'This loan is not active. Current status: ' . $loan->getStatus()->getValue();
            $this->redirect('/librarian/scan?loan_id=' . $loanId);
            return;
        }

        try {
            $loan->returnBook();
            $this->loanRepo->save($loan);

            // Increase book availability
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
}