<?php
// ================================================================
// ✅ HEADER INCLUDE PROTECTION – Prevent infinite recursion
// ================================================================
if (defined('HEADER_LOADED')) {
    return;
}
define('HEADER_LOADED', true);

ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure constants
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    define('BASE_URL', $protocol . '://' . $host . $scriptDir);
}

if (!defined('BASE_PATH')) {
    $possiblePaths = [
        dirname(__DIR__, 2),
        dirname(__DIR__, 3),
        dirname(__DIR__),
    ];
    foreach ($possiblePaths as $path) {
        if (file_exists($path . '/view')) {
            define('BASE_PATH', $path);
            break;
        }
    }
    if (!defined('BASE_PATH')) {
        define('BASE_PATH', dirname(__DIR__, 2));
    }
}

$pageTitle = $pageTitle ?? 'Library Management System';

// ---- Notification bell visibility ----
$showNotifications = false; // Start with false

// If user is not logged in, skip everything
if (isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true) {
    // User is logged in – check permission if possible
    $authClass = \App\Shared\Core\Authorization\Authorization::class;
    $container = $GLOBALS['container'] ?? null;

    if ($container && $container->has($authClass)) {
        try {
            $authorization = $container->get($authClass);
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
                $authorization->loadUserPermissions($_SESSION['user_id']);
                $showNotifications = $authorization->hasPermission('view_notifications');
                error_log('🔔 [DEBUG] Permission check passed, showNotifications = ' . ($showNotifications ? 'true' : 'false'));
            } else {
                error_log('🔔 [DEBUG] User ID not set in session');
            }
        } catch (\Exception $e) {
            error_log('❌ Permission check error: ' . $e->getMessage());
            // Fallback: show bell if user is logged in (even without permission check)
            $showNotifications = true;
        }
    } else {
        // Container or Authorization service not available – fallback to login status
        $showNotifications = true;
        error_log('⚠️ [DEBUG] Container/Authorization not found, showing bell anyway (fallback)');
    }
}

