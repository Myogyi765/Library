<?php
namespace App\Borrow\Application\UseCase;

use App\Book\Domain\Repository\BookRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\Loan\Domain\Entity\Loan;

class BorrowBook
{
    private BookRepositoryInterface $bookRepository;
    private UserRepositoryInterface $userRepository;
    private LoanRepositoryInterface $loanRepository;

    public function __construct(
        BookRepositoryInterface $bookRepository,
        UserRepositoryInterface $userRepository,
        LoanRepositoryInterface $loanRepository
    ) {
        $this->bookRepository = $bookRepository;
        $this->userRepository = $userRepository;
        $this->loanRepository = $loanRepository;
    }

    public function execute(int $bookId, int $userId): void
    {
        $book = $this->bookRepository->findById($bookId);
        if (!$book) {
            throw new \Exception('Book not found.');
        }
        if ($book->getAvailableQuantity() <= 0) {
            throw new \Exception('No copies available.');
        }

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \Exception('User not found.');
        }

        $existingLoan = $this->loanRepository->findActiveOrPendingByUserAndBook($userId, $bookId);
        if ($existingLoan) {
            throw new \Exception('You already have an active or pending loan for this book.');
        }

        $loan = new Loan($userId, $bookId);
        $this->loanRepository->save($loan);

    }
}