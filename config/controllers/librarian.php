<?php

use App\Shared\Core\ErrorHandler;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Core\Authorization\Authorization;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Domain\Repository\CategoryRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Librarian\Presentation\Controller\DashboardController as LibrarianDashboardController;
use App\Librarian\Presentation\Controller\LibrarianCategoryController;
use App\Librarian\Presentation\Controller\LoanController;
use App\Librarian\Presentation\Controller\UserController;
use App\Payment\Presentation\Controller\LibrarianPaymentController;
use App\Payment\Application\Handler\ApprovePaymentHandler;
use App\Payment\Application\Handler\RejectPaymentHandler;

// ================================================================
// 👨‍🏫 Librarian Controllers
// ================================================================

$container->set(LibrarianDashboardController::class, function($c) {
    return new LibrarianDashboardController(
        $c->get(UserAuthenticator::class),
        $c->get(BookRepositoryInterface::class),
        $c->get(CategoryRepositoryInterface::class),
        $c->get(UserRepositoryInterface::class),
        $c->get(LoanRepositoryInterface::class),
        $c->get(PaymentRepositoryInterface::class),
        $c->get(Authorization::class)
    );
});

$container->set(LibrarianCategoryController::class, function($c) {
    return new LibrarianCategoryController($c);
});

$container->set(LoanController::class, function($c) {
    return new LoanController(
        $c->get(LoanRepositoryInterface::class),
        $c->get(BookRepositoryInterface::class),
        $c->get(UserRepositoryInterface::class),
        $c->get('db')
    );
});

$container->set(UserController::class, function($c) {
    return new UserController($c);
});

$container->set(LibrarianPaymentController::class, function($c) {
    return new LibrarianPaymentController(
        $c->get(PaymentRepositoryInterface::class),
        $c->get(ApprovePaymentHandler::class),
        $c->get(RejectPaymentHandler::class)
    );
});

ErrorHandler::log('✅ Librarian controllers registered', 'DEBUG');