<?php

// ── All necessary use statements ──
use App\User\Infrastructure\Persistence\UserRepository;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\UserDomainService;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\User\Infrastructure\Service\VerificationService;
use App\User\Application\UseCase\RegisterUser;
use App\User\Application\UseCase\LoginUser;
use App\User\Application\UseCase\LogoutUser;
use App\User\Application\UseCase\GetUser;
use App\User\Presentation\Controller\AuthController;
use App\User\Presentation\Controller\LoginController;
use App\User\Presentation\Controller\VerificationController;
use App\User\Presentation\Controller\ViewController;
use App\User\Presentation\Controller\DashboardController;
use App\User\Presentation\Controller\BorrowController as UserBorrowController;
use App\User\Presentation\Controller\InvoiceController as UserInvoiceController;

// These will be resolved when the container is run, but we need them for type hints
use App\Circulation\Application\Handler\BorrowBookHandler;
use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Shared\Core\Authorization\Authorization;

return function ($container) {

    // ── Repository ──
    $container->singleton(UserRepository::class, function ($c) {
        return new UserRepository($c->get('db'));
    });
    $container->singleton(UserRepositoryInterface::class, fn($c) => $c->get(UserRepository::class));
    $container->set('user.repository', fn($c) => $c->get(UserRepositoryInterface::class));

    // ── Domain Services ──
    $container->singleton(UserDomainService::class, function ($c) {
        return new UserDomainService($c->get(UserRepositoryInterface::class));
    });
    $container->set('user.domain.service', fn($c) => $c->get(UserDomainService::class));

    // ── Authenticator ──
    $container->singleton(UserAuthenticator::class, function ($c) {
        return new UserAuthenticator(
            $c->get(UserRepositoryInterface::class),
            $c->get(Authorization::class)
        );
    });
    $container->set('user.authenticator', fn($c) => $c->get(UserAuthenticator::class));

    // ── Verification Service ──
    $container->singleton(VerificationService::class, function ($c) {
        return new VerificationService($c->get(UserRepositoryInterface::class));
    });
    $container->set('verification.service', fn($c) => $c->get(VerificationService::class));

    // ── UseCases ──
    $container->singleton(RegisterUser::class, function ($c) {
        return new RegisterUser(
            $c->get(UserRepositoryInterface::class),
            $c->get(UserDomainService::class),
            $c->get(VerificationService::class)
        );
    });

    $container->singleton(LoginUser::class, function ($c) {
        return new LoginUser(
            $c->get(UserRepositoryInterface::class),
            $c->get(UserAuthenticator::class)
        );
    });

    $container->singleton(LogoutUser::class, function ($c) {
        return new LogoutUser($c->get(UserAuthenticator::class));
    });

    $container->singleton(GetUser::class, function ($c) {
        return new GetUser(
            $c->get(UserRepositoryInterface::class),
            $c->get(UserAuthenticator::class)
        );
    });

    // ── User Controllers ──
    $container->singleton(AuthController::class, function ($c) {
        return new AuthController(
            $c->get(RegisterUser::class),
            $c->get(LoginUser::class),
            $c->get(LogoutUser::class),
            $c->get(UserAuthenticator::class),
            $c->get('loan.repository'),
            $c->get('book.repository')
        );
    });

    $container->singleton(LoginController::class, function ($c) {
        return new LoginController(
            $c->get(UserAuthenticator::class),
            $c->get(Authorization::class)
        );
    });

    $container->singleton(VerificationController::class, function ($c) {
        return new VerificationController(
            $c->get(VerificationService::class),
            $c->get(UserAuthenticator::class),
            $c->get(UserRepositoryInterface::class)
        );
    });

    $container->singleton(ViewController::class, function ($c) {
        return new ViewController(
            $c->get(Authorization::class),
            $c->get(UserRepositoryInterface::class),
            $c->get('payment.repository')
        );
    });

    $container->singleton(DashboardController::class, function ($c) {
        return new DashboardController(
            $c->get(UserAuthenticator::class),
            $c->get(VerificationService::class),
            $c->get(UserRepositoryInterface::class)
        );
    });

    $container->singleton(UserBorrowController::class, function ($c) {
        return new UserBorrowController(
            $c->get(BorrowBookHandler::class),
            $c->get(Authorization::class)
        );
    });

    $container->singleton(UserInvoiceController::class, function ($c) {
        return new UserInvoiceController(
            $c->get(InvoiceRepositoryInterface::class),
            $c->get('payment.repository'),
            $c->get('loan.repository'),
            $c->get(UserRepositoryInterface::class),
            $c->get('book.repository')
        );
    });
};