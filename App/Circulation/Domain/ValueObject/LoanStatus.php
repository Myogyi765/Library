<?php

namespace App\Circulation\Domain\ValueObject;

class LoanStatus
{
    private const PENDING = 'pending';
    private const AWAITING_PAYMENT = 'awaiting_payment';
    private const ACTIVE = 'active';
    private const RETURNED = 'returned';
    private const REJECTED = 'rejected';
    private const OVERDUE = 'overdue';

    private string $value;

    private function __construct(string $value)
    {
        if (!in_array($value, [self::PENDING, self::AWAITING_PAYMENT, self::ACTIVE, self::RETURNED, self::REJECTED, self::OVERDUE])) {
            throw new \InvalidArgumentException("Invalid loan status: {$value}");
        }
        $this->value = $value;
    }

    public static function PENDING(): self { return new self(self::PENDING); }
    public static function AWAITING_PAYMENT(): self { return new self(self::AWAITING_PAYMENT); }
    public static function ACTIVE(): self { return new self(self::ACTIVE); }
    public static function RETURNED(): self { return new self(self::RETURNED); }
    public static function REJECTED(): self { return new self(self::REJECTED); }
    public static function OVERDUE(): self { return new self(self::OVERDUE); }

    public static function from(string $value): self { return new self($value); }

    public function getValue(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    public function isPending(): bool { return $this->value === self::PENDING; }
    public function isActive(): bool { return $this->value === self::ACTIVE; }
    public function isRejected(): bool { return $this->value === self::REJECTED; }
    public function isAwaitingPayment(): bool { return $this->value === self::AWAITING_PAYMENT; }
}