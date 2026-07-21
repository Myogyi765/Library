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
use App\Payment\Domain\ValueObject\PaymentStatus;
use App\Admin\Application\Service\SettingsService;
use App\Notification\Application\Service\NotificationService;
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
    private SettingsService $settingsService;
    private NotificationService $notificationService;

    public function __construct(
        PaymentRepositoryInterface $paymentRepo,
        ApprovePaymentHandler $approveHandler,
        RejectPaymentHandler $rejectHandler,
        LoanRepositoryInterface $loanRepo,
        UserRepositoryInterface $userRepo,
        BookRepositoryInterface $bookRepo,
        InvoiceRepositoryInterface $invoiceRepo,
        SettingsService $settingsService,
        NotificationService $notificationService
    ) {
        parent::__construct();
        $this->paymentRepo = $paymentRepo;
        $this->approveHandler = $approveHandler;
        $this->rejectHandler = $rejectHandler;
        $this->loanRepo = $loanRepo;
        $this->userRepo = $userRepo;
        $this->bookRepo = $bookRepo;
        $this->invoiceRepo = $invoiceRepo;
        $this->settingsService = $settingsService;
        $this->notificationService = $notificationService;
        $this->setViewBasePath(BASE_PATH . '/view/');
    }

    public function index(): void
    {
        $statusFilter = $_GET['status'] ?? 'all';

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

        $pageTitle = 'Payments';
        $content = BASE_PATH . '/view/librarian/payments/index.php';
        $data = [
            'payments' => $payments,
            'currentFilter' => $statusFilter,
        ];
        extract($data);
        include BASE_PATH . '/view/librarian-dashboard.php';
    }

    public function show($id): void
    {
        $id = (int) $id;
        $payment = $this->paymentRepo->findById($id);
        if (!$payment) {
            $this->view('404');
            return;
        }
        $this->view('payment/librarian/show', ['payment' => $payment]);
    }

    public function approve($id): void
    {
        $id = (int) $id;
        try {
            $cmd = new ApprovePaymentCommand($id, $_SESSION['user_id'] ?? 0);
            $this->approveHandler->handle($cmd);

            $this->sendApprovalNotification($id);

            $this->redirect(BASE_URL . '/librarian/payments/invoice/' . $id);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect(BASE_URL . '/librarian/payments');
        }
    }

    public function reject($id): void
    {
        $id = (int) $id;
        try {
            $cmd = new RejectPaymentCommand($id, $_SESSION['user_id'] ?? 0);
            $this->rejectHandler->handle($cmd);

            $this->sendRejectionNotification($id);

            $_SESSION['flash_success'] = 'Payment rejected.';
            $this->redirect(BASE_URL . '/librarian/payments');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect(BASE_URL . '/librarian/payments');
        }
    }

    private function sendApprovalNotification(int $paymentId): void
    {
        try {
            $payment = $this->paymentRepo->findById($paymentId);
            if (!$payment) {
                return;
            }

            $loan = $this->loanRepo->findById($payment->getLoanId());
            if (!$loan) {
                return;
            }

            $book = $this->bookRepo->findById($loan->getBookId());
            $bookTitle = $book ? $book->getTitle() : 'Unknown Book';

            $userId = $loan->getUserId();

            $this->notificationService->createNotification(
                $userId,
                'user',
                'payment_approved',
                '✅ Payment Approved',
                'Your payment has been approved for book: "' . $bookTitle . '".',
                BASE_URL . '/payment/invoice/' . $paymentId
            );

            error_log("✅ Approval notification sent to User ID: {$userId} for Payment ID: {$paymentId}");
        } catch (\Exception $e) {
            error_log("❌ Failed to send approval notification: " . $e->getMessage());
        }
    }

    private function sendRejectionNotification(int $paymentId): void
    {
        try {
            $payment = $this->paymentRepo->findById($paymentId);
            if (!$payment) {
                return;
            }

            $loan = $this->loanRepo->findById($payment->getLoanId());
            if (!$loan) {
                return;
            }

            $book = $this->bookRepo->findById($loan->getBookId());
            $bookTitle = $book ? $book->getTitle() : 'Unknown Book';

            $userId = $loan->getUserId();

            $this->notificationService->createNotification(
                $userId,
                'user',
                'payment_rejected',
                '❌ Payment Rejected',
                'Your payment for book: "' . $bookTitle . '" has been rejected. Please contact support.',
                BASE_URL . '/payment/submit/' . $paymentId
            );

            error_log("✅ Rejection notification sent to User ID: {$userId} for Payment ID: {$paymentId}");
        } catch (\Exception $e) {
            error_log("❌ Failed to send rejection notification: " . $e->getMessage());
        }
    }

    public function viewInvoice($id): void
    {
        $id = (int) $id;
        try {
            $payment = $this->paymentRepo->findById($id);
            if (!$payment) {
                throw new \Exception('Payment not found.');
            }

            $invoice = $this->invoiceRepo->findByPaymentId($id);

            if (!$invoice) {
                if ($payment->getStatus()->getValue() !== 'completed') {
                    throw new \Exception('Invoice can only be generated for approved payments.');
                }

                $loan = $this->loanRepo->findById($payment->getLoanId());
                if (!$loan) throw new \Exception('Loan not found.');

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

            $loan = $this->loanRepo->findById($payment->getLoanId());
            if (!$loan) throw new \Exception('Loan not found.');
            $user = $this->userRepo->findById($loan->getUserId());
            if (!$user) throw new \Exception('User not found.');
            $book = $this->bookRepo->findById($loan->getBookId());
            if (!$book) throw new \Exception('Book not found.');

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

    public function showRefundForm($id): void
    {
        $settings = $this->settingsService->getSettings();
        if (empty($settings['enable_refunds'])) {
            $_SESSION['error_message'] = 'Refund feature is currently disabled.';
            $this->redirect(BASE_URL . '/librarian/payments');
            return;
        }

        $payment = $this->paymentRepo->findById($id);
        if (!$payment) {
            $_SESSION['error_message'] = 'Payment not found.';
            $this->redirect(BASE_URL . '/librarian/payments');
            return;
        }

        $refundStatus = $payment->getRefundStatus() ?? 'none';
        $status = $payment->getStatus()->getValue();

        if ($refundStatus !== 'none' || !in_array($status, ['completed', 'approved'])) {
            $_SESSION['error_message'] = 'This payment cannot be refunded.';
            $this->redirect(BASE_URL . '/librarian/payments');
            return;
        }

        $this->view('payment/librarian/refund', ['payment' => $payment]);
    }

    public function processRefund($id): void
    {
        $settings = $this->settingsService->getSettings();
        if (empty($settings['enable_refunds'])) {
            $_SESSION['error_message'] = 'Refund feature is currently disabled.';
            $this->redirect(BASE_URL . '/librarian/payments');
            return;
        }

        $payment = $this->paymentRepo->findById($id);
        if (!$payment) {
            $_SESSION['error_message'] = 'Payment not found.';
            $this->redirect(BASE_URL . '/librarian/payments');
            return;
        }

        $refundStatus = $payment->getRefundStatus() ?? 'none';
        $status = $payment->getStatus()->getValue();

        if ($refundStatus !== 'none' || !in_array($status, ['completed', 'approved'])) {
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