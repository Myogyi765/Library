<?php

namespace App\Payment\Presentation\Controller;

use App\Payment\Application\Command\SubmitPaymentCommand;
use App\Payment\Application\Handler\SubmitPaymentHandler;
use App\Payment\Presentation\Request\SubmitPaymentRequest;
use App\Payment\Infrastructure\Storage\FileUploadService;
use App\Shared\Base\BaseController;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\Admin\Application\Service\SettingsService;

class PaymentController extends BaseController
{
    private SubmitPaymentHandler $handler;
    private FileUploadService $fileUpload;
    private LoanRepositoryInterface $loanRepo;
    private SettingsService $settingsService;

    public function __construct(
        SubmitPaymentHandler $handler,
        FileUploadService $fileUpload,
        LoanRepositoryInterface $loanRepo,
        SettingsService $settingsService
    ) {
        parent::__construct();
        $this->handler = $handler;
        $this->fileUpload = $fileUpload;
        $this->loanRepo = $loanRepo;
        $this->settingsService = $settingsService;
    }

    public function showSubmitForm($loanId): void
    {
        $loanId = (int) $loanId;
        $loan = $this->loanRepo->findById($loanId);

        if (!$loan) {
            $this->view('404');
            return;
        }

        if (!isset($_SESSION['user_id']) || $loan->getUserId() !== $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Unauthorized access.';
            $this->redirect('/user-dashboard');
            return;
        }

        if ($loan->getStatus()->getValue() !== 'awaiting_payment') {
            $_SESSION['error_message'] = 'This loan is not awaiting payment.';
            $this->redirect('/user-dashboard');
            return;
        }

        $borrowingFee = $loan->getBorrowingFee() ?? $this->settingsService->getBorrowingFee();

        // 🔐 Generate a UUID v4 idempotency key
        $idempotencyKey = $this->generateUuid();

        $this->view('payment/submit', [
            'loan'           => $loan,
            'borrowingFee'   => $borrowingFee,
            'idempotencyKey' => $idempotencyKey   // pass to view
        ]);
    }

    public function submit(): void
    {
        try {
            $request = new SubmitPaymentRequest($_POST, $_FILES);

            $screenshotPath = null;
            if ($request->hasFile('screenshot')) {
                $file = $request->getFile('screenshot');
                $screenshotPath = $this->fileUpload->store($file);
            }

            $cmd = new SubmitPaymentCommand(
                loanId: $request->getLoanId(),
                userId: $_SESSION['user_id'] ?? 0,
                amount: $request->getAmount(),
                paymentMethod: $request->getPaymentMethod(),
                transactionReference: $request->getTransactionReference(),
                screenshotPath: $screenshotPath,
                idempotencyKey: $request->getIdempotencyKey()   // from hidden input
            );

            $payment = $this->handler->handle($cmd);   // now returns Payment (new or existing)

            // ✅ Optionally store payment id for later use
            $_SESSION['payment_id'] = $payment->getId();

            $_SESSION['success_message'] = 'Payment submitted successfully! The librarian will review it shortly.';
            $this->redirect('/payment/success');

        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Payment failed: ' . $e->getMessage();
            $loanId = $_POST['loan_id'] ?? 0;
            $this->redirect('/payment/submit/' . $loanId);
        }
    }

    public function success(): void
    {
        $this->view('payment/success');
    }

    /**
     * Generate a RFC 4122 compliant UUID v4.
     * You can also use ramsey/uuid if you prefer.
     */
    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}