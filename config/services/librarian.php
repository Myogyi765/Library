<?php

use App\Librarian\Infrastructure\Persistence\LibrarianRepository;
use App\Librarian\Application\Service\LibrarianService;
use App\Librarian\Presentation\Controller\DashboardController as LibrarianDashboardController;
use App\Librarian\Presentation\Controller\LibrarianCategoryController;
use App\Librarian\Presentation\Controller\LoanController;
use App\Librarian\Presentation\Controller\UserController;
use App\Librarian\Presentation\Controller\ScanController;
use App\Librarian\Presentation\Controller\LibrarianAuthController;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Domain\Repository\CategoryRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Shared\Core\Authorization\Authorization;
use App\Admin\Application\Service\DashboardStatisticsService; // ✅ Import

return function ($container) {

    // ── Repository ──
    $container->singleton(LibrarianRepository::class, function ($c) {
        return new LibrarianRepository($c->get('db'));
    });
    $container->set('librarian.repository', fn($c) => $c->get(LibrarianRepository::class));

    // ── Service ──
    $container->singleton(LibrarianService::class, function ($c) {
        return new LibrarianService($c->get('librarian.repository'));
    });
    $container->set('librarian.service', fn($c) => $c->get(LibrarianService::class));

    // ── Controllers ──
    $container->singleton(LibrarianDashboardController::class, function ($c) {
        return new LibrarianDashboardController(
            $c->get(UserAuthenticator::class),
            $c->get(BookRepositoryInterface::class),
            $c->get(CategoryRepositoryInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(LoanRepositoryInterface::class),
            $c->get(PaymentRepositoryInterface::class),
            $c->get(Authorization::class),
            $c->get(DashboardStatisticsService::class) // ✅ Added missing dependency
        );
    });

    $container->singleton(LibrarianCategoryController::class, function ($c) {
        return new LibrarianCategoryController(
            $c->get(CategoryRepositoryInterface::class)
        );
    });

    $container->singleton(LoanController::class, function ($c) {
        return new LoanController(
            $c->get(LoanRepositoryInterface::class),
            $c->get(BookRepositoryInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get('db')
        );
    });

    $container->singleton(UserController::class, function ($c) {
        return new UserController(
            $c->get(UserAuthenticator::class),
            $c->get(UserRepositoryInterface::class)
        );
    });

    $container->singleton(ScanController::class, function ($c) {
        return new ScanController(
            $c->get(BookRepositoryInterface::class),
            $c->get(LoanRepositoryInterface::class),
            $c->get(UserRepositoryInterface::class)
        );
    });

    $container->singleton(LibrarianAuthController::class, function ($c) {
        return new LibrarianAuthController(
            $c->get(UserAuthenticator::class)
        );
    });
};