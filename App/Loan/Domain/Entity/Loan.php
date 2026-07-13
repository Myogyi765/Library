<?php

namespace App\Loan\Domain\Entity;

use App\Loan\Domain\ValueObject\LoanStatus;

class Loan
{
    private ?int $id = null;
    private int $userId;
    private int $bookId;
    private LoanStatus $status;
    private ?\DateTimeImmutable $borrowedAt = null;
    private ?\DateTimeImmutable $dueDate = null;
    private ?\DateTimeImmutable $returnedAt = null;
    private ?\DateTimeImmutable $createdAt = null;

    private ?int $borrowingFee = null; 

    public function __construct(int $userId, int $bookId)
    {
        $this->userId = $userId;
        $this->bookId = $bookId;
        $this->status = LoanStatus::PENDING();
        $this->borrowedAt = null;             
        $this->dueDate = null;               
        $this->createdAt = new \DateTimeImmutable();
    }

    

    public function confirm(): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Only pending loans can be confirmed.');
        }
        $this->status = LoanStatus::ACTIVE();
      
        if ($this->borrowedAt === null) {
            $this->borrowedAt = new \DateTimeImmutable();
        }
    }

    public function reject(): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Only pending loans can be rejected.');
        }
        $this->status = LoanStatus::REJECTED();
    }

    public function returnBook(): void
    {
        if (!$this->status->isActive()) {
            throw new \DomainException('Only active loans can be returned.');
        }
        $this->status = LoanStatus::RETURNED();
        $this->returnedAt = new \DateTimeImmutable();
    }

    public function markAwaitingPayment(): void
    {
        $this->status = LoanStatus::AWAITING_PAYMENT();
    }


    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getBookId(): int { return $this->bookId; }
    public function getStatus(): LoanStatus { return $this->status; }
    public function getBorrowedAt(): ?\DateTimeImmutable { return $this->borrowedAt; }
    public function getDueDate(): ?\DateTimeImmutable { return $this->dueDate; }
    public function getReturnedAt(): ?\DateTimeImmutable { return $this->returnedAt; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getBorrowingFee(): ?int { return $this->borrowingFee; }
    public function setBorrowingFee(int $fee): void { $this->borrowingFee = $fee; }

    public function setId(int $id): void { $this->id = $id; }
    public function setStatus(LoanStatus $status): void { $this->status = $status; }
    public function setBorrowedAt(?\DateTimeImmutable $date): void { $this->borrowedAt = $date; }
    public function setDueDate(?\DateTimeImmutable $date): void { $this->dueDate = $date; }
    public function setReturnedAt(?\DateTimeImmutable $date): void { $this->returnedAt = $date; }
    public function setCreatedAt(\DateTimeImmutable $date): void { $this->createdAt = $date; }
}