<?php

namespace App\Payment\Presentation\Request;

class LibrarianPaymentActionRequest
{
    private array $data;
    private array $session;

    public function __construct(array $data = [], array $session = [])
    {
        $this->data = $data;
        $this->session = $session;
        $this->validate();
    }

    public function authorize(): bool
    {
        return isset($this->session['user_id']) && isset($this->session['role']) && $this->session['role'] === 'librarian';
    }

    public function getPaymentId(): int
    {
        return (int) ($this->data['payment_id'] ?? 0);
    }


    private function validate(): void
    {
        if (!$this->authorize()) {
            throw new \RuntimeException('Unauthorized: Librarian access required.');
        }
        if ($this->getPaymentId() <= 0) {
            throw new \InvalidArgumentException('Payment ID is required.');
        }
    }
}