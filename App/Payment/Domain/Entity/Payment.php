<?php
namespace App\Payment\Domain\Entity;

use App\Payment\Domain\ValueObject\Money;
use App\Payment\Domain\ValueObject\PaymentStatus;
use App\Payment\Domain\Exception\PaymentDomainException;

class Payment
{
    private ?int $id = null;
    private int $loanId;
    private int $userId;
    private Money $amount;
    private string $paymentMethod;      // 'kpay' or 'wavepay'
    private string $transactionReference;
    private ?string $screenshotPath = null;
    private PaymentStatus $status;
    private ?\DateTimeImmutable $submittedAt = null;
    private ?\DateTimeImmutable $approvedAt = null;
    private ?\DateTimeImmutable $rejectedAt = null;
    private ?string $idempotencyKey = null;

    private ?string $refundStatus = 'none';
    private ?\DateTimeImmutable $refundedAt = null;
    private ?string $refundReason = null;

    public function __construct(
        int $loanId,
        int $userId,
        Money $amount,
        string $paymentMethod,
        string $transactionReference,
        ?string $screenshotPath = null,
        ?string $idempotencyKey = null  
    ) {
        if (!in_array($paymentMethod, ['kpay', 'wavepay'])) {
            throw new \InvalidArgumentException('Payment method must be kpay or wavepay.');
        }
        $this->loanId = $loanId;
        $this->userId = $userId;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
        $this->transactionReference = $transactionReference;
        $this->screenshotPath = $screenshotPath;
        $this->status = PaymentStatus::PENDING_APPROVAL();
        $this->submittedAt = new \DateTimeImmutable();
        $this->idempotencyKey = $idempotencyKey;
    }

    public function approve(): void
    {
        if (!$this->status->equals(PaymentStatus::PENDING_APPROVAL())) {
            throw new PaymentDomainException('Only pending approval can be approved.');
        }
        $this->status = PaymentStatus::APPROVED();
        $this->approvedAt = new \DateTimeImmutable();
    }

    public function reject(): void
    {
        if (!$this->status->equals(PaymentStatus::PENDING_APPROVAL())) {
            throw new PaymentDomainException('Only pending approval can be rejected.');
        }
        $this->status = PaymentStatus::REJECTED();
        $this->rejectedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getLoanId(): int { return $this->loanId; }
    public function getUserId(): int { return $this->userId; }
    public function getAmount(): Money { return $this->amount; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getTransactionReference(): string { return $this->transactionReference; }
    public function getScreenshotPath(): ?string { return $this->screenshotPath; }
    public function getStatus(): PaymentStatus { return $this->status; }
    public function getSubmittedAt(): ?\DateTimeImmutable { return $this->submittedAt; }
    public function getApprovedAt(): ?\DateTimeImmutable { return $this->approvedAt; }
    public function getRejectedAt(): ?\DateTimeImmutable { return $this->rejectedAt; }
    public function getIdempotencyKey(): ?string { return $this->idempotencyKey; }

    public function getRefundStatus(): ?string
    {
        return $this->refundStatus;
    }

    public function getRefundedAt(): ?\DateTimeImmutable
    {
        return $this->refundedAt;
    }

    public function getRefundReason(): ?string
    {
        return $this->refundReason;
    }

    public function setId(int $id): void { $this->id = $id; }
    public function setScreenshotPath(string $path): void { $this->screenshotPath = $path; }
    public function setStatus(PaymentStatus $status): void { $this->status = $status; }
    public function setSubmittedAt(\DateTimeImmutable $date): void { $this->submittedAt = $date; }
    public function setApprovedAt(?\DateTimeImmutable $date): void { $this->approvedAt = $date; }
    public function setRejectedAt(?\DateTimeImmutable $date): void { $this->rejectedAt = $date; }
    public function setIdempotencyKey(string $key): void { $this->idempotencyKey = $key; }

    // ============ Refund Setters ============
    public function setRefundStatus(string $refundStatus): self
    {
        $this->refundStatus = $refundStatus;
        return $this;
    }

    public function setRefundedAt(?\DateTimeImmutable $refundedAt): self
    {
        $this->refundedAt = $refundedAt;
        return $this;
    }

    public function setRefundReason(?string $refundReason): self
    {
        $this->refundReason = $refundReason;
        return $this;
    }
}