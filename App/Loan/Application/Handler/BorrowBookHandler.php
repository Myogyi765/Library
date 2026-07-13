<?php

namespace App\Loan\Application\Handler;

use App\Loan\Domain\Entity\Loan;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Loan\Application\Command\BorrowBookCommand;
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

        $loan = new Loan($cmd->userId, $cmd->bookId);

        $maxDays = $this->settingsService->getMaxBorrowDays();
        $borrowingFee = $this->settingsService->getBorrowingFee();
        
        $now = new \DateTimeImmutable();
        $dueDate = $now->modify("+{$maxDays} days");
        
        if (method_exists($loan, 'setBorrowedAt')) {
            $loan->setBorrowedAt($now);
        }
        if (method_exists($loan, 'setDueDate')) {
            $loan->setDueDate($dueDate);
        }
        if (method_exists($loan, 'setBorrowingFee')) {
            $loan->setBorrowingFee($borrowingFee);
        }

        $this->loanRepo->save($loan);
    }
}