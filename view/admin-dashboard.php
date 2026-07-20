<?php
// ================================================================
// Admin Dashboard – Single Layout (includes header, sidebar, footer)
// ================================================================

// ✅ Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Use UserAuthenticator's session variables
if (!isset($_SESSION['user_authenticated']) || $_SESSION['user_authenticated'] !== true) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo '<h1>403 Forbidden</h1><p>You do not have admin access.</p>';
    exit;
}

// $pageTitle should be set by the controller
$pageTitle = $pageTitle ?? 'Admin Dashboard';

// ✅ Include header – this is the ONLY place header is included
include BASE_PATH . '/view/layout/header.php';

// Stats for dashboard (fallback if controller data is not provided)
if (!isset($stats) || !is_array($stats)) {
    $stats = [
        'users' => 245,
        'librarian' => 7,
        'books' => 1840,
        'available' => 1235,
        'borrowed' => 605,
        'activeLoans' => 62,
        'overdue' => 11,
    ];
}

// ✅ Unread notifications count (should be passed from controller)
$unreadNotifications = $unreadNotifications ?? 0;
?>

<div class="flex h-screen bg-gray-50 dark:bg-gray-900">
    <!-- ===== SIDEBAR ===== -->
    <aside
        class="w-64 bg-white dark:bg-gray-800 shadow-lg flex flex-col fixed inset-y-0 left-0 z-30 transition-all duration-300">
        <!-- Brand -->
        <div class="flex items-center justify-center h-16 border-b border-gray-200 dark:border-gray-700">
            <!-- <a href="<?= BASE_URL ?>/admin/dashboard" class="flex items-center gap-2">
                <i class="fas fa-book-open text-blue-600 dark:text-blue-400 text-2xl"></i>
                <span class="text-xl font-bold text-gray-800 dark:text-white">Library Admin</span>
            </a> -->
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <!-- Dashboard (Blue) -->
            <a href="<?= BASE_URL ?>/admin/dashboard"
                class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg <?= strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-chart-pie w-5 text-center <?= strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false ? 'text-blue-600 dark:text-blue-400' : 'text-blue-500 dark:text-blue-400' ?>"></i>
                <span>Dashboard</span>
            </a>

        

            <!-- Librarian (Purple) -->
            <a href="<?= BASE_URL ?>/admin/librarian"
                class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg <?= strpos($_SERVER['REQUEST_URI'], '/admin/librarian') !== false ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-user-graduate w-5 text-center <?= strpos($_SERVER['REQUEST_URI'], '/admin/librarian') !== false ? 'text-blue-600 dark:text-blue-400' : 'text-purple-500 dark:text-purple-400' ?>"></i>
                <span>Librarian</span>
            </a>

            <!-- Users (Green) -->
            <a href="<?= BASE_URL ?>/admin/users"
                class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-users w-5 text-center <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false ? 'text-blue-600 dark:text-blue-400' : 'text-green-500 dark:text-green-400' ?>"></i>
                <span>Users</span>
            </a>

            <!-- Books (Amber/Orange) -->
            <a href="<?= BASE_URL ?>/admin/books"
                class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg <?= strpos($_SERVER['REQUEST_URI'], '/admin/books') !== false ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-book w-5 text-center <?= strpos($_SERVER['REQUEST_URI'], '/admin/books') !== false ? 'text-blue-600 dark:text-blue-400' : 'text-blue-500 dark:text-amber-400' ?>"></i>
                <span>Books</span>
            </a>

            <!-- Reports (Rose/Pink) -->
            <a href="<?= BASE_URL ?>/admin/reports"
                class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg <?= strpos($_SERVER['REQUEST_URI'], '/admin/reports') !== false ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-chart-bar w-5 text-center <?= strpos($_SERVER['REQUEST_URI'], '/admin/reports') !== false ? 'text-blue-600 dark:text-blue-400' : 'text-rose-500 dark:text-rose-400' ?>"></i>
                <span>Reports</span>
            </a>

            <!-- Fine & Fee Settings (Yellow) -->
            <a href="<?= BASE_URL ?>/admin/fines"
                class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg <?= strpos($_SERVER['REQUEST_URI'], '/admin/fines') !== false ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-coins w-5 text-center <?= strpos($_SERVER['REQUEST_URI'], '/admin/fines') !== false ? 'text-blue-600 dark:text-blue-400' : 'text-yellow-500 dark:text-yellow-400' ?>"></i>
                <span>Fine & Fee Settings</span>
            </a>

            

            <!-- ✅ Settings (Blue) -->
            <a href="<?= BASE_URL ?>/admin/settings"
                class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition">
                <i class="fas fa-cog w-5 text-center <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false ? 'text-blue-600 dark:text-blue-400' : 'text-blue-500 dark:text-blue-400' ?>"></i>
                <span>Settings</span>
            </a>
        </nav>

        <!-- User Profile & Logout -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3 px-2 py-2">
                <div
                    class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <i class="fas fa-user-shield text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="flex-1 ml-64 p-8 overflow-y-auto" id="main-content">
        <?php
        // ✅ Check if content is passed from controller
        if (isset($content) && file_exists($content)) {
            // ✅ Extract view data if provided
            if (isset($viewData) && is_array($viewData)) {
                extract($viewData);
            }
            include $content;
        } else {
            // Default dashboard content (fallback)
            include BASE_PATH . '/view/admin/dashboard-content.php';
        }
        ?>
    </main>
</div>