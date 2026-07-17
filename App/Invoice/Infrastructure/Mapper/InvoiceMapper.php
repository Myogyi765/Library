<?php

namespace App\Invoice\Infrastructure\Mapper;

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\ValueObject\InvoiceStatus;

class InvoiceMapper
{
    public function toDomain(array $data): Invoice
    {
        $invoice = new Invoice(
            $data['invoice_number'],
            (int) $data['payment_id'],
            (int) $data['loan_id'],
            (int) $data['user_id'],
            (int) $data['book_id'],
            (float) $data['amount'],
            $data['currency'] ?? 'MMK',
            $data['payment_method'],
            $data['transaction_reference'],
            new \DateTimeImmutable($data['borrowed_at']),
            new \DateTimeImmutable($data['due_date'])
        );

        if (isset($data['id'])) {
            $invoice->setId((int) $data['id']);
        }

        if (isset($data['status'])) {
            $invoice->setStatus($this->getStatusFromString($data['status']));
        }

        if (isset($data['issued_at'])) {
            $invoice->setIssuedAt(new \DateTimeImmutable($data['issued_at']));
        }

        return $invoice;
    }

    public function toPersistence(Invoice $invoice): array
    {
        return [
            'id' => $invoice->getId(),
            'invoice_number' => $invoice->getInvoiceNumber(),
            'payment_id' => $invoice->getPaymentId(),
            'loan_id' => $invoice->getLoanId(),
            'user_id' => $invoice->getUserId(),
            'book_id' => $invoice->getBookId(),
            'amount' => $invoice->getAmount(),
            'currency' => $invoice->getCurrency(),
            'payment_method' => $invoice->getPaymentMethod(),
            'transaction_reference' => $invoice->getTransactionReference(),
            'borrowed_at' => $invoice->getBorrowedAt()->format('Y-m-d H:i:s'),
            'due_date' => $invoice->getDueDate()->format('Y-m-d H:i:s'),
            'issued_at' => $invoice->getIssuedAt()->format('Y-m-d H:i:s'), 
            'status' => $invoice->getStatus()->getValue(),
        ];
    }

    
    private function getStatusFromString(string $status): InvoiceStatus
    {
        $status = strtolower($status);
        if ($status === 'issued') {
            return InvoiceStatus::ISSUED();
        }
        if ($status === 'cancelled') {
            return InvoiceStatus::CANCELLED();
        }
        return InvoiceStatus::ISSUED();
    }
}
