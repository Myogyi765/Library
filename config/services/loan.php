<?php

use App\Shared\Core\ErrorHandler;
use App\Loan\Infrastructure\Persistence\LoanRepository;
use App\Loan\Infrastructure\Mapper\LoanMapper;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\Loan\Application\Handler\BorrowBookHandler;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Admin\Application\Service\SettingsService;   // ✅ added

$container->singleton(LoanMapper::class, function($c) {
    return new LoanMapper();
});

$container->singleton(LoanRepositoryInterface::class, function($c) {
    return new LoanRepository(
        $c->get('db'),
        $c->get(LoanMapper::class)
    );
});
$container->set('loan.repository', function($c) {
    return $c->get(LoanRepositoryInterface::class);
});

$container->singleton(BorrowBookHandler::class, function($c) {
    return new BorrowBookHandler(
        $c->get(LoanRepositoryInterface::class),
        $c->get(BookRepositoryInterface::class),
        $c->get(SettingsService::class)              // ✅ added third argument
    );
});

ErrorHandler::log('✅ Loan services registered', 'DEBUG');