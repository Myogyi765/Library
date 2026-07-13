<?php

namespace App\Loan\Domain\Repository;

use App\Loan\Domain\Entity\Loan;

interface LoanRepositoryInterface
{
    public function save(Loan $loan): void;
    public function findById(int $id): ?Loan;
    public function findByUserAndBook(int $userId, int $bookId): ?Loan;
    public function findActiveOrPendingByUserAndBook(int $userId, int $bookId): ?Loan;
    public function findPendingLoans(): array;
    public function findLoansByUser(int $userId): array;
    public function findActiveByUserId(int $userId): array;
    public function findAll(): array;

      public function delete(int $id): bool;
}