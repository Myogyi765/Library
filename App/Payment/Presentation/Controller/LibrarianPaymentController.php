<?php

namespace App\Payment\Presentation\Controller;

use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Payment\Application\Handler\ApprovePaymentHandler;
use App\Payment\Application\Handler\RejectPaymentHandler;
use App\Payment\Application\Command\ApprovePaymentCommand;
use App\Payment\Application\Command\RejectPaymentCommand;
use App\Shared\Base\BaseController;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Entity\Invoice;
use App\Payment\Domain\ValueObject\PaymentStatus;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter; // ✅ SVG writer – no GD needed
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Encoding\Encoding;

class LibrarianPaymentController extends BaseController
{
    private PaymentRepositoryInterface $paymentRepo;
    private ApprovePaymentHandler $approveHandler;
    private RejectPaymentHandler $rejectHandler;
    private LoanRepositoryInterface $loanRepo;
    private UserRepositoryInterface $userRepo;
    private BookRepositoryInterface $bookRepo;
    private InvoiceRepositoryInterface $invoiceRepo;

    public function __construct(
        PaymentRepositoryInterface $paymentRepo,
        ApprovePaymentHandler $approveHandler,
        RejectPaymentHandler $rejectHandler,
        LoanRepositoryInterface $loanRepo,
        UserRepositoryInterface $userRepo,
        BookRepositoryInterface $bookRepo,
        InvoiceRepositoryInterface $invoiceRepo
    ) {
        parent::__construct();
        $this->paymentRepo = $paymentRepo;
        $this->approveHandler = $approveHandler;
        $this->rejectHandler = $rejectHandler;
        $this->loanRepo = $loanRepo;
        $this->userRepo = $userRepo;
        $this->bookRepo = $bookRepo;
        $this->invoiceRepo = $invoiceRepo;
    }

    public function index(): void
    {
        $payments = $this->paymentRepo->findPendingApprovals();
        $this->view('payment/librarian/index', ['payments' => $payments]);
    }

    public function show(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $payment = $this->paymentRepo->findById($id);
        if (!$payment) {
            $this->view('404');
            return;
        }
        $this->view('payment/librarian/show', ['payment' => $payment]);
    }

