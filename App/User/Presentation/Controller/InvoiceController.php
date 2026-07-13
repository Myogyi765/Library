<?php

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;

class InvoiceController extends BaseController
{
    private InvoiceRepositoryInterface $invoiceRepo;
    private PaymentRepositoryInterface $paymentRepo;
    private LoanRepositoryInterface $loanRepo;
    private UserRepositoryInterface $userRepo;
    private BookRepositoryInterface $bookRepo;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepo,
        PaymentRepositoryInterface $paymentRepo,
        LoanRepositoryInterface $loanRepo,
        UserRepositoryInterface $userRepo,
        BookRepositoryInterface $bookRepo
    ) {
        parent::__construct();
        $this->invoiceRepo = $invoiceRepo;
        $this->paymentRepo = $paymentRepo;
        $this->loanRepo = $loanRepo;
        $this->userRepo = $userRepo;
        $this->bookRepo = $bookRepo;
    }

    public function show($id): void
    {
        $invoice = $this->invoiceRepo->findById((int)$id);
        if (!$invoice) {
            http_response_code(404);
            echo 'Invoice not found.';
            return;
        }

        $payment = $this->paymentRepo->findById($invoice->getPaymentId());
        if (!$payment) {
            http_response_code(404);
            echo 'Payment not found.';
            return;
        }

        // Check if this invoice belongs to the logged-in user
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($payment->getUserId() !== $userId) {
            http_response_code(403);
            echo 'You do not have permission to view this invoice.';
            return;
        }

        $loan = $this->loanRepo->findById($payment->getLoanId());
        if (!$loan) {
            http_response_code(404);
            echo 'Loan not found.';
            return;
        }

        $user = $this->userRepo->findById($userId);
        $book = $this->bookRepo->findById($loan->getBookId());

        $invoiceData = [
            'invoice_number' => $invoice->getInvoiceNumber(),
            'date' => $invoice->getIssuedAt()->format('d M Y'),
            'user' => $user,
            'book' => $book,
            'loan' => $loan,
            'payment' => $payment,
            'borrowed_at' => $loan->getBorrowedAt(),
            'due_date' => $loan->getDueDate(),
            'invoice' => $invoice,
        ];

        // Reuse the existing invoice view (payment/invoice.php)
        $this->view('payment/invoice', $invoiceData);
    }
}