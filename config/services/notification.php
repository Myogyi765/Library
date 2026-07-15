<?php

use App\Notification\Infrastructure\Persistence\NotificationRepository;
use App\Notification\Application\Service\NotificationService;
use App\Notification\Presentation\Controller\NotificationController;

return function ($container) {

    // ── Repository ──
    $container->singleton(NotificationRepository::class, function ($c) {
        return new NotificationRepository($c->get('db'));
    });
    $container->set('notification.repository', fn($c) => $c->get(NotificationRepository::class));

    // ── Service ──
    $container->singleton(NotificationService::class, function ($c) {
        return new NotificationService($c->get('notification.repository'));
    });
    $container->set('notification.service', fn($c) => $c->get(NotificationService::class));

    // ── Controller ──
    $container->singleton(NotificationController::class, function ($c) {
        return new NotificationController($c->get(NotificationService::class));
    });
};