    /**
     * Approve a payment and show invoice.
     * If invoice already exists, just show it; otherwise create new.
     */
    public function approve($id): void
    {
        $id = (int) $id;
        try {
            // 1️⃣ Get payment
            $payment = $this->paymentRepo->findById($id);
            if (!$payment) {
                throw new \Exception('Payment not found.');
            }

            // 2️⃣ Check if invoice already exists for this payment
            $existingInvoice = $this->invoiceRepo->findByPaymentId($id);
            if ($existingInvoice) {
                // ✅ Invoice already exists – just show it with fresh data
                $loan = $this->loanRepo->findById($payment->getLoanId());
                $user = $this->userRepo->findById($loan->getUserId());
                $book = $this->bookRepo->findById($loan->getBookId());
                $this->displayInvoice($payment, $loan, $user, $book, $existingInvoice);
                return;
            }

            if ($payment->getStatus()->equals(PaymentStatus::APPROVED())) {
                throw new \Exception('Payment is approved but no invoice found. Please contact admin.');
            }

            // 4️⃣ Only proceed if payment is pending approval
            if (!$payment->getStatus()->equals(PaymentStatus::PENDING_APPROVAL())) {
                throw new \Exception('Only pending approval can be approved. Current status: ' . $payment->getStatus()->getValue());
            }

            // 5️⃣ Approve payment
            $cmd = new ApprovePaymentCommand($id, $_SESSION['user_id'] ?? 0);
            $this->approveHandler->handle($cmd);

            // 6️⃣ Fetch related data for invoice
            $loan = $this->loanRepo->findById($payment->getLoanId());
            if (!$loan) throw new \Exception('Loan not found.');
            $user = $this->userRepo->findById($loan->getUserId());
            if (!$user) throw new \Exception('User not found.');
            $book = $this->bookRepo->findById($loan->getBookId());
            if (!$book) throw new \Exception('Book not found.');

            // 7️⃣ Generate invoice number
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($id, 6, '0', STR_PAD_LEFT);

            // 8️⃣ Create and save Invoice
            $invoice = new Invoice(
                $invoiceNumber,
                $payment->getId(),
                $loan->getId(),
                $user->getId(),
                $book->getId(),
                $payment->getAmount()->getAmount(),
                'MMK',
                $payment->getPaymentMethod(),
                $payment->getTransactionReference(),
                $loan->getBorrowedAt() ?? new \DateTimeImmutable(),
                $loan->getDueDate() ?? (new \DateTimeImmutable())->modify('+14 days')
            );
            $this->invoiceRepo->save($invoice);

            // 9️⃣ Show invoice
            $this->displayInvoice($payment, $loan, $user, $book, $invoice);

        } catch (\Exception $e) {
            $this->redirect(BASE_URL . '/librarian/payments?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Helper: Display invoice view with QR code (uses SVG – no GD required).
     */
    private function displayInvoice($payment, $loan, $user, $book, $invoice): void
    {
        // Generate QR Code using SVG writer (compatible with PHP 7.4+, no GD needed)
        $qrData = BASE_URL . '/librarian/scan?loan_id=' . $loan->getId();
        $qrCode = Builder::create()
            ->writer(new SvgWriter())
            ->data($qrData)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(150)
            ->margin(10)
            ->build();
        $qrCodeDataUri = $qrCode->getDataUri();

        $invoiceData = [
            'invoice_number' => $invoice->getInvoiceNumber(),
            'date' => (new \DateTimeImmutable())->format('d M Y'),
            'user' => $user,
            'book' => $book,
            'loan' => $loan,
            'payment' => $payment,
            'borrowed_at' => $loan->getBorrowedAt(),
            'due_date' => $loan->getDueDate(),
            'invoice' => $invoice,
            'qrCode' => $qrCodeDataUri,
        ];

        $this->view('payment/invoice', $invoiceData);
    }


    /**
 * Show invoice for a given payment ID.
 */
public function viewInvoice($id): void
{
    $id = (int) $id;
    try {
        // Fetch payment
        $payment = $this->paymentRepo->findById($id);
        if (!$payment) {
            throw new \Exception('Payment not found.');
        }

        // Fetch invoice by payment_id
        $invoice = $this->invoiceRepo->findByPaymentId($id);
        if (!$invoice) {
            throw new \Exception('Invoice not found for this payment.');
        }

        // Fetch loan, user, book
        $loan = $this->loanRepo->findById($payment->getLoanId());
        if (!$loan) throw new \Exception('Loan not found.');
        $user = $this->userRepo->findById($loan->getUserId());
        if (!$user) throw new \Exception('User not found.');
        $book = $this->bookRepo->findById($loan->getBookId());
        if (!$book) throw new \Exception('Book not found.');

        // Reuse the displayInvoice helper (which generates QR code and shows view)
        $this->displayInvoice($payment, $loan, $user, $book, $invoice);

    } catch (\Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        $this->redirect(BASE_URL . '/librarian/payments');
    }
}

    public function reject($id): void
    {
        $id = (int) $id;
        try {
            $cmd = new RejectPaymentCommand($id, $_SESSION['user_id'] ?? 0);
            $this->rejectHandler->handle($cmd);
            $this->redirect(BASE_URL . '/librarian/payments?success=' . urlencode('Payment rejected.'));
        } catch (\Exception $e) {
            $this->redirect(BASE_URL . '/librarian/payments?error=' . urlencode($e->getMessage()));
        }
    }


 
public function showRefundForm($id)
{
    $payment = $this->paymentRepo->findById($id);
    
    if (!$payment) {
        $_SESSION['error_message'] = 'Payment not found.';
        $this->redirect(BASE_URL . '/librarian/payments');
        return;
    }

    $refundStatus = $payment->getRefundStatus() ?? 'none';
    $status = $payment->getStatus()->getValue();
    
    if ($refundStatus !== 'none' || $status !== 'completed') {
        $_SESSION['error_message'] = 'This payment cannot be refunded.';
        $this->redirect(BASE_URL . '/librarian/payments');
        return;
    }

    $this->view('payment/librarian/refund', ['payment' => $payment]);
}


public function processRefund($id)
{
    $payment = $this->paymentRepo->findById($id);

    if (!$payment) {
        $_SESSION['error_message'] = 'Payment not found.';
        $this->redirect(BASE_URL . '/librarian/payments');
        return;
    }

    $refundStatus = $payment->getRefundStatus() ?? 'none';
    $status = $payment->getStatus()->getValue();

    if ($refundStatus !== 'none' || $status !== 'completed') {
        $_SESSION['error_message'] = 'Invalid refund request.';
        $this->redirect(BASE_URL . '/librarian/payments');
        return;
    }

    $payment->setRefundStatus('completed');
    $payment->setRefundedAt(new \DateTimeImmutable());
    $payment->setRefundReason($_POST['refund_reason'] ?? 'No reason provided');

    $this->paymentRepo->save($payment);

    $_SESSION['success_message'] = 'Refund processed successfully!';
    $this->redirect(BASE_URL . '/librarian/payments');
}
}