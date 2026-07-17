<?php
namespace App\Payment\Domain\ValueObject;

class PaymentStatus
{
    private const PENDING_APPROVAL = 'pending_approval';
    private const APPROVED = 'approved';
    private const REJECTED = 'rejected';

    private string $value;

    private function __construct(string $value)
    {
        if (!in_array($value, [self::PENDING_APPROVAL, self::APPROVED, self::REJECTED])) {
            throw new \InvalidArgumentException("Invalid payment status: $value");
        }
        $this->value = $value;
    }

    public static function PENDING_APPROVAL(): self { return new self(self::PENDING_APPROVAL); }
    public static function APPROVED(): self { return new self(self::APPROVED); }
    public static function REJECTED(): self { return new self(self::REJECTED); }
    public static function from(string $value): self { return new self($value); }

    public function getValue(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    public function isPendingApproval(): bool
    {
        return $this->value === self::PENDING_APPROVAL;
    }

    public function isApproved(): bool
    {
        return $this->value === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->value === self::REJECTED;
    }
}