error_log('🔔 [FINAL] showNotifications = ' . ($showNotifications ? 'true' : 'false'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Optional login styles -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/login.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        lib: {
                            blue: '#0056b3',
                            gold: '#ffc107'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* ================================================================
           HEADER STYLES (navigation only)
           ================================================================ */
        * {
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .nav-link-blue {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.9rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        .nav-link-blue {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            line-height: 1;
            border-radius: 0.85rem;
            padding: 0.55rem 0.95rem;
            min-height: 2.85rem;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        .nav-link-blue:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.14);
            transform: translateY(-1px);
        }
        .nav-link-blue i,
        .mobile-link-blue i {
            font-size: 1rem;
            width: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .dark .nav-link-blue {
            color: rgba(255, 255, 255, 0.9);
        }
        .dark .nav-link-blue:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .theme-link-blue {
            cursor: pointer;
        }
        .theme-link-blue i {
            transition: transform 0.3s ease, color 0.3s ease;
        }
        .theme-link-blue:hover i {
            transform: rotate(25deg) scale(1.15);
            color: #fbbf24;
        }

        .login-btn, .register-btn {
            border-radius: 9999px;
            padding: 0.4rem 1.2rem;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.01em;
        }
        .login-btn {
            border: 1.5px solid #3b82f6;
            background: transparent;
            color: #ffffff;
        }
        .login-btn:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: #60a5fa;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
            transform: translateY(-2px) scale(1.02);
        }
        .register-btn {
            background: #ffffff;
            color: #4853cf;
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .register-btn:hover {
            background: #dbdbdb;
            box-shadow: 0 8px 25px rgba(34, 77, 170, 0.5);
            transform: translateY(-3px) scale(1.03);
        }
        .dark .login-btn {
            border-color: #60a5fa;
            color: #93c5fd;
        }
        .dark .login-btn:hover {
            background: rgba(96, 165, 250, 0.15);
            border-color: #93c5fd;
            box-shadow: 0 0 20px rgba(96, 165, 250, 0.3);
        }
        .dark .register-btn {
            background: #3b82f6;
            color: #ffffff;
        }
        .dark .register-btn:hover {
            background: #2563eb;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .mobile-link-blue {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.68rem 1rem;
            border-radius: 0.9rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .mobile-link-blue:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.16);
            transform: translateX(1px);
        }
        .mobile-link-blue i {
            width: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .logout-btn:active {
            transform: scale(0.95);
        }
        .dark .logout-btn {
            background: #1f2937;
            border-color: #4b5563;
            color: #f87171;
        }
        .dark .logout-btn:hover {
            background: #374151;
            color: #fca5a5;
            border-color: #6b7280;
        }

        #notification-dropdown {
            max-height: 420px;
            overflow-y: auto;
            min-width: 320px;
            max-width: 360px;
            border-radius: 1rem;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
            padding: 0.25rem;
            background-clip: padding-box;
        }
        #notification-dropdown .notification-item {
            border-radius: 0.85rem;
        }
        #notification-badge {
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.35rem;
            line-height: 1.15;
            font-weight: 700;
        }
        #notification-badge:not(.hidden) {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .notification-item {
            transition: background 0.2s ease, transform 0.2s ease;
            border-radius: 0.75rem;
        }
        .notification-item:hover {
            background: rgba(59, 130, 246, 0.08);
            transform: translateX(1px);
        }
        .dark .notification-item:hover {
            background: rgba(59, 130, 246, 0.15);
        }
    </style>

    <!-- ============================================================== -->
    <!-- 🔔 SET BASE_URL DIRECTLY FROM PHP – MOST RELIABLE              -->
    <!-- ============================================================== -->
    <script>
        window.BASE_URL = '<?php echo BASE_URL; ?>';
        console.log('🔔 [PHP] BASE_URL set to:', window.BASE_URL);
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors duration-300">

<!-- ===== NAVIGATION ===== -->
<nav class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-slate-800 dark:to-slate-900
            border-b border-white/10 py-3 px-4 sticky top-0 z-50 shadow-md">
    <div class="container mx-auto flex items-center justify-between">
        <!-- Logo -->
        <a href="<?php echo BASE_URL; ?>/home" class="flex items-center gap-3 group">
            <div class="w-12 h-12 rounded-full bg-white border-2 border-white/30
                        flex items-center justify-center overflow-hidden shadow-sm
                        transition-transform duration-300 group-hover:scale-105">
                <img src="<?php echo BASE_URL; ?>/images/logo.png"
                     class="w-full h-full object-cover"
                     alt="Library Logo"
                     onerror="this.style.display='none'">
            </div>
            <span class="text-xl font-bold text-white">Library</span>
        </a>

        <!-- Desktop Nav -->
        <div class="hidden md:flex items-center gap-1 ml-auto">
            <a href="<?php echo BASE_URL; ?>/home" class="nav-link-blue">Home</a>
            <a href="<?php echo BASE_URL; ?>/books" class="nav-link-blue">Catalog</a>
            <button id="theme-toggle" class="nav-link-blue theme-link-blue">
                <i id="theme-icon" class="fas fa-moon"></i>
            </button>

            <?php
            $isLoggedIn = isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;

            if ($isLoggedIn):
                $role = $_SESSION['user_role'] ?? 'user';
                $dashboardLink = match($role) {
                    'admin'     => '/admin/dashboard',
                    'librarian' => '/librarian/dashboard',
                    default     => '/user-dashboard'
                };
            ?>
                <a href="<?php echo BASE_URL . $dashboardLink; ?>" class="nav-link-blue">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>

                <?php if ($showNotifications): ?>
                    <!-- Notification Bell -->
                    <div class="relative inline-block" id="notification-container">
                        <button id="notification-bell" class="nav-link-blue relative px-3 py-2" title="Notifications" type="button">
                            <i class="fa-solid fa-bell"></i>
                            <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full px-1.5 py-0.5 leading-none min-w-[20px] text-center hidden">0</span>
                        </button>
                        <div id="notification-dropdown" class="absolute right-0 mt-2 hidden z-50 overflow-hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xl rounded-2xl">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-800 z-10">
                                <span class="font-semibold text-gray-900 dark:text-white">Notifications</span>
                                <button id="mark-all-read" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Mark all as read</button>
                            </div>
                            <div id="notification-list" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">No notifications</div>
                            </div>
                            <div class="p-3 border-t border-gray-200 dark:border-gray-700 text-center bg-gray-50 dark:bg-gray-900">
                                <a href="#" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View all</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <a href="<?php echo BASE_URL; ?>/logout"
                   class="nav-link-blue bg-white text-red-600 hover:bg-red-100 hover:text-red-600 border border-red-200 rounded-full px-4 py-1.5 transition logout-btn">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/login" class="nav-link-blue login-btn">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
                <a href="<?php echo BASE_URL; ?>/register" class="nav-link-blue register-btn">
                    <i class="fa-solid fa-user-plus"></i> Register
                </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Toggle -->
        <button id="mobile-menu-button" class="md:hidden text-white text-xl p-2 hover:bg-white/10 rounded-lg transition" type="button">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden mt-3 bg-white/10 backdrop-blur-md rounded-xl border border-white/20 p-3 space-y-1">
        <a href="<?php echo BASE_URL; ?>/home" class="mobile-link-blue"><i class="fa-solid fa-house"></i> Home</a>
        <a href="<?php echo BASE_URL; ?>/books" class="mobile-link-blue"><i class="fa-solid fa-book-open"></i> Catalog</a>
        <?php if ($isLoggedIn): ?>
            <a href="<?php echo BASE_URL . $dashboardLink; ?>" class="mobile-link-blue">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            <?php if ($showNotifications): ?>
                <div class="relative inline-block w-full">
                    <button id="mobile-notification-bell" class="mobile-link-blue w-full text-left px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20" type="button">
                        <i class="fa-solid fa-bell"></i>
                        <span class="ml-2">Notifications</span>
                        <span id="mobile-notification-badge" class="ml-auto bg-red-500 text-white text-[10px] rounded-full px-2 py-0.5 hidden">0</span>
                    </button>
                </div>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/logout"
               class="mobile-link-blue bg-red-100 hover:bg-red-200 text-red-700 rounded-lg px-4 py-2 transition mobile-logout-btn">
                <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/login" class="mobile-link-blue"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            <a href="<?php echo BASE_URL; ?>/register" class="mobile-link-blue bg-white/20 text-white font-semibold rounded-lg"><i class="fa-solid fa-user-plus"></i> Register Now</a>
        <?php endif; ?>
    </div>
</nav>

<script src="<?php echo BASE_URL; ?>/js/header.js"></script>
</body>
</html>