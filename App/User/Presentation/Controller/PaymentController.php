<?php

namespace App\Payment\Presentation\Controller;

use App\Payment\Application\Command\SubmitPaymentCommand;
use App\Payment\Application\Handler\SubmitPaymentHandler;
use App\Payment\Presentation\Request\SubmitPaymentRequest; 
use App\Payment\Infrastructure\Storage\FileUploadService;
use App\Shared\Base\BaseController;
use App\Loan\Domain\Repository\LoanRepositoryInterface;

class PaymentController extends BaseController
{
    private SubmitPaymentHandler $handler;
    private FileUploadService $fileUpload;
    private LoanRepositoryInterface $loanRepo;

    public function __construct(
        SubmitPaymentHandler $handler,
        FileUploadService $fileUpload,
        LoanRepositoryInterface $loanRepo
    ) {
        parent::__construct();
        $this->handler = $handler;
        $this->fileUpload = $fileUpload;
        $this->loanRepo = $loanRepo;
    }

    public function showSubmitForm(int $loanId): void
    {
        $loan = $this->loanRepo->findById($loanId);
        
        if (!$loan) {
            header('Location: ' . BASE_URL . '/librarian/loans');
            exit;
        }

        $this->view('payment/submit', ['loan' => $loan]);
    }

    public function submit(SubmitPaymentRequest $request): void
    {
        $screenshotPath = null;

        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $screenshotPath = $this->fileUpload->store($_FILES['screenshot']);
        }

        $loanId = (int) ($_POST['loan_id'] ?? 0);
        $amount = (float) ($_POST['amount'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? '';
        $transactionReference = $_POST['transaction_reference'] ?? '';

        $cmd = new SubmitPaymentCommand(
            loanId: $loanId,
            userId: (int) ($_SESSION['user_id'] ?? 0), 
            amount: $amount,
            paymentMethod: $paymentMethod,
            transactionReference: $transactionReference,
            screenshotPath: $screenshotPath
        );

        $this->handler->handle($cmd);

        $_SESSION['flash_success'] = 'Your payment proof has been submitted successfully. Please wait while an administrator reviews and verifies your payment.';
        header('Location: ' . BASE_URL . '/payment/success');
        exit;
    }

    public function success(): void
    {
        $this->view('payment/success');
    }
}