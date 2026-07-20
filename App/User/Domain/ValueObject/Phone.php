<?php

namespace App\User\Domain\ValueObject;

use App\User\Exception\InvalidPhoneException;

class Phone
{
    private ?string $value;

    public function __construct(?string $value)
    {
        if ($value === null || $value === '' || $value === '+95000000000') {
            $this->value = null;
            return;
        }

        if (!preg_match('/^(09|\+95)[0-9]{7,10}$/', $value)) {
            throw new InvalidPhoneException(
                "Invalid phone number format: {$value}. Must be 09... or +95... (e.g., 09999999999 or +959999999999)"
            );
        }

        $this->value = $value;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return $this->value === null;
    }

    public function __toString(): string
    {
        return $this->value ?? '';
    }

    public function equals(Phone $other): bool
    {
        return $this->value === $other->getValue();
    }
}
