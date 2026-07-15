<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
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

    public function show(int $id): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $invoice = $this->invoiceRepo->findById($id);
        if ($invoice === null) {
            $this->renderNotFound('Invoice not found.');
            return;
        }

        $payment = $this->paymentRepo->findById($invoice->getPaymentId());
        if ($payment === null) {
            $this->renderNotFound('Payment not found.');
            return;
        }

        if ($payment->getUserId() !== $userId) {
            $this->renderForbidden('You do not have permission to view this invoice.');
            return;
        }

        $loan = $this->loanRepo->findById($payment->getLoanId());
        if ($loan === null) {
            $this->renderNotFound('Loan not found.');
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

        $this->view('payment/invoice', $invoiceData);
    }


    private function renderNotFound(string $message): void
    {
        http_response_code(404);
        echo $message;
    }

    private function renderForbidden(string $message): void
    {
        http_response_code(403);
        echo $message;
    }
}