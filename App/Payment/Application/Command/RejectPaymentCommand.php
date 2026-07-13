<?php
namespace App\Payment\Application\Command;

class RejectPaymentCommand
{
    public function __construct(
        public  int $paymentId,
        public  int $librarianId
    ) {}
}