<?php

namespace App\Payment\Presentation\Request;

class SubmitPaymentRequest
{
    private array $data;
    private array $files;

    public function __construct(array $data, array $files = [])
    {
        $this->data = $data;
        $this->files = $files;
        $this->validate();
    }

    public function getLoanId(): int
    {
        return (int) ($this->data['loan_id'] ?? 0);
    }

    public function getAmount(): float
    {
        return (float) ($this->data['amount'] ?? 0);
    }

    public function getPaymentMethod(): string
    {
        return $this->data['payment_method'] ?? '';
    }

    public function getTransactionReference(): string
    {
        return trim($this->data['transaction_reference'] ?? '');
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function getFile(string $key): array
    {
        return $this->files[$key] ?? [];
    }


     public function getIdempotencyKey(): string
    {
        return trim($this->data['idempotency_key'] ?? '');
    }

    private function validate(): void
    {
        $errors = [];
        
        if (empty($this->getLoanId())) {
            $errors[] = 'Loan ID is required.';
        }
        if ($this->getAmount() <= 0) {
            $errors[] = 'Amount must be greater than 0.';
        }

          if (empty($this->getIdempotencyKey())) {
            $errors[] = 'Idempotency key is required.';
        }
        if (!in_array($this->getPaymentMethod(), ['kpay', 'wavepay'])) {
            $errors[] = 'Payment method must be kpay or wavepay.';
        }
        if (empty($this->getTransactionReference())) {
            $errors[] = 'Transaction reference is required.';
        }
        if (!$this->hasFile('screenshot')) {
            $errors[] = 'Screenshot is required.';
        }
        
        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode(' ', $errors));
        }
    }
}