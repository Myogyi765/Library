<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Payment\Application\Command\SubmitPaymentCommand;
use App\Payment\Application\Handler\SubmitPaymentHandler;
use App\Payment\Presentation\Request\SubmitPaymentRequest;
use App\Payment\Infrastructure\Storage\FileUploadService;
use App\Shared\Base\BaseController;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Admin\Application\Service\SettingsService;
use App\Payment\Domain\Entity\Payment;

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

    public function showSubmitForm(int $loanId): void
    {
        $loan = $this->loanRepo->findById($loanId);

        if (!$loan) {
            $this->view('404');
            return;
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0 || $loan->getUserId() !== $userId) {
            $_SESSION['error_message'] = 'Unauthorized access.';
            $this->redirect(BASE_URL . '/user-dashboard');
            return;
        }

        $status = $loan->getStatus()->getValue();
        if ($status !== 'awaiting_payment') {
            $statusMessages = [
                'pending'   => 'Your borrow request is still pending approval by the librarian.',
                'active'    => 'You have already borrowed this book.',
                'rejected'  => 'Your borrow request was rejected.',
                'returned'  => 'This loan has already been returned.',
            ];
            $message = $statusMessages[$status] ?? 'This loan is not awaiting payment.';
            
            $_SESSION['error_message'] = $message;
            $this->redirect(BASE_URL . '/books/' . $loan->getBookId());
            return;
        }

        $borrowingFee = $loan->getBorrowingFee() ?? $this->settingsService->getBorrowingFee();
        $idempotencyKey = $this->generateUuid();

        $this->view('payment/submit', [
            'loan'           => $loan,
            'borrowingFee'   => $borrowingFee,
            'idempotencyKey' => $idempotencyKey,
        ]);
    }

    public function submit(): void
    {
        try {
            $request = new SubmitPaymentRequest($_POST, $_FILES);

            $screenshotPath = null;
            if ($request->hasFile('screenshot')) {
                $screenshotPath = $this->fileUpload->store($request->getFile('screenshot'));
            }

            $command = new SubmitPaymentCommand(
                $request->getLoanId(),
                (int) ($_SESSION['user_id'] ?? 0),
                $request->getAmount(),
                $request->getPaymentMethod(),
                $request->getTransactionReference(),
                $screenshotPath,
                $request->getIdempotencyKey()
            );

            $payment = $this->handler->handle($command);

            if ($payment instanceof Payment) {
                $_SESSION['payment_id'] = $payment->getId();

                $this->createNotification(
                    null,                  
                    'librarian',            
                    'payment_submitted',
                    'Payment submitted',
                    'A user has submitted a payment for review.',
                    BASE_URL . '/librarian/dashboard?page=payments' 
                );
            }

            $_SESSION['success_message'] = 'Payment submitted successfully! The librarian will review it shortly.';
            $this->redirect(BASE_URL . '/payment/success');

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            $this->redirect(BASE_URL . '/payment/submit/' . ($_POST['loan_id'] ?? 0));

        } catch (\DomainException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            $this->redirect(BASE_URL . '/payment/submit/' . ($_POST['loan_id'] ?? 0));

        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Payment submission failed. Please try again later.';
            $this->redirect(BASE_URL . '/payment/submit/' . ($_POST['loan_id'] ?? 0));
        }
    }

    public function success(): void
    {
        $this->view('payment/success');
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}