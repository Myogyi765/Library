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

           public function findPendingApprovalsWithDetails(): array;

           
public function findAllWithDetails(): array;
public function findByStatusWithDetails(string $status): array;
}