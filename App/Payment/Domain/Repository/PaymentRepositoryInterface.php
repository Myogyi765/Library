<?php
namespace App\Payment\Domain\Repository;

use App\Payment\Domain\Entity\Payment;

interface PaymentRepositoryInterface
{
    public function save(Payment $payment): void;
    public function findById(int $id): ?Payment;
    public function findByLoanId(int $loanId): ?Payment;
    public function findPendingApprovals(): array;
    public function findAll(): array;
    public function findByIdempotencyKey(string $key): ?Payment;
    public function findByUserId(int $userId): array;

    // ✅ FIXED: Only one declaration per method (with default parameters)
    public function findAllWithDetails(int $offset = 0, int $limit = 100): array;
    public function findPendingApprovalsWithDetails(int $offset = 0, int $limit = 100): array;
    public function findByStatusWithDetails(string $status, int $offset = 0, int $limit = 100): array;
}