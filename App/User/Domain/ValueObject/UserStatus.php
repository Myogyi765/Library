<?php

namespace App\User\Domain\ValueObject;

class UserStatus
{
    public const PENDING = 'pending';
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';  
    public const SUSPENDED = 'suspended';
    public const BANNED = 'banned';

    private string $value;

    public function __construct(string $value)
    {
        // ✅ Include INACTIVE in allowed values
        if (!in_array($value, [self::PENDING, self::ACTIVE, self::INACTIVE, self::SUSPENDED, self::BANNED])) {
            throw new \InvalidArgumentException("Invalid user status: $value");
        }
        $this->value = $value;
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function inactive(): self  
    {
        return new self(self::INACTIVE);
    }

    public static function suspended(): self
    {
        return new self(self::SUSPENDED);
    }

    public static function banned(): self
    {
        return new self(self::BANNED);
    }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}