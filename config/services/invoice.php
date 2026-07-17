<?php

use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Invoice\Infrastructure\Persistence\InvoiceRepository;
use App\Invoice\Infrastructure\Mapper\InvoiceMapper;
use App\Invoice\Presentation\Controller\InvoiceController;


use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;

use App\Shared\Core\ErrorHandler;

return function($container) {
    $container->singleton(InvoiceMapper::class, function($c) {
        return new InvoiceMapper();
    });

    $container->singleton(InvoiceRepositoryInterface::class, function($c) {
        return new InvoiceRepository(
            $c->get('db'),
            $c->get(InvoiceMapper::class)
        );
    });

    $container->set('invoice.repository', function($c) {
        return $c->get(InvoiceRepositoryInterface::class);
    });

    $container->singleton(InvoiceController::class, function($c) {
        return new InvoiceController(
            $c->get(InvoiceRepositoryInterface::class),   
            $c->get(PaymentRepositoryInterface::class),   
            $c->get(LoanRepositoryInterface::class),      
            $c->get(UserRepositoryInterface::class),  
            $c->get(BookRepositoryInterface::class)
        );
    });

    ErrorHandler::log('✅ Invoice services registered (with all dependencies)', 'DEBUG');
};