<?php

use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Invoice\Infrastructure\Persistence\InvoiceRepository;
use App\Invoice\Infrastructure\Mapper\InvoiceMapper;
use App\Shared\Core\ErrorHandler;

// Invoice Mapper
$container->singleton(InvoiceMapper::class, function($c) {
    return new InvoiceMapper();
});

// Invoice Repository
$container->singleton(InvoiceRepositoryInterface::class, function($c) {
    return new InvoiceRepository(
        $c->get('db'),
        $c->get(InvoiceMapper::class)
    );
});

// Alias for convenience
$container->set('invoice.repository', function($c) {
    return $c->get(InvoiceRepositoryInterface::class);
});

ErrorHandler::log('✅ Invoice services registered', 'DEBUG');
