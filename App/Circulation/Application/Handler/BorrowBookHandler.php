<?php

namespace App\Circulation\Application\Handler;

use App\Circulation\Domain\Entity\Loan;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Circulation\Application\Command\BorrowBookCommand;
use App\Admin\Application\Service\SettingsService;

class BorrowBookHandler
{
    public function __construct(
        private LoanRepositoryInterface $loanRepo,
        private BookRepositoryInterface $bookRepo,
        private SettingsService $settingsService 
    ) {}

    public function handle(BorrowBookCommand $cmd): void
    {
        $book = $this->bookRepo->findById($cmd->bookId);
        if (!$book) {
            throw new \DomainException('Book not found.');
        }
        if ($book->getAvailableQuantity() <= 0) {
            throw new \DomainException('Book is out of stock.');
        }

        $existing = $this->loanRepo->findActiveOrPendingByUserAndBook($cmd->userId, $cmd->bookId);
        if ($existing) {
            throw new \DomainException('You already have a pending or active loan for this book.');
        }

        $maxLimit = $this->settingsService->getMaxBorrowLimit();
        
        $userActiveLoans = $this->loanRepo->findActiveByUserId($cmd->userId);
        $currentLoanCount = count($userActiveLoans);
        
        error_log("📚 [BorrowBookHandler] User {$cmd->userId} has {$currentLoanCount} active loans. Max limit: {$maxLimit}");
        
        if ($currentLoanCount >= $maxLimit) {
            throw new \DomainException("You have reached the maximum borrowing limit of {$maxLimit} books. Please return some books before borrowing more.");
        }

        $maxDays = $this->settingsService->getMaxBorrowDays();
        $borrowingFee = $this->settingsService->getBorrowingFee();

        error_log("🔥🔥🔥 [BorrowBookHandler] maxDays = " . $maxDays);
        error_log("🔥🔥🔥 [BorrowBookHandler] borrowingFee = " . $borrowingFee);

        $now = new \DateTimeImmutable();
        $dueDate = $now->modify("+{$maxDays} days");

        $loan = new Loan($cmd->userId, $cmd->bookId);

        $loan->setBorrowedAt($now);
        $loan->setDueDate($dueDate);
        $loan->setBorrowingFee($borrowingFee);

        error_log("🔥🔥🔥 [BorrowBookHandler] dueDate = " . $dueDate->format('Y-m-d H:i:s'));

        $this->loanRepo->save($loan);

        error_log("🔥🔥🔥 [BorrowBookHandler] Loan saved successfully. ID = " . $loan->getId());
    }
}