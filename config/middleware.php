<?php

// ================================================================
// 🔐 Middleware
// ================================================================
use App\Shared\Core\ErrorHandler;
use App\Shared\Core\Middleware\AuthMiddleware;
use App\Shared\Core\Middleware\RoleMiddleware;
use App\Shared\Core\Middleware\PermissionMiddleware;
use App\User\Infrastructure\Security\UserAuthenticator;

$container->set(AuthMiddleware::class, function($c) {
    return new AuthMiddleware($c->get(UserAuthenticator::class));
});

$container->set(RoleMiddleware::class, function($c) {
    return new RoleMiddleware($c->get(UserAuthenticator::class), '');
});

$container->set(PermissionMiddleware::class, function($c) {
    return new PermissionMiddleware($c->get(UserAuthenticator::class), '');
});

ErrorHandler::log('✅ Middleware registered', 'DEBUG');