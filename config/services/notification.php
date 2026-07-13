<?php

// ================================================================
// 🔔 Notification Services
// ================================================================
use App\Shared\Core\ErrorHandler;
use App\Notification\Infrastructure\Persistence\NotificationRepository;
use App\Notification\Application\Service\NotificationService;

$container->singleton('notification.repository', function() use ($pdo) {
    return new NotificationRepository($pdo);
});
$container->singleton('notification.service', function($c) {
    return new NotificationService($c->get('notification.repository'));
});

ErrorHandler::log('✅ Notification services registered', 'DEBUG');