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
use App\Admin\Application\Service\SettingsService;

class ApprovePaymentHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepo,
        private LoanRepositoryInterface $loanRepo,
        private BookRepositoryInterface $bookRepo,
        private InvoiceRepositoryInterface $invoiceRepo,
        private SettingsService $settingsService
    ) {}

    public function handle(ApprovePaymentCommand $cmd): void
    {
        try {
            error_log("🔍 Approving payment ID: " . $cmd->paymentId);

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

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

            $isFinePayment = false;
            if (isset($_SESSION['pending_fine_loan_id']) && $_SESSION['pending_fine_loan_id'] === $loan->getId()) {
                $isFinePayment = true;
                error_log("✅ This is a fine payment for loan #{$loan->getId()}");
            }

            if ($isFinePayment) {
                $loan->returnBook();
                $this->loanRepo->save($loan);
                error_log("✅ Loan #{$loan->getId()} set to RETURNED.");

                $book = $this->bookRepo->findById($loan->getBookId());
                if ($book) {
                    $book->setAvailableQuantity($book->getAvailableQuantity() + 1);
                    $this->bookRepo->save($book);
                    error_log("✅ Book quantity increased.");
                }

                unset($_SESSION['pending_fine_loan_id']);
                unset($_SESSION['pending_fine_amount']);
                error_log("✅ Cleared session flags for fine payment.");

            } else {
                $maxDays = $this->settingsService->getMaxBorrowDays();
                error_log("📅 [ApprovePaymentHandler] maxDays = " . $maxDays);

                $now = new \DateTimeImmutable();
                $dueDate = $now->modify("+{$maxDays} days");

                $loan->setBorrowedAt($now);
                $loan->setDueDate($dueDate);
                $loan->setStatus(LoanStatus::ACTIVE());
                $this->loanRepo->save($loan);
                error_log("✅ Loan updated to ACTIVE with due_date = " . $dueDate->format('Y-m-d H:i:s'));

                $this->bookRepo->decrementQuantity($loan->getBookId(), 1);
                error_log("✅ Book quantity decreased.");

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
                    $now,
                    $dueDate
                );

                $this->invoiceRepo->save($invoice);
                error_log("✅ Invoice created with number: " . $invoice->getInvoiceNumber());
            }

        } catch (\Exception $e) {
            error_log("❌ ApprovePaymentHandler error: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}