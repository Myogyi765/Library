<?php

namespace App\Circulation\Domain\Repository;

use App\Circulation\Domain\Entity\Loan;

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

    // ─── COUNT METHODS ────────────────────────────────
    public function count(): int;
    public function countByStatus(string $status): int;
    public function countOverdue(): int;
    public function countByUserId(int $userId): int;

    // ─── FIND METHODS ────────────────────────────────
    public function findRecent(int $limit): array;
    public function findByUserId(int $userId): array;
    public function findAllWithDetails(): array;

    // ─── PAGINATION METHODS (PERFORMANCE) ──────────────
    public function findPaginatedByUserId(int $userId, int $limit, int $offset): array;
    public function findAllWithDetailsPaginated(int $limit, int $offset): array;
}