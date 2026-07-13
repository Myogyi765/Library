<?php
namespace App\Payment\Domain\Repository;

interface LoanRepositoryInterface
{
    public function findById(int $id): ?object; // or Loan entity
    public function save(object $loan): void;
}