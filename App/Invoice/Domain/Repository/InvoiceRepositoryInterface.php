<?php

namespace App\Invoice\Domain\Repository;

use App\Invoice\Domain\Entity\Invoice;

interface InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): void;
    public function findById(int $id): ?Invoice;
    public function findByInvoiceNumber(string $invoiceNumber): ?Invoice;
    public function findByPaymentId(int $paymentId): ?Invoice;
    public function findByLoanId(int $loanId): array;
    public function findByUserId(int $userId): array;
}