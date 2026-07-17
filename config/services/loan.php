<?php

use App\Shared\Core\ErrorHandler;
use App\Circulation\Infrastructure\Persistence\LoanRepository;
use App\Circulation\Infrastructure\Mapper\LoanMapper;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Circulation\Application\Handler\BorrowBookHandler;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Admin\Application\Service\SettingsService;  

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
        $c->get(SettingsService::class)            
    );
});

ErrorHandler::log('✅ Loan services registered', 'DEBUG');