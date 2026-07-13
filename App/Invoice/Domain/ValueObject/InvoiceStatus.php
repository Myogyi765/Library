<?php

namespace App\Invoice\Domain\ValueObject;

class InvoiceStatus
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function ISSUED(): self
    {
        return new self('issued');
    }

    public static function CANCELLED(): self
    {
        return new self('cancelled');
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}