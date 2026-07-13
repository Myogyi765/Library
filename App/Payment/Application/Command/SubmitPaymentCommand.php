<?php
namespace App\Payment\Application\Command;

class SubmitPaymentCommand
{
    public function __construct(
        public  int $loanId,
        public  int $userId,
        public  float $amount,
        public  string $paymentMethod, // 'kpay' or 'wavepay'
        public  string $transactionReference,
        public  ?string $screenshotPath = null,

          public string $idempotencyKey 
    ) {}
}