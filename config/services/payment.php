<?php

use App\Shared\Core\ErrorHandler;
use App\Payment\Infrastructure\Mapper\PaymentMapper;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Payment\Infrastructure\Repository\PaymentRepository;
use App\Payment\Infrastructure\Storage\FileUploadService;
use App\Payment\Application\Handler\SubmitPaymentHandler;
use App\Payment\Application\Handler\ApprovePaymentHandler;
use App\Payment\Application\Handler\RejectPaymentHandler;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;

$container->singleton(PaymentMapper::class, fn($c) => new PaymentMapper());

$container->singleton(PaymentRepositoryInterface::class, function($c) {
    return new PaymentRepository($c->get('db'), $c->get(PaymentMapper::class));
});
$container->set('payment.repository', fn($c) => $c->get(PaymentRepositoryInterface::class));

$container->singleton(FileUploadService::class, fn($c) => new FileUploadService());

$container->singleton(SubmitPaymentHandler::class, function($c) {
    return new SubmitPaymentHandler(
        $c->get(PaymentRepositoryInterface::class),
        $c->get(LoanRepositoryInterface::class),
        $c->get(BookRepositoryInterface::class)
    );
});

$container->singleton(ApprovePaymentHandler::class, function($c) {
    return new ApprovePaymentHandler(
        $c->get(PaymentRepositoryInterface::class),
        $c->get(LoanRepositoryInterface::class),
          $c->get(BookRepositoryInterface::class) 
    );
});

$container->singleton(RejectPaymentHandler::class, function($c) {
    return new RejectPaymentHandler(
        $c->get(PaymentRepositoryInterface::class),
        $c->get(LoanRepositoryInterface::class)
    );
});

ErrorHandler::log('✅ Payment services registered', 'DEBUG');