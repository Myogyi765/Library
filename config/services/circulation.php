<?php

use App\Circulation\Infrastructure\Persistence\LoanRepository;
use App\Circulation\Infrastructure\Mapper\LoanMapper;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Circulation\Application\Handler\BorrowBookHandler;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Admin\Application\Service\SettingsService;

return function ($container) {

    // ── Loan Repository ──
    $container->singleton(LoanMapper::class, fn() => new LoanMapper());
    $container->singleton(LoanRepository::class, function ($c) {
        return new LoanRepository($c->get('db'), $c->get(LoanMapper::class));
    });
    $container->singleton(LoanRepositoryInterface::class, fn($c) => $c->get(LoanRepository::class));
    $container->set('loan.repository', fn($c) => $c->get(LoanRepositoryInterface::class));

    // ── Handler ──
    $container->singleton(BorrowBookHandler::class, function ($c) {
        return new BorrowBookHandler(
            $c->get(LoanRepositoryInterface::class),
            $c->get(BookRepositoryInterface::class),
            $c->get(SettingsService::class)
        );
    });
};