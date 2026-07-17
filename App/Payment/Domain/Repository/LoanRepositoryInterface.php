<?php
namespace App\Payment\Domain\Repository;

interface LoanRepositoryInterface
{
    public function findById(int $id): ?object; 
    public function save(object $loan): void;
}