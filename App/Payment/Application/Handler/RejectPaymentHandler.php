<?php
namespace App\Payment\Application\Handler;

use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Loan\Domain\Repository\LoanRepositoryInterface; 
use App\Payment\Application\Command\RejectPaymentCommand;
use App\Payment\Domain\Exception\PaymentDomainException;

class RejectPaymentHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepo,
        private LoanRepositoryInterface $loanRepo
    ) {}

    public function handle(RejectPaymentCommand $cmd): void
    {
        $payment = $this->paymentRepo->findById($cmd->paymentId);
        if (!$payment) {
            throw new PaymentDomainException('Payment not found.');
        }
        $payment->reject();
        $this->paymentRepo->save($payment);
    }
}