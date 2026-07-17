<?php

use App\Notification\Infrastructure\Persistence\NotificationRepository;
use App\Notification\Application\Service\NotificationService;
use App\Notification\Presentation\Controller\NotificationController;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Core\Authorization\Authorization;

return function ($container) {

    $container->singleton(NotificationRepository::class, function ($c) {
        return new NotificationRepository($c->get('db'));
    });
    $container->set('notification.repository', fn($c) => $c->get(NotificationRepository::class));

    $container->singleton(NotificationService::class, function ($c) {
        return new NotificationService($c->get('notification.repository'));
    });
    $container->set('notification.service', fn($c) => $c->get(NotificationService::class));

    $container->singleton(NotificationController::class, function ($c) {
        return new NotificationController(
            $c->get(NotificationService::class),
            $c->get(UserAuthenticator::class),
            $c->get(Authorization::class)
        );
    });
};