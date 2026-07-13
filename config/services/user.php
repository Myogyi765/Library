<?php

use App\Shared\Core\ErrorHandler;
use App\User\Infrastructure\Persistence\UserRepository;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\User\Infrastructure\Service\VerificationService;
use App\User\Domain\Service\UserDomainService;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Shared\Core\Authorization\Authorization; 

// User Repository
$container->singleton('user.repository', function() use ($pdo) {
    return new UserRepository($pdo);
});
$container->singleton(UserRepositoryInterface::class, function($c) {
    return $c->get('user.repository');
});

// User Domain Service
$container->singleton('user.domain.service', function() use ($container) {
    return new UserDomainService($container->get('user.repository'));
});

// ✅ Authorization (Session-based)
$container->singleton(Authorization::class, function($c) {
    return new Authorization($c->get('db'));
});
$container->set('authorization', function($c) { 
    return $c->get(Authorization::class);
});
ErrorHandler::log('✅ Authorization registered', 'DEBUG');

// User Authenticator
$container->singleton('user.authenticator', function() use ($container) {
    return new UserAuthenticator(
        $container->get('user.repository'),
        $container->get(Authorization::class) 
    );
});
$container->set(UserAuthenticator::class, function() use ($container) {
    return $container->get('user.authenticator');
});

// Verification Service
$container->singleton('verification.service', function() use ($container) {
    return new VerificationService($container->get('user.repository'));
});

ErrorHandler::log('✅ User services registered', 'DEBUG');