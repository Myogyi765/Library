<?php

namespace App\Payment\Presentation\Controller;

use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Payment\Application\Handler\ApprovePaymentHandler;
use App\Payment\Application\Handler\RejectPaymentHandler;
use App\Payment\Application\Command\ApprovePaymentCommand;
use App\Payment\Application\Command\RejectPaymentCommand;
use App\Shared\Base\BaseController;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Entity\Invoice;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
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

    /**
     * List all payments with filtering by status.
     * Supports query param ?status=all|pending|approved|rejected (default: all).
     */
    public function index(): void
    {
        $statusFilter = $_GET['status'] ?? 'all';

        // Fetch payments based on filter using repository methods
        switch ($statusFilter) {
            case 'pending':
                $payments = $this->paymentRepo->findPendingApprovalsWithDetails();
                break;
            case 'approved':
                $payments = $this->paymentRepo->findByStatusWithDetails('completed');
                break;
            case 'rejected':
                $payments = $this->paymentRepo->findByStatusWithDetails('rejected');
                break;
            default:
                $payments = $this->paymentRepo->findAllWithDetails();
                break;
        }

        $this->view('payment/librarian/index', [
            'payments'       => $payments,
            'currentFilter'  => $statusFilter,
        ]);
    }

    /**
     * Show single payment details.
     */
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
     * Approve a payment and redirect to the invoice page.
     */
    public function approve(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);

        try {
            $cmd = new ApprovePaymentCommand($id, $_SESSION['user_id'] ?? 0);
            $this->approveHandler->handle($cmd);

            // ✅ Fixed: Redirect to the correct invoice route
            $this->redirect(BASE_URL . '/librarian/payments/invoice/' . $id);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect(BASE_URL . '/librarian/payments');
        }
    }

    /**
     * Reject a payment.
     */
    public function reject(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);

        try {
            $cmd = new RejectPaymentCommand($id, $_SESSION['user_id'] ?? 0);
            $this->rejectHandler->handle($cmd);

            $_SESSION['flash_success'] = 'Payment has been rejected successfully.';
            $this->redirect(BASE_URL . '/librarian/payments');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect(BASE_URL . '/librarian/payments');
        }
    }

    /**
     * Display invoice for a given payment ID.
     * If invoice doesn't exist (e.g., old payments), generate it on the fly.
     */
    public function viewInvoice(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);

        try {
            $payment = $this->paymentRepo->findById($id);
            if (!$payment) {
                throw new \Exception('Payment not found.');
            }

            // Try to find existing invoice
            $invoice = $this->invoiceRepo->findByPaymentId($id);
            if (!$invoice) {
                // ✅ Generate invoice on the fly for approved payments
                if ($payment->getStatus()->getValue() !== 'completed') {
                    throw new \Exception('Invoice can only be generated for approved payments.');
                }

                $loan = $this->loanRepo->findById($payment->getLoanId());
                if (!$loan) {
                    throw new \Exception('Loan not found.');
                }

                $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($id, 6, '0', STR_PAD_LEFT);
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

            // Fetch related data for the invoice view
            $loan = $this->loanRepo->findById($payment->getLoanId());
            if (!$loan) {
                throw new \Exception('Loan not found.');
            }
            $user = $this->userRepo->findById($loan->getUserId());
            if (!$user) {
                throw new \Exception('User not found.');
            }
            $book = $this->bookRepo->findById($loan->getBookId());
            if (!$book) {
                throw new \Exception('Book not found.');
            }

            // Generate QR Code (using SVG writer – no GD required)
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
                'date'           => (new \DateTimeImmutable())->format('d M Y'),
                'user'           => $user,
                'book'           => $book,
                'loan'           => $loan,
                'payment'        => $payment,
                'borrowed_at'    => $loan->getBorrowedAt(),
                'due_date'       => $loan->getDueDate(),
                'invoice'        => $invoice,
                'qrCode'         => $qrCodeDataUri,
            ];

            $this->view('payment/invoice', $invoiceData);

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect(BASE_URL . '/librarian/payments');
        }
    }
}