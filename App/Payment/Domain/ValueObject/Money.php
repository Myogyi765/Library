<?php
namespace App\Payment\Domain\ValueObject;

class Money
{
    private float $amount;
    private string $currency;

    public function __construct(float $amount, string $currency = 'MMK')
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }
        $this->amount = $amount;
        $this->currency = strtoupper($currency);
    }

    public function getAmount(): float { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getValue(): float
{
    return $this->amount;
}
}