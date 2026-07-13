<?php
namespace App\Payment\Infrastructure\Mapper;

use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\ValueObject\Money;
use App\Payment\Domain\ValueObject\PaymentStatus;

class PaymentMapper
{
    public function toDomain(array $data): Payment
    {
        $money = new Money((float)$data['amount'], $data['currency']);
        $payment = new Payment(
            loanId: (int)$data['loan_id'],
            userId: (int)$data['user_id'],
            amount: $money,
            paymentMethod: $data['payment_method'],
            transactionReference: $data['transaction_reference'],
            screenshotPath: $data['screenshot_path'] ?? null,
            idempotencyKey: $data['idempotency_key'] ?? null
        );
        $payment->setId((int)$data['id']);
        $payment->setStatus(PaymentStatus::from($data['status']));

        if (!empty($data['submitted_at'])) {
            $payment->setSubmittedAt(new \DateTimeImmutable($data['submitted_at']));
        }
        if (!empty($data['approved_at'])) {
            $payment->setApprovedAt(new \DateTimeImmutable($data['approved_at']));
        }
        if (!empty($data['rejected_at'])) {
            $payment->setRejectedAt(new \DateTimeImmutable($data['rejected_at']));
        }

        // ============ ✅ Refund Fields Mapping ============
        if (isset($data['refund_status'])) {
            $payment->setRefundStatus($data['refund_status']);
        }
        if (!empty($data['refunded_at'])) {
            $payment->setRefundedAt(new \DateTimeImmutable($data['refunded_at']));
        }
        if (isset($data['refund_reason'])) {
            $payment->setRefundReason($data['refund_reason']);
        }

        return $payment;
    }

    public function toPersistence(Payment $payment): array
    {
        return [
            'id' => $payment->getId(),
            'loan_id' => $payment->getLoanId(),
            'user_id' => $payment->getUserId(),
            'amount' => $payment->getAmount()->getAmount(),
            'currency' => $payment->getAmount()->getCurrency(),
            'status' => $payment->getStatus()->getValue(),
            'payment_method' => $payment->getPaymentMethod(),
            'transaction_reference' => $payment->getTransactionReference(),
            'screenshot_path' => $payment->getScreenshotPath(),
            'submitted_at' => $payment->getSubmittedAt()?->format('Y-m-d H:i:s'),
            'approved_at' => $payment->getApprovedAt()?->format('Y-m-d H:i:s'),
            'rejected_at' => $payment->getRejectedAt()?->format('Y-m-d H:i:s'),
            'idempotency_key' => $payment->getIdempotencyKey(),

            // ============ ✅ Refund Fields for Persistence ============
            'refund_status' => $payment->getRefundStatus(),
            'refunded_at' => $payment->getRefundedAt()?->format('Y-m-d H:i:s'),
            'refund_reason' => $payment->getRefundReason(),
        ];
    }
}