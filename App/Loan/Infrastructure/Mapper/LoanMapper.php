<?php

namespace App\Loan\Infrastructure\Mapper;

use App\Loan\Domain\Entity\Loan;
use App\Loan\Domain\ValueObject\LoanStatus;

class LoanMapper
{
    public function toDomain(array $data): Loan
    {
        $loan = new Loan((int)$data['user_id'], (int)$data['book_id']);
        $loan->setId((int)$data['id']);
        $loan->setStatus(LoanStatus::from($data['status']));

        if (!empty($data['borrowed_at'])) {
            $loan->setBorrowedAt(new \DateTimeImmutable($data['borrowed_at']));
        }
        if (!empty($data['due_date'])) {
            $loan->setDueDate(new \DateTimeImmutable($data['due_date']));
        }
        if (!empty($data['returned_at'])) {
            $loan->setReturnedAt(new \DateTimeImmutable($data['returned_at']));
        }
        if (!empty($data['created_at'])) {
            $loan->setCreatedAt(new \DateTimeImmutable($data['created_at']));
        }

        return $loan;
    }

    public function toPersistence(Loan $loan): array
    {
   
        return [
            'id' => $loan->getId(),
            'user_id' => $loan->getUserId(),
            'book_id' => $loan->getBookId(),
            'status' => $loan->getStatus()->getValue(),
            'borrowed_at' => $loan->getBorrowedAt() ? $loan->getBorrowedAt()->format('Y-m-d H:i:s') : null,
            'due_date' => $loan->getDueDate() ? $loan->getDueDate()->format('Y-m-d H:i:s') : null,
            'returned_at' => $loan->getReturnedAt() ? $loan->getReturnedAt()->format('Y-m-d H:i:s') : null,
            'created_at' => $loan->getCreatedAt() ? $loan->getCreatedAt()->format('Y-m-d H:i:s') : null,
        ];
    }
}