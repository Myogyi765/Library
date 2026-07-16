<?php
// ================================================================
// Librarian Dashboard – Single Layout (includes header, sidebar, footer)
// ================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Authentication check
if (!isset($_SESSION['user_authenticated']) || $_SESSION['user_authenticated'] !== true) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'librarian') {
    http_response_code(403);
    echo '<h1>403 Forbidden</h1><p>You do not have librarian access.</p>';
    exit;
}

// $pageTitle should be set by the controller
$pageTitle = $pageTitle ?? 'Librarian Dashboard';
$page = $_GET['page'] ?? 'dashboard'; // Get page from URL

// Include header
include BASE_PATH . '/view/layout/header.php';
?>

<div class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- ===== SIDEBAR (Narrower & Blue Theme) ===== -->
    <aside class="w-56 bg-white dark:bg-gray-800 shadow-lg flex flex-col fixed inset-y-0 left-0 z-30 transition-all duration-300">
        <!-- Brand -->
        <div class="flex items-center justify-center h-16 border-b border-gray-200 dark:border-gray-700">
            <a href="<?= BASE_URL ?>/librarian/dashboard" class="flex items-center gap-2">
                <i class="fas fa-book-open text-blue-600 dark:text-blue-400 text-2xl"></i>
                <span class="text-xl font-bold text-gray-800 dark:text-white">Library Staff</span>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
            <!-- Dashboard -->
            <a href="<?= BASE_URL ?>/librarian/dashboard?page=dashboard" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $page === 'dashboard' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-chart-pie w-5 text-center <?= $page === 'dashboard' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' ?>"></i>
                <span>Dashboard</span>
            </a>

            <!-- Users -->
            <a href="<?= BASE_URL ?>/librarian/dashboard?page=users" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $page === 'users' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-users w-5 text-center <?= $page === 'users' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' ?>"></i>
                <span>Users</span>
            </a>

            <!-- Books -->
            <a href="<?= BASE_URL ?>/librarian/dashboard?page=books" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $page === 'books' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-book w-5 text-center <?= $page === 'books' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' ?>"></i>
                <span>Books</span>
            </a>

            <!-- Loans -->
            <a href="<?= BASE_URL ?>/librarian/dashboard?page=loans" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $page === 'loans' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-hand-holding w-5 text-center <?= $page === 'loans' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' ?>"></i>
                <span>Loans</span>
            </a>

            <!-- Payments -->
            <a href="<?= BASE_URL ?>/librarian/dashboard?page=payments" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $page === 'payments' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-credit-card w-5 text-center <?= $page === 'payments' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' ?>"></i>
                <span>Payments</span>
            </a>

            <!-- Refunds -->
            <a href="<?= BASE_URL ?>/librarian/dashboard?page=refunds" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $page === 'refunds' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-undo-alt w-5 text-center <?= $page === 'refunds' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' ?>"></i>
                <span>Refunds</span>
            </a>

            <!-- Settings -->
            <a href="<?= BASE_URL ?>/librarian/dashboard?page=settings" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $page === 'settings' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-cog w-5 text-center <?= $page === 'settings' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' ?>"></i>
                <span>Settings</span>
            </a>
        </nav>

        <!-- User Profile & Logout -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-4 space-y-2">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <i class="fas fa-user-graduate text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800 dark:text-white"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Librarian') ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Librarian</p>
                </div>
            </div>
            <!-- Logout button (optional) -->
            <a href="<?= BASE_URL ?>/logout" class="flex items-center gap-3 px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                <i class="fas fa-sign-out-alt w-5 text-center text-red-500"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT (margin adjusted for narrower sidebar) ===== -->
    <main class="flex-1 ml-56 p-8 overflow-y-auto" id="main-content">
        <?php
        // ✅ Extract view data passed from DashboardController
        if (isset($viewData) && is_array($viewData)) {
            extract($viewData);
        }

        // Ensure $page is set (fallback)
        $page = $page ?? 'dashboard';
        $stats = $stats ?? [];
        $loans = $loans ?? [];
        $users = $users ?? [];
        $books = $books ?? [];

        // ✅ Include the content file (handles page switching)
        include BASE_PATH . '/view/librarian/dashboard-content.php';
        ?>
    </main>
</div>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>