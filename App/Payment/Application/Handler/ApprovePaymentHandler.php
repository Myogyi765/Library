<?php
namespace App\Payment\Application\Handler;

use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Loan\Domain\ValueObject\LoanStatus;
use App\Payment\Application\Command\ApprovePaymentCommand;
use App\Payment\Domain\Exception\PaymentDomainException;

class ApprovePaymentHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepo,
        private LoanRepositoryInterface $loanRepo,
        private BookRepositoryInterface $bookRepo   // ✅ injected
    ) {}

    public function handle(ApprovePaymentCommand $cmd): void
    {
        $payment = $this->paymentRepo->findById($cmd->paymentId);
        if (!$payment) {
            throw new PaymentDomainException('Payment not found.');
        }
        $payment->approve();
        $this->paymentRepo->save($payment);

        $loan = $this->loanRepo->findById($payment->getLoanId());
        if (!$loan) {
            throw new PaymentDomainException('Loan not found.');
        }
        $loan->setBorrowedAt(new \DateTimeImmutable());
        $loan->setDueDate((new \DateTimeImmutable())->modify('+14 days'));
        $loan->setStatus(LoanStatus::ACTIVE());
        $this->loanRepo->save($loan);

        // ✅ Decrease book availability
        $bookId = $loan->getBookId();
        $this->bookRepo->decrementQuantity($bookId, 1);
    }
}