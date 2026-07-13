<?php
namespace App\Payment\Application\Command;

class ApprovePaymentCommand
{
    public function __construct(
        public  int $paymentId,
        public  int $librarianId
    ) {}
}