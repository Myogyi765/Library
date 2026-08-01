<?php

declare(strict_types=1);

namespace App\Invoice\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Invoice\Domain\Entity\Invoice;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

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

        // ✅ FIX: Search by Invoice ID first (most common usage)
        $invoice = $this->invoiceRepo->findById($id);

        // If not found, try as Payment ID (for backward compatibility)
        if ($invoice === null) {
            $invoice = $this->invoiceRepo->findByPaymentId($id);
        }

        // If still not found, try to create invoice from payment
        if ($invoice === null) {
            $payment = $this->paymentRepo->findById($id);
            if ($payment) {
                $status = $payment->getStatus()->getValue();
                if (in_array($status, ['approved', 'completed'])) {
                    $loan = $this->loanRepo->findById($payment->getLoanId());
                    if ($loan) {
                        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad((string)$payment->getId(), 6, '0', STR_PAD_LEFT);
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
                            $loan->getBorrowedAt() ?? new \DateTimeImmutable(),
                            $loan->getDueDate() ?? (new \DateTimeImmutable())->modify('+14 days')
                        );
                        $this->invoiceRepo->save($invoice);
                    }
                }
            }
        }

        if ($invoice === null) {
            $this->renderNotFound('Invoice not found.');
            return;
        }

        $payment = $this->paymentRepo->findById($invoice->getPaymentId());
        if ($payment === null || $payment->getUserId() !== $userId) {
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

        // ✅ Generate QR Code with URL to scanner page
        $qrCode = null;
        try {
            // Build a URL that the scanner can use to fetch the loan details
            $qrData = BASE_URL . '/librarian/scan?loan_id=' . $loan->getId();

            $qrResult = Builder::create()
                ->writer(new SvgWriter())
                ->data($qrData)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->size(150)
                ->margin(10)
                ->build();

            $qrCode = $qrResult->getDataUri();
        } catch (\Exception $e) {
            error_log('❌ QR Code generation failed: ' . $e->getMessage());
            $qrCode = null;
        }

        $invoiceData = [
            'invoice_number' => $invoice->getInvoiceNumber(),
            'date'           => $invoice->getIssuedAt()->format('d M Y'),
            'user'           => $user,
            'book'           => $book,
            'loan'           => $loan,
            'payment'        => $payment,
            'borrowed_at'    => $loan->getBorrowedAt(),
            'due_date'       => $loan->getDueDate(),
            'invoice'        => $invoice,
            'qrCode'         => $qrCode,
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