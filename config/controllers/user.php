<?php

use App\Shared\Core\ErrorHandler;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Core\Authorization\Authorization;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Presentation\Controller\LoginController;
use App\User\Presentation\Controller\AuthController;
use App\User\Presentation\Controller\VerificationController;
use App\User\Presentation\Controller\ViewController;
use App\User\Presentation\Controller\BorrowController as UserBorrowController;
use App\User\Presentation\Controller\InvoiceController;
use App\Loan\Application\Handler\BorrowBookHandler;

// ================================================================
// 👤 User Controllers
// ================================================================

$container->set(LoginController::class, function($c) {
    return new LoginController(
        $c->get(UserAuthenticator::class),
        $c->get(Authorization::class)
    );
});

$container->set(AuthController::class, function($c) {
    return new AuthController($c);
});

$container->set(VerificationController::class, function($c) {
    return new VerificationController($c);
});

$container->set(ViewController::class, function($c) {
    return new ViewController(
        $c->get(Authorization::class),
        $c->get(UserRepositoryInterface::class)
    );
});

$container->set(UserBorrowController::class, function($c) {
    return new UserBorrowController(
        $c->get(BorrowBookHandler::class),
        $c->get(Authorization::class)
    );
});

$container->set(InvoiceController::class, function($c) {
    return new InvoiceController(
        $c->get(\App\Invoice\Domain\Repository\InvoiceRepositoryInterface::class),
        $c->get(\App\Payment\Domain\Repository\PaymentRepositoryInterface::class),
        $c->get(\App\Loan\Domain\Repository\LoanRepositoryInterface::class),
        $c->get(UserRepositoryInterface::class),
        $c->get(\App\Book\Domain\Repository\BookRepositoryInterface::class)
    );
});

ErrorHandler::log('✅ User controllers registered', 'DEBUG');