<?php

use App\Book\Application\UseCase\GetBook;
use App\Book\Application\UseCase\GetBooks;
use App\Admin\Infrastructure\Persistence\SettingRepository;
use App\Admin\Application\Service\SettingsService;
use App\Admin\Application\Service\PermissionService;
use App\Admin\Application\Service\UserManagementService;
use App\Admin\Application\Service\ReportService;
use App\Admin\Application\Service\DashboardStatisticsService;
use App\Admin\Presentation\Controller\AdminDashboardController;
use App\Admin\Presentation\Controller\AdminLibrarianController;
use App\Admin\Presentation\Controller\AdminUserController;
use App\Admin\Presentation\Controller\AdminSettingsController;
use App\Admin\Presentation\Controller\AdminRoleController;
use App\Admin\Presentation\Controller\AdminFineController;
use App\Admin\Presentation\Controller\AdminBookController;
use App\Admin\Presentation\Controller\AdminReportController;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\User\Infrastructure\Persistence\UserRepository;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Shared\Core\Authorization\Authorization;

return function ($container) {

    $container->singleton(SettingRepository::class, function ($c) {
        return new SettingRepository($c->get('db'));
    });
    $container->set('setting.repository', fn($c) => $c->get(SettingRepository::class));

    $container->singleton(SettingsService::class, function ($c) {
        return new SettingsService($c->get(SettingRepository::class));
    });
    $container->set('admin.settings.service', fn($c) => $c->get(SettingsService::class));

    $container->singleton(PermissionService::class, function ($c) {
        return new PermissionService($c->get('db'));
    });
    $container->singleton(UserManagementService::class, function ($c) {
        return new UserManagementService($c->get(UserRepositoryInterface::class));
    });

    $container->singleton(ReportService::class, function ($c) {
        return new ReportService(
            $c->get(BookRepositoryInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(LoanRepositoryInterface::class)
        );
    });
    $container->singleton(DashboardStatisticsService::class, function ($c) {
        return new DashboardStatisticsService(
            $c->get(BookRepositoryInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(LoanRepositoryInterface::class)
        );
    });

    $container->singleton(AdminDashboardController::class, function ($c) {
        return new AdminDashboardController(
            $c->get(UserAuthenticator::class),
            $c->get(DashboardStatisticsService::class)
        );
    });

    $container->singleton(AdminLibrarianController::class, function ($c) {
        return new AdminLibrarianController(
            $c->get(UserAuthenticator::class),
            $c->get('librarian.service')
        );
    });

    $container->singleton(AdminUserController::class, function ($c) {
        return new AdminUserController(
            $c->get(UserRepositoryInterface::class),
            $c->get(UserAuthenticator::class),
            $c->get(UserManagementService::class)
        );
    });

    $container->singleton(AdminSettingsController::class, function ($c) {
        return new AdminSettingsController(
            $c->get(UserAuthenticator::class),
            $c->get(UserRepository::class),
            $c->get(Authorization::class),
            $c->get(PermissionService::class),
            $c->get(SettingsService::class)
        );
    });

    $container->singleton(AdminRoleController::class, function ($c) {
        return new AdminRoleController(
            $c->get(UserRepositoryInterface::class),
            $c->get(UserAuthenticator::class)
        );
    });

    $container->singleton(AdminFineController::class, function ($c) {
        return new AdminFineController(
            $c->get(SettingRepository::class),
            $c->get(UserAuthenticator::class)
        );
    });

    $container->singleton(AdminBookController::class, function ($c) {
        return new AdminBookController(
            $c->get(GetBooks::class),
            $c->get(GetBook::class)
        );
    });

    $container->singleton(AdminReportController::class, function ($c) {
        return new AdminReportController($c->get(ReportService::class));
    });
};