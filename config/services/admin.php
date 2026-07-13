<?php

use App\Shared\Core\ErrorHandler;
use App\Admin\Infrastructure\Persistence\AdminRepository;
use App\Admin\Application\UseCase\LoginAdmin;
use App\Admin\Application\UseCase\RegisterAdmin;
use App\Admin\Application\UseCase\GetAdmin;
use App\Admin\Application\Service\AdminService;
use App\Admin\Infrastructure\Persistence\SettingRepository;

if (interface_exists('App\Admin\Domain\Repository\AdminRepositoryInterface')) {
    $container->set('admin.repository', function() use ($pdo) {
        return new AdminRepository($pdo);
    });
    $container->singleton(LoginAdmin::class, function($c) {
        return new LoginAdmin($c->get('admin.repository'));
    });
    $container->singleton(RegisterAdmin::class, function($c) {
        return new RegisterAdmin($c->get('admin.repository'));
    });
    $container->singleton(GetAdmin::class, function($c) {
        return new GetAdmin($c->get('admin.repository'));
    });
    $container->set('admin.service', function() use ($container) {
        return new AdminService(
            $container->get(LoginAdmin::class),
            $container->get(RegisterAdmin::class),
            $container->get(GetAdmin::class),
            $container->get('admin.repository')
        );
    });
    ErrorHandler::log('✅ Admin services registered', 'DEBUG');
} else {
    ErrorHandler::log('⚠️ AdminRepositoryInterface not found – Admin services skipped', 'WARNING');
}

$container->singleton(SettingRepository::class, function($c) use ($pdo) {
    return new SettingRepository($pdo);
});
$container->set('setting.repository', function($c) {
    return $c->get(SettingRepository::class);
});
ErrorHandler::log('✅ SettingRepository registered', 'DEBUG');