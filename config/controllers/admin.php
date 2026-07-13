<?php

use App\Shared\Core\ErrorHandler;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Core\Authorization\Authorization;
use App\Admin\Infrastructure\Persistence\SettingRepository;
use App\Admin\Presentation\Controller\DashboardController as AdminDashboardController;
use App\Admin\Presentation\Controller\AdminLibrarianController;
use App\Admin\Presentation\Controller\AdminUserController;
use App\Admin\Presentation\Controller\AdminSettingsController;
use App\Admin\Presentation\Controller\AdminRoleController;
use App\Admin\Presentation\Controller\AdminFineController;

// ================================================================
// 👑 Admin Controllers
// ================================================================

$container->set(AdminDashboardController::class, function($c) {
    return new AdminDashboardController($c);
});

$container->set(AdminLibrarianController::class, function($c) {
    return new AdminLibrarianController(
        $c->get(UserAuthenticator::class),
        $c->get('librarian.service')
    );
});

$container->set(AdminUserController::class, function($c) {
    return new AdminUserController(
        $c->get('user.repository'),
        $c->get(UserAuthenticator::class)
    );
});

$container->set(AdminSettingsController::class, function($c) {
    return new AdminSettingsController(
        $c->get(UserAuthenticator::class),
        $c->get('user.repository'),
        $c->get(Authorization::class),
        $c->get('db')
    );
});

$container->set(AdminRoleController::class, function($c) {
    return new AdminRoleController(
        $c->get('user.repository'),
        $c->get(UserAuthenticator::class)
    );
});

$container->set(AdminFineController::class, function($c) {
    return new AdminFineController(
        $c->get(SettingRepository::class),
        $c->get(UserAuthenticator::class)
    );
});

ErrorHandler::log('✅ Admin controllers registered', 'DEBUG');