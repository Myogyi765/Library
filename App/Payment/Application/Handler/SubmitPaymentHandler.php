<?php

namespace App\Payment\Application\Handler;

use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\ValueObject\Money;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Payment\Application\Command\SubmitPaymentCommand;

class SubmitPaymentHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepo,
        private LoanRepositoryInterface $loanRepo,
        private BookRepositoryInterface $bookRepo,
    ) {}

    /**
     * Handles the submission – returns existing payment if idempotency key already used,
     * otherwise creates and saves a new payment.
     */
    public function handle(SubmitPaymentCommand $cmd): Payment   // ✅ changed from void to Payment
    {
        // 1️⃣ Check if a payment with this idempotency key already exists
        $existing = $this->paymentRepo->findByIdempotencyKey($cmd->idempotencyKey);
        if ($existing) {
            return $existing;
        }

        // 2️⃣ Create new payment record
        $money = new Money($cmd->amount, 'MMK');
        $payment = new Payment(
            loanId: $cmd->loanId,
            userId: $cmd->userId,
            amount: $money,
            paymentMethod: $cmd->paymentMethod,
            transactionReference: $cmd->transactionReference,
            screenshotPath: $cmd->screenshotPath,
            idempotencyKey: $cmd->idempotencyKey
        );
        $this->paymentRepo->save($payment);

        return $payment;
    }
}