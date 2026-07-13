<?php

use App\Shared\Core\ErrorHandler;
use App\Payment\Presentation\Controller\PaymentController;
use App\Payment\Presentation\Controller\LibrarianPaymentController;
use App\Payment\Application\Handler\SubmitPaymentHandler;
use App\Payment\Application\Handler\ApprovePaymentHandler;
use App\Payment\Application\Handler\RejectPaymentHandler;
use App\Payment\Infrastructure\Storage\FileUploadService;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Admin\Application\Service\SettingsService; 

// ================================================================
// 💳 Payment Controllers
// ================================================================

// 1️⃣ User Payment Controller
$container->set(PaymentController::class, function($c) {
    return new PaymentController(
        $c->get(SubmitPaymentHandler::class),
        $c->get(FileUploadService::class),
        $c->get(LoanRepositoryInterface::class),
        $c->get(SettingsService::class) 
    );
});

// 2️⃣ Librarian Payment Controller (updated with InvoiceRepository)
$container->set(LibrarianPaymentController::class, function($c) {
    return new LibrarianPaymentController(
        $c->get(PaymentRepositoryInterface::class),
        $c->get(ApprovePaymentHandler::class),
        $c->get(RejectPaymentHandler::class),
        $c->get(LoanRepositoryInterface::class),
        $c->get(UserRepositoryInterface::class),
        $c->get(BookRepositoryInterface::class),
        $c->get(InvoiceRepositoryInterface::class) 
    );
});

ErrorHandler::log('✅ Payment controllers registered', 'DEBUG');