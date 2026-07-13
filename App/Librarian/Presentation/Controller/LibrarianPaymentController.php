<?php

namespace App\Payment\Presentation\Controller;

use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Payment\Application\Handler\ApprovePaymentHandler;
use App\Payment\Application\Handler\RejectPaymentHandler;
use App\Payment\Application\Command\ApprovePaymentCommand;
use App\Payment\Application\Command\RejectPaymentCommand;
use App\Shared\Base\BaseController;

class LibrarianPaymentController extends BaseController
{
    private PaymentRepositoryInterface $paymentRepo;
    private ApprovePaymentHandler $approveHandler;
    private RejectPaymentHandler $rejectHandler;

    public function __construct(
        PaymentRepositoryInterface $paymentRepo,
        ApprovePaymentHandler $approveHandler,
        RejectPaymentHandler $rejectHandler
    ) {
        parent::__construct();
        
        $this->paymentRepo = $paymentRepo;
        $this->approveHandler = $approveHandler;
        $this->rejectHandler = $rejectHandler;
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

    public function approve(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        
        try {
            $cmd = new ApprovePaymentCommand($id, $_SESSION['user_id'] ?? 0);
            $this->approveHandler->handle($cmd);

            // Set success message in session
            $_SESSION['flash_success'] = 'Payment has been approved successfully.';
            $this->redirect(BASE_URL . '/librarian/payments');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect(BASE_URL . '/librarian/payments');
        }
    }

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
}