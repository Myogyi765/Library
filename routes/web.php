<?php

use App\User\Presentation\Controller\AuthController;
use App\User\Presentation\Controller\VerificationController;
use App\User\Presentation\Controller\ViewController;
use App\User\Presentation\Controller\BorrowController;
use App\User\Presentation\Controller\InvoiceController as UserInvoiceController;

use App\Payment\Presentation\Controller\PaymentController;
use App\Payment\Presentation\Controller\LibrarianPaymentController;

// ✅ Fixed: use exact class names
use App\Admin\Presentation\Controller\AdminDashboardController;
use App\Admin\Presentation\Controller\AdminLibrarianController;
use App\Admin\Presentation\Controller\AdminUserController;
use App\Admin\Presentation\Controller\AdminSettingsController;
use App\Admin\Presentation\Controller\AdminRoleController;
use App\Admin\Presentation\Controller\AdminReportController as ReportController;
use App\Admin\Presentation\Controller\AdminFineController;
use App\Admin\Presentation\Controller\AdminBookController;

use App\Librarian\Presentation\Controller\DashboardController as LibrarianDashboardController;
use App\Librarian\Presentation\Controller\LibrarianCategoryController;
use App\Librarian\Presentation\Controller\LoanController;
use App\Librarian\Presentation\Controller\UserController;
use App\Librarian\Presentation\Controller\RefundController;
use App\Librarian\Presentation\Controller\ScanController; // ✅ Added

use App\User\Presentation\Controller\LoginController;
use App\Shared\Core\Middleware\AuthMiddleware;
use App\Shared\Core\Authorization\Authorization;

use App\Book\Presentation\Controller\BookController;
use App\Notification\Presentation\Controller\NotificationController;

// ---------- Helper: detect if request is API ----------
function isApiRequest(): bool {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    return strpos($path, '/api') === 0;
}

// ---------- Role check callbacks (with JSON support) ----------
$adminOnly = function () {
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        if (isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Admin access required.']);
            return false;
        }
        http_response_code(403);
        echo '<h1>403 Forbidden</h1><p>Admin access required.</p>';
        return false;
    }
    return true;
};

$librarianOnly = function () {
    if (($_SESSION['user_role'] ?? '') !== 'librarian') {
        if (isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Librarian access required.']);
            return false;
        }
        http_response_code(403);
        echo '<h1>403 Forbidden</h1><p>Librarian access required.</p>';
        return false;
    }
    return true;
};

$userOnly = function () {
    if (($_SESSION['user_role'] ?? '') !== 'user') {
        if (isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'User access required.']);
            return false;
        }
        http_response_code(403);
        echo '<h1>403 Forbidden</h1><p>User access required.</p>';
        return false;
    }
    return true;
};

// ---------- Permission check (with JSON support) ----------
$authorizationCheck = function ($permission) use ($container) {
    return function () use ($container, $permission) {
        $authorization = $container->get(Authorization::class);

        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_permissions']);
            unset($_SESSION['user_roles']);
            $authorization->loadUserPermissions($_SESSION['user_id']);
        }
        if (!$authorization->hasPermission($permission)) {
            if (isApiRequest()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'You do not have permission: ' . $permission]);
                return false;
            }
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission: ' . htmlspecialchars($permission) . '</p>';
            return false;
        }
        return true;
    };
};

// ---------- Middleware shortcuts ----------
$adminMiddleware = [AuthMiddleware::class, $adminOnly];
$librarianMiddleware = [AuthMiddleware::class, $librarianOnly];
$userMiddleware = [AuthMiddleware::class, $userOnly];

// ---------- Routes ----------
$router->get('/', function () {
    header('Location: ' . BASE_URL . '/home');
    exit;
});

$router->get('/home', [AuthController::class, 'home']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->get('/login', [LoginController::class, 'showLogin']);
$router->get('/verify', [VerificationController::class, 'verifyEmail']);
$router->get('/verify-phone', [VerificationController::class, 'showVerifyPhone']);
$router->get('/resend-verification', [VerificationController::class, 'resendVerification']);
$router->get('/logout', [LoginController::class, 'logout']);

// ---- User area (role = user) ----
$router->get('/user-dashboard', [AuthController::class, 'userDashboard'], $userMiddleware);
$router->get('/profile', [ViewController::class, 'profile'], array_merge($userMiddleware, [$authorizationCheck('view_profile')]));
$router->get('/profile/edit', [ViewController::class, 'editProfile'], array_merge($userMiddleware, [$authorizationCheck('edit_profile')]));
$router->get('/payment/submit/{loan}', [PaymentController::class, 'showSubmitForm'], array_merge($userMiddleware, [$authorizationCheck('view_payments')]));
$router->get('/payment/success', [PaymentController::class, 'success'], $userMiddleware);
$router->post('/payment/submit', [PaymentController::class, 'submit'], array_merge($userMiddleware, [$authorizationCheck('create_payments')]));
$router->post('/books/borrow/{id}', [BorrowController::class, 'borrow'], array_merge($userMiddleware, [$authorizationCheck('borrow_books')]));
$router->get('/invoice/{id}', [UserInvoiceController::class, 'show'], $userMiddleware);

// ---- Admin area (role = admin) ----
$router->get('/admin/dashboard', [AdminDashboardController::class, 'index'], $adminMiddleware);
$router->get('/admin/reports', [ReportController::class, 'index'], $adminMiddleware);
$router->get('/admin/reports/export/csv', [ReportController::class, 'exportCsv'], $adminMiddleware);
$router->get('/admin/reports/export/pdf', [ReportController::class, 'exportPdf'], $adminMiddleware);
$router->get('/admin/librarian', [AdminLibrarianController::class, 'index'], $adminMiddleware);
$router->get('/admin/librarian/create', [AdminLibrarianController::class, 'create'], $adminMiddleware);
$router->get('/admin/librarian/edit/{id}', [AdminLibrarianController::class, 'edit'], $adminMiddleware);
$router->get('/admin/librarian/delete/{id}', [AdminLibrarianController::class, 'delete'], $adminMiddleware);
$router->get('/admin/users', [AdminUserController::class, 'index'], $adminMiddleware);
$router->get('/admin/users/create', [AdminUserController::class, 'create'], $adminMiddleware);
$router->get('/admin/users/edit/{id}', [AdminUserController::class, 'edit'], $adminMiddleware);
$router->get('/admin/users/delete/{id}', [AdminUserController::class, 'delete'], $adminMiddleware);
$router->get('/admin/settings', [AdminSettingsController::class, 'index'], $adminMiddleware);
$router->get('/admin/roles', [AdminRoleController::class, 'index'], $adminMiddleware);
$router->get('/admin/roles/edit/{id}', [AdminRoleController::class, 'edit'], $adminMiddleware);
$router->get('/admin/fines', [AdminFineController::class, 'index'], $adminMiddleware);
$router->get('/admin/books', [AdminBookController::class, 'index'], $adminMiddleware);
$router->get('/admin/books/show', [AdminBookController::class, 'show'], $adminMiddleware);

// ✅ ADDED: Enable/Disable toggle routes for users and librarians (POST)
$router->post('/admin/users/toggle/{id}', [AdminUserController::class, 'toggleStatus'], $adminMiddleware);
$router->post('/admin/librarians/toggle/{id}', [AdminLibrarianController::class, 'toggleStatus'], $adminMiddleware);

$router->post('/admin/librarian/create', [AdminLibrarianController::class, 'store'], $adminMiddleware);
$router->post('/admin/librarian/edit/{id}', [AdminLibrarianController::class, 'update'], $adminMiddleware);
$router->post('/admin/users/create', [AdminUserController::class, 'store'], $adminMiddleware);
$router->post('/admin/users/edit/{id}', [AdminUserController::class, 'update'], $adminMiddleware);
$router->post('/admin/settings/update', [AdminSettingsController::class, 'update'], $adminMiddleware);
$router->post('/admin/roles/update/{id}', [AdminRoleController::class, 'update'], $adminMiddleware);
$router->post('/admin/fines/update', [AdminFineController::class, 'update'], $adminMiddleware);

// ---- Librarian area (role = librarian) ----
$router->get('/librarian/dashboard', [LibrarianDashboardController::class, 'index'], $librarianMiddleware);

$router->get('/librarian/books', [BookController::class, 'librarianIndex'], array_merge($librarianMiddleware, [$authorizationCheck('view_books')]));
$router->get('/librarian/books/create', [BookController::class, 'create'], array_merge($librarianMiddleware, [$authorizationCheck('create_books')]));
$router->get('/librarian/books/edit/{id}', [BookController::class, 'edit'], array_merge($librarianMiddleware, [$authorizationCheck('edit_books')]));
$router->get('/librarian/books/delete/{id}', [BookController::class, 'delete'], array_merge($librarianMiddleware, [$authorizationCheck('delete_books')]));

$router->get('/librarian/loans', [LoanController::class, 'index'], array_merge($librarianMiddleware, [$authorizationCheck('view_loans')]));
$router->get('/librarian/loans/create', [LoanController::class, 'create'], array_merge($librarianMiddleware, [$authorizationCheck('create_loans')]));
$router->get('/librarian/loans/edit/{id}', [LoanController::class, 'edit'], array_merge($librarianMiddleware, [$authorizationCheck('edit_loans')]));
$router->get('/librarian/loans/delete/{id}', [LoanController::class, 'delete'], array_merge($librarianMiddleware, [$authorizationCheck('delete_loans')]));

$router->get('/librarian/users', [UserController::class, 'index'], array_merge($librarianMiddleware, [$authorizationCheck('view_users')]));
$router->get('/librarian/users/create', [UserController::class, 'create'], array_merge($librarianMiddleware, [$authorizationCheck('create_users')]));
$router->get('/librarian/users/edit/{id}', [UserController::class, 'edit'], array_merge($librarianMiddleware, [$authorizationCheck('edit_users')]));
$router->get('/librarian/users/delete/{id}', [UserController::class, 'delete'], array_merge($librarianMiddleware, [$authorizationCheck('delete_users')]));

// ----- Payment routes (FIXED: index route placed FIRST) -----
$router->get('/librarian/payments', [LibrarianPaymentController::class, 'index'], 
    array_merge($librarianMiddleware, [$authorizationCheck('view_payments')])
);

// Refund (GET form + POST process)
$router->get('/librarian/payments/{id}/refund', [LibrarianPaymentController::class, 'showRefundForm'], 
    array_merge($librarianMiddleware, [$authorizationCheck('refund_payments')])
);
$router->post('/librarian/payments/{id}/refund', [LibrarianPaymentController::class, 'processRefund'], 
    array_merge($librarianMiddleware, [$authorizationCheck('refund_payments')])
);

// Approve / Reject
$router->post('/librarian/payments/{id}/approve', [LibrarianPaymentController::class, 'approve'], 
    array_merge($librarianMiddleware, [$authorizationCheck('edit_payments')])
);
$router->post('/librarian/payments/{id}/reject', [LibrarianPaymentController::class, 'reject'], 
    array_merge($librarianMiddleware, [$authorizationCheck('edit_payments')])
);

// Generic show (payment details) – this must come AFTER the exact '/payments' route
$router->get('/librarian/payments/{id}', [LibrarianPaymentController::class, 'show'], 
    array_merge($librarianMiddleware, [$authorizationCheck('view_payments')])
);

// Invoice (view invoice)
$router->get('/librarian/payments/invoice/{id}', [LibrarianPaymentController::class, 'viewInvoice'], 
    array_merge($librarianMiddleware, [$authorizationCheck('view_payments')])
);

// ----- 🆕 Refund Management (separate listing & actions) -----
$router->get('/librarian/refunds', [RefundController::class, 'index'], 
    array_merge($librarianMiddleware, [$authorizationCheck('view_payments')])
);
$router->post('/librarian/refunds/{id}/approve', [RefundController::class, 'approve'], 
    array_merge($librarianMiddleware, [$authorizationCheck('refund_payments')])
);
$router->post('/librarian/refunds/{id}/reject', [RefundController::class, 'reject'], 
    array_merge($librarianMiddleware, [$authorizationCheck('refund_payments')])
);

// ✅ Scan & Return routes
$router->get('/librarian/scan', [ScanController::class, 'scan'], 
    array_merge($librarianMiddleware, [$authorizationCheck('view_loans')])
);
$router->post('/librarian/scan/return', [ScanController::class, 'returnBook'], 
    array_merge($librarianMiddleware, [$authorizationCheck('edit_loans')])
);

// Categories
$router->get('/librarian/categories', [LibrarianCategoryController::class, 'index'], array_merge($librarianMiddleware, [$authorizationCheck('view_categories')]));
$router->get('/librarian/categories/create', [LibrarianCategoryController::class, 'create'], array_merge($librarianMiddleware, [$authorizationCheck('create_categories')]));
$router->get('/librarian/categories/delete/{id}', [LibrarianCategoryController::class, 'delete'], array_merge($librarianMiddleware, [$authorizationCheck('delete_categories')]));

// ---- POST routes for librarian ----
$router->post('/librarian/categories/store', [LibrarianCategoryController::class, 'store'], array_merge($librarianMiddleware, [$authorizationCheck('create_categories')]));
$router->post('/librarian/books/store', [BookController::class, 'store'], array_merge($librarianMiddleware, [$authorizationCheck('create_books')]));
$router->post('/librarian/books/update/{id}', [BookController::class, 'update'], array_merge($librarianMiddleware, [$authorizationCheck('edit_books')]));
$router->post('/librarian/loans/store', [LoanController::class, 'store'], array_merge($librarianMiddleware, [$authorizationCheck('create_loans')]));
$router->post('/librarian/loans/update/{id}', [LoanController::class, 'update'], array_merge($librarianMiddleware, [$authorizationCheck('edit_loans')]));
$router->post('/librarian/loans/return/{id}', [LoanController::class, 'returnBook'], array_merge($librarianMiddleware, [$authorizationCheck('edit_loans')]));
$router->post('/librarian/loans/confirm/{id}', [LoanController::class, 'confirm'], array_merge($librarianMiddleware, [$authorizationCheck('edit_loans')]));
$router->post('/librarian/loans/reject/{id}', [LoanController::class, 'reject'], array_merge($librarianMiddleware, [$authorizationCheck('edit_loans')]));
$router->post('/librarian/users/store', [UserController::class, 'store'], array_merge($librarianMiddleware, [$authorizationCheck('create_users')]));
$router->post('/librarian/users/update/{id}', [UserController::class, 'update'], array_merge($librarianMiddleware, [$authorizationCheck('edit_users')]));

// ---- Public (authenticated) book viewing ----
$router->get('/books', [BookController::class, 'publicIndex'], [AuthMiddleware::class, $authorizationCheck('view_books')]);
$router->get('/books/{id}', [BookController::class, 'show'], [AuthMiddleware::class, $authorizationCheck('view_books')]);

// ---- API & misc ----
$router->get('/api/notifications', [NotificationController::class, 'getNotifications'], [AuthMiddleware::class, $authorizationCheck('view_notifications')]);
$router->post('/api/notifications/read', [NotificationController::class, 'markRead'], [AuthMiddleware::class, $authorizationCheck('edit_notifications')]);

// ---- Dev: seed notifications (localhost only) ----
$router->get('/dev/seed-notifications', function () {
    // Only allow from local dev environment
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (!in_array($ip, ['127.0.0.1', '::1'])) {
        http_response_code(403);
        echo 'Forbidden';
        return;
    }

    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_authenticated']) || $_SESSION['user_authenticated'] !== true) {
        http_response_code(401);
        echo 'Unauthorized - please log in first';
        return;
    }

    $userId = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['user_role'] ?? 'user';
    if (!$userId) {
        http_response_code(400);
        echo 'Cannot determine user id from session';
        return;
    }

    $db = $GLOBALS['container']->get('db');

    $stmt = $db->prepare("INSERT INTO notifications (user_id, role, type, title, message, link, is_read, created_at) VALUES (:user_id, :role, :type, :title, :message, :link, 0, :created_at)");
    $now = (new DateTime())->format('Y-m-d H:i:s');

    $samples = [
        ['type' => 'info', 'title' => 'Welcome back!', 'message' => 'Thanks for logging in. Check out the new books in the catalog.'],
        ['type' => 'loan', 'title' => 'Loan due soon', 'message' => 'One of your loans is due in 3 days. Please return or extend it.'],
        ['type' => 'payment', 'title' => 'Payment received', 'message' => 'Your payment was approved. Thank you!']
    ];

    foreach ($samples as $s) {
        $stmt->execute([
            ':user_id' => $userId,
            ':role' => $role,
            ':type' => $s['type'],
            ':title' => $s['title'],
            ':message' => $s['message'],
            ':link' => null,
            ':created_at' => $now,
        ]);
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'inserted' => count($samples)]);
}, [AuthMiddleware::class]);

// ---- Fallback redirects ----
$router->get('/admin/login', function () { header('Location: ' . BASE_URL . '/login'); exit; });
$router->get('/librarian/login', function () { header('Location: ' . BASE_URL . '/login'); exit; });
$router->get('/librarian/logout', function () {
    if (isset($_SESSION['librarian_logged_in'])) {
        unset($_SESSION['librarian_logged_in']);
        unset($_SESSION['librarian_id']);
        unset($_SESSION['librarian_name']);
        unset($_SESSION['librarian_department']);
    }
    header('Location: ' . BASE_URL . '/login');
    exit;
});

// ---- Verification & auth posts ----
$router->post('/verify-phone', [VerificationController::class, 'verifyPhone']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/verify-email-code', [VerificationController::class, 'verifyEmailWithCode']);
$router->post('/login', [LoginController::class, 'login']);

$router->get('/librarian/scanner', [ScanController::class, 'scanner'], 
    array_merge($librarianMiddleware, [$authorizationCheck('view_loans')])
);