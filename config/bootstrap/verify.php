<?php

use App\Shared\Core\ErrorHandler;
use App\User\Infrastructure\Security\UserAuthenticator;
use App\Shared\Core\Authorization\Authorization;
use App\User\Presentation\Controller\LoginController;
use App\User\Presentation\Controller\AuthController;
use App\User\Presentation\Controller\BorrowController as UserBorrowController;
use App\Loan\Application\Handler\BorrowBookHandler;
use App\Admin\Presentation\Controller\AdminUserController;
use App\Admin\Presentation\Controller\AdminSettingsController;
use App\Admin\Presentation\Controller\AdminRoleController;
use App\Admin\Presentation\Controller\AdminFineController;
use App\Admin\Infrastructure\Persistence\SettingRepository;
use App\Librarian\Presentation\Controller\LibrarianCategoryController;
use App\Librarian\Presentation\Controller\LoanController;
use App\Librarian\Presentation\Controller\UserController;
use App\Book\Presentation\Controller\BookController;
use App\Loan\Domain\Repository\LoanRepositoryInterface;
use App\Loan\Infrastructure\Mapper\LoanMapper;
use App\Notification\Presentation\Controller\NotificationController;
use App\Shared\Core\Middleware\AuthMiddleware;
use App\Shared\Core\Middleware\RoleMiddleware;
use App\Shared\Core\Middleware\PermissionMiddleware;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\Book\Domain\Repository\CategoryRepositoryInterface;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Payment\Presentation\Controller\PaymentController;
use App\Payment\Application\Handler\SubmitPaymentHandler;
use App\Payment\Infrastructure\Storage\FileUploadService;
use App\Payment\Application\Handler\ApprovePaymentHandler;
use App\Payment\Application\Handler\RejectPaymentHandler;
use App\Payment\Presentation\Controller\LibrarianPaymentController;

// ================================================================
// ✅ Verify Critical Services
// ================================================================

$critical = [
    'db',
    'user.repository',
    'user.authenticator',
    UserAuthenticator::class,
    Authorization::class,        
    // 'Authorization',          
    'verification.service',
    LoginController::class,
    AuthController::class,
    UserBorrowController::class,
    BorrowBookHandler::class,
    'admin.service',
    AdminUserController::class,
    AdminSettingsController::class,
    AdminRoleController::class,
    AdminFineController::class,
    SettingRepository::class,
    LibrarianCategoryController::class,
    LoanController::class,
    UserController::class,
    BookController::class,
    'loan.repository',
    LoanRepositoryInterface::class,
    LoanMapper::class,
    NotificationController::class,
    'notification.service',
    'notification.repository',
    AuthMiddleware::class,
    RoleMiddleware::class,
    PermissionMiddleware::class,
    BookRepositoryInterface::class,
    UserRepositoryInterface::class,
    CategoryRepositoryInterface::class,
    PaymentRepositoryInterface::class,
    PaymentController::class,
    SubmitPaymentHandler::class,
    FileUploadService::class,
    ApprovePaymentHandler::class,
    RejectPaymentHandler::class,
    LibrarianPaymentController::class,
];

$allRegistered = true;
foreach ($critical as $service) {
    if ($container->has($service)) {
        ErrorHandler::log("✅ Critical service registered: {$service}", 'DEBUG');
    } else {
        ErrorHandler::log("❌ Critical service MISSING: {$service}", 'ERROR');
        $allRegistered = false;
    }
}

if ($allRegistered) {
    ErrorHandler::log('✅ All critical services registered successfully', 'INFO');
} else {
    ErrorHandler::log('⚠️ Some critical services are missing!', 'WARNING');
}