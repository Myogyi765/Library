<?php

namespace App\Invoice\Domain\Entity;

use App\Invoice\Domain\ValueObject\InvoiceStatus;

class Invoice
{
    private ?int $id = null;
    private string $invoiceNumber;
    private int $paymentId;
    private int $loanId;
    private int $userId;
    private int $bookId;
    private float $amount;
    private string $currency;
    private string $paymentMethod;
    private string $transactionReference;
    private \DateTimeImmutable $borrowedAt;
    private \DateTimeImmutable $dueDate;
    private \DateTimeImmutable $issuedAt;
    private InvoiceStatus $status;

    public function __construct(
        string $invoiceNumber,
        int $paymentId,
        int $loanId,
        int $userId,
        int $bookId,
        float $amount,
        string $currency,
        string $paymentMethod,
        string $transactionReference,
        \DateTimeImmutable $borrowedAt,
        \DateTimeImmutable $dueDate
    ) {
        $this->invoiceNumber = $invoiceNumber;
        $this->paymentId = $paymentId;
        $this->loanId = $loanId;
        $this->userId = $userId;
        $this->bookId = $bookId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->paymentMethod = $paymentMethod;
        $this->transactionReference = $transactionReference;
        $this->borrowedAt = $borrowedAt;
        $this->dueDate = $dueDate;
        $this->issuedAt = new \DateTimeImmutable();
        $this->status = InvoiceStatus::ISSUED();
    }

    public function getId(): ?int { return $this->id; }
    public function getInvoiceNumber(): string { return $this->invoiceNumber; }
    public function getPaymentId(): int { return $this->paymentId; }
    public function getLoanId(): int { return $this->loanId; }
    public function getUserId(): int { return $this->userId; }
    public function getBookId(): int { return $this->bookId; }
    public function getAmount(): float { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getTransactionReference(): string { return $this->transactionReference; }
    public function getBorrowedAt(): \DateTimeImmutable { return $this->borrowedAt; }
    public function getDueDate(): \DateTimeImmutable { return $this->dueDate; }
    public function getIssuedAt(): \DateTimeImmutable { return $this->issuedAt; }
    public function getStatus(): InvoiceStatus { return $this->status; }

    public function setId(int $id): void { $this->id = $id; }
    public function setStatus(InvoiceStatus $status): void { $this->status = $status; }
    public function setIssuedAt(\DateTimeImmutable $issuedAt): void { $this->issuedAt = $issuedAt; }

    public function cancel(): void
    {
        $this->status = InvoiceStatus::CANCELLED();
    }
}