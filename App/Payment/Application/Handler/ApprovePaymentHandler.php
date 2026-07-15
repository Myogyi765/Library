<?php

namespace App\Payment\Application\Handler;

use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Entity\Invoice;
use App\Circulation\Domain\ValueObject\LoanStatus;
use App\Payment\Application\Command\ApprovePaymentCommand;
use App\Payment\Domain\Exception\PaymentDomainException;

class ApprovePaymentHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepo,
        private LoanRepositoryInterface $loanRepo,
        private BookRepositoryInterface $bookRepo,
        private InvoiceRepositoryInterface $invoiceRepo
    ) {}

    public function handle(ApprovePaymentCommand $cmd): void
    {
        try {
            error_log("🔍 Approving payment ID: " . $cmd->paymentId);

            $payment = $this->paymentRepo->findById($cmd->paymentId);
            if (!$payment) {
                throw new PaymentDomainException('Payment not found.');
            }

            $payment->approve();
            $this->paymentRepo->save($payment);
            error_log("✅ Payment approved and saved.");

            $loan = $this->loanRepo->findById($payment->getLoanId());
            if (!$loan) {
                throw new PaymentDomainException('Loan not found.');
            }
            $loan->setBorrowedAt(new \DateTimeImmutable());
            $loan->setDueDate((new \DateTimeImmutable())->modify('+14 days'));
            $loan->setStatus(LoanStatus::ACTIVE());
            $this->loanRepo->save($loan);
            error_log("✅ Loan updated to ACTIVE.");

            $this->bookRepo->decrementQuantity($loan->getBookId(), 1);
            error_log("✅ Book quantity decreased.");

            // Create Invoice
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($payment->getId(), 6, '0', STR_PAD_LEFT);
            $invoice = new Invoice(
                $invoiceNumber,
                $payment->getId(),
                $loan->getId(),
                $payment->getUserId(),
                $loan->getBookId(),
                $payment->getAmount()->getAmount(),
                'MMK',
                $payment->getPaymentMethod(),
                $payment->getTransactionReference(),
                $loan->getBorrowedAt() ?? new \DateTimeImmutable(),
                $loan->getDueDate()
            );

            $this->invoiceRepo->save($invoice);
            error_log("✅ Invoice created with number: " . $invoice->getInvoiceNumber());

        } catch (\Exception $e) {
            error_log("❌ ApprovePaymentHandler error: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}