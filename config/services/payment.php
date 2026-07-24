<?php

use App\Payment\Infrastructure\Mapper\PaymentMapper;
use App\Payment\Infrastructure\Repository\PaymentRepository;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Payment\Infrastructure\Storage\FileUploadService;
use App\Payment\Application\Handler\SubmitPaymentHandler;
use App\Payment\Application\Handler\ApprovePaymentHandler;
use App\Payment\Application\Handler\RejectPaymentHandler;
use App\Payment\Presentation\Controller\PaymentController;
use App\Payment\Presentation\Controller\LibrarianPaymentController;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Admin\Application\Service\SettingsService;  
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Notification\Application\Service\NotificationService; 

return function ($container) {

    $container->singleton(PaymentMapper::class, fn() => new PaymentMapper());

    $container->singleton(PaymentRepository::class, function ($c) {
        return new PaymentRepository($c->get('db'), $c->get(PaymentMapper::class));
    });
    $container->singleton(PaymentRepositoryInterface::class, fn($c) => $c->get(PaymentRepository::class));
    $container->set('payment.repository', fn($c) => $c->get(PaymentRepositoryInterface::class));

    $container->singleton(FileUploadService::class, fn() => new FileUploadService());

    $container->singleton(SubmitPaymentHandler::class, function ($c) {
        return new SubmitPaymentHandler(
            $c->get(PaymentRepositoryInterface::class),
            $c->get(LoanRepositoryInterface::class),
            $c->get(BookRepositoryInterface::class)
        );
    });

    $container->singleton(ApprovePaymentHandler::class, function ($c) {
        return new ApprovePaymentHandler(
            $c->get(PaymentRepositoryInterface::class),
            $c->get(LoanRepositoryInterface::class),
            $c->get(BookRepositoryInterface::class),
            $c->get(InvoiceRepositoryInterface::class),
            $c->get(SettingsService::class)  
        );
    });

    $container->singleton(RejectPaymentHandler::class, function ($c) {
        return new RejectPaymentHandler(
            $c->get(PaymentRepositoryInterface::class),
            $c->get(LoanRepositoryInterface::class)
        );
    });

    $container->singleton(PaymentController::class, function ($c) {
        return new PaymentController(
            $c->get(SubmitPaymentHandler::class),
            $c->get(FileUploadService::class),
            $c->get(LoanRepositoryInterface::class),
            $c->get(SettingsService::class),
            $c->get(PaymentRepositoryInterface::class) 
        );
    });

    $container->singleton(LibrarianPaymentController::class, function ($c) {
        return new LibrarianPaymentController(
            $c->get(PaymentRepositoryInterface::class),
            $c->get(ApprovePaymentHandler::class),
            $c->get(RejectPaymentHandler::class),
            $c->get(LoanRepositoryInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(BookRepositoryInterface::class),
            $c->get(InvoiceRepositoryInterface::class),
            $c->get(SettingsService::class),
            $c->get(NotificationService::class) 
        );
    });
};