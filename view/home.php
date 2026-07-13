<?php
// ================================================================
// ✅ FIXED: Proper path handling
// ================================================================

if (!defined('BASE_PATH')) {
    $basePath = dirname(__DIR__);
    if (basename($basePath) === 'view') {
        $basePath = dirname($basePath);
    }
    define('BASE_PATH', $basePath);
}

if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    define('BASE_URL', $protocol . '://' . $host . $scriptDir);
}

// Debug (remove in production)
error_log("✅ home.php - BASE_PATH: " . BASE_PATH);
error_log("✅ home.php - BASE_URL: " . BASE_URL);

// ================================================================
// INCLUDE HEADER
// ================================================================

$headerPath = BASE_PATH . '/view/layout/header.php';
if (file_exists($headerPath)) {
    include $headerPath;
} else {
    $altPaths = [
        __DIR__ . '/layout/header.php',
        dirname(__DIR__) . '/view/layout/header.php',
    ];
    foreach ($altPaths as $altPath) {
        if (file_exists($altPath)) {
            include $altPath;
            break;
        }
    }
}
?>

<!-- ================================================================ -->
<!-- ===================== SUCCESS / ERROR MESSAGES ================= -->
<!-- ================================================================ -->

<?php 
$hasMessages = isset($_SESSION['register_success']) || 
               isset($_SESSION['login_success']) || 
               isset($_SESSION['warning_message']) || 
               (isset($_SESSION['login_errors']) && !empty($_SESSION['login_errors'])) || 
               (isset($_SESSION['register_errors']) && !empty($_SESSION['register_errors'])) || 
               isset($_SESSION['logout_success']) || 
               isset($_SESSION['success_message']) || 
               isset($_SESSION['error_message']);
?>

<?php if ($hasMessages): ?>
<div class="max-w-4xl mx-auto px-4 mt-3 space-y-2">
    <?php if (isset($_SESSION['register_success'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 text-green-800 dark:text-green-300 p-3 rounded-lg flex items-start gap-2 text-sm" role="alert">
            <i class="fas fa-check-circle text-green-500 text-lg mt-0.5"></i>
            <div>
                <p class="font-semibold"><?php echo htmlspecialchars($_SESSION['register_success']); ?></p>
                <?php if (isset($_SESSION['verification_message'])): ?>
                    <p class="text-xs mt-0.5 opacity-90"><?php echo htmlspecialchars($_SESSION['verification_message']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php unset($_SESSION['register_success'], $_SESSION['verification_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['login_success'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 text-green-800 dark:text-green-300 p-3 rounded-lg flex items-start gap-2 text-sm" role="alert">
            <i class="fas fa-check-circle text-green-500 text-lg mt-0.5"></i>
            <p class="font-semibold"><?php echo htmlspecialchars($_SESSION['login_success']); ?></p>
        </div>
        <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['warning_message'])): ?>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 text-yellow-800 dark:text-yellow-300 p-3 rounded-lg flex items-start gap-2 text-sm" role="alert">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-lg mt-0.5"></i>
            <div>
                <p><?php echo htmlspecialchars($_SESSION['warning_message']); ?></p>
                <?php if (isset($_SESSION['warning_action'])): ?>
                    <a href="<?php echo htmlspecialchars($_SESSION['warning_action']); ?>" class="inline-block mt-1 px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-colors text-xs">
                        <?php echo htmlspecialchars($_SESSION['warning_action_text'] ?? 'Resend Verification'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php unset($_SESSION['warning_message'], $_SESSION['warning_action'], $_SESSION['warning_action_text']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['login_errors']) && !empty($_SESSION['login_errors'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 text-red-800 dark:text-red-300 p-3 rounded-lg flex items-start gap-2 text-sm" role="alert">
            <i class="fas fa-exclamation-circle text-red-500 text-lg mt-0.5"></i>
            <ul class="list-disc list-inside space-y-0.5">
                <?php foreach ($_SESSION['login_errors'] as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['login_errors']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['register_errors']) && !empty($_SESSION['register_errors'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 text-red-800 dark:text-red-300 p-3 rounded-lg flex items-start gap-2 text-sm" role="alert">
            <i class="fas fa-exclamation-circle text-red-500 text-lg mt-0.5"></i>
            <ul class="list-disc list-inside space-y-0.5">
                <?php foreach ($_SESSION['register_errors'] as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['register_errors']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['logout_success'])): ?>
        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 text-blue-800 dark:text-blue-300 p-3 rounded-lg flex items-start gap-2 text-sm" role="alert">
            <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5"></i>
            <p><?php echo htmlspecialchars($_SESSION['logout_success']); ?></p>
        </div>
        <?php unset($_SESSION['logout_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 text-green-800 dark:text-green-300 p-3 rounded-lg flex items-start gap-2 text-sm" role="alert">
            <i class="fas fa-check-circle text-green-500 text-lg mt-0.5"></i>
            <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 text-red-800 dark:text-red-300 p-3 rounded-lg flex items-start gap-2 text-sm" role="alert">
            <i class="fas fa-exclamation-circle text-red-500 text-lg mt-0.5"></i>
            <p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ================================================================ -->
<!-- ===================== HERO BANNER ============================== -->
<!-- ================================================================ -->

<section class="relative w-full h-[300px] flex items-center justify-center text-white text-center overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/50 to-black/70 z-10"></div>
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
         style="background-image: url('<?php echo BASE_URL; ?>/images/library-banner.jpg');"></div>

    <div class="relative z-20 px-4 max-w-2xl mx-auto">
        <h1 class="text-3xl sm:text-4xl font-bold mb-2 drop-shadow-lg">
            Welcome to <span class="text-blue-300">Library</span> Management
        </h1>
        <p class="text-base sm:text-lg text-gray-200/90 mb-5">
            Discover, Borrow, and Manage Knowledge.
        </p>

        <!-- Search bar – changed button color to blue -->
        <form action="<?php echo BASE_URL; ?>/books" method="GET" class="flex max-w-sm mx-auto bg-white/10 backdrop-blur-sm rounded-lg shadow-lg p-0.5 border border-white/20">
            <input type="text" name="q" placeholder="Search books..."
                   class="flex-1 p-2.5 bg-transparent text-white placeholder-white/70 border-none outline-none rounded-l-lg text-sm">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition-all hover:scale-105 flex items-center gap-1.5 text-sm">
                <i class="fas fa-search"></i>
                <span class="hidden sm:inline">Search</span>
            </button>
        </form>

        <div class="flex flex-wrap justify-center gap-2.5 mt-6">
            <a href="<?php echo BASE_URL; ?>/books"
               class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white font-medium rounded-lg transition border border-white/30 text-sm">
                <i class="fas fa-book-open mr-1.5"></i>Browse Catalog
            </a>
            <?php if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])): ?>
                <button id="open-register-modal"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-md text-sm">
                    <i class="fas fa-user-plus mr-1.5"></i>Join Now
                </button>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/user-dashboard"
                   class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white font-semibold rounded-lg transition shadow-md text-sm">
                    <i class="fas fa-user mr-1.5"></i>My Dashboard
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ================================================================ -->
<!-- ===================== STATISTICS =============================== -->
<!-- ================================================================ -->

<section class="container mx-auto py-8 px-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
    <?php
    $stats = [
        '0+' => ['icon' => 'fa-book', 'label' => 'Books', 'color' => 'blue'],
        '+'  => ['icon' => 'fa-users', 'label' => 'Members', 'color' => 'green'],
        '1+' => ['icon' => 'fa-chart-line', 'label' => 'Daily Visitors', 'color' => 'purple']
    ];
    foreach ($stats as $stat => $data):
        $color = $data['color'];
    ?>
    <div class="group bg-white dark:bg-gray-900/80 rounded-lg shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5 p-4 text-center border border-gray-100 dark:border-gray-800">
        <div class="w-12 h-12 bg-<?php echo $color; ?>-100 dark:bg-<?php echo $color; ?>-900/30 rounded-full flex items-center justify-center mx-auto mb-2 group-hover:scale-105 transition">
            <i class="fas <?php echo $data['icon']; ?> text-xl text-<?php echo $color; ?>-600 dark:text-<?php echo $color; ?>-400"></i>
        </div>
        <h2 class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo $stat; ?></h2>
        <p class="text-gray-600 dark:text-gray-400 text-xs font-medium"><?php echo $data['label']; ?></p>
    </div>
    <?php endforeach; ?>
</section>

<!-- ================================================================ -->
<!-- ===================== FEATURES SECTION ========================= -->
<!-- ================================================================ -->

<section class="bg-gray-50 dark:bg-gray-800/30 py-10">
    <div class="container mx-auto px-4">
        <h2 class="text-xl sm:text-2xl font-bold text-center text-gray-900 dark:text-white mb-6">Why Choose Our Library?</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <?php
            $features = [
                ['icon' => 'fa-clock', 'title' => '24/7 Access', 'color' => 'blue'],
                ['icon' => 'fa-shield-alt', 'title' => 'Secure & Safe', 'color' => 'green'],
                ['icon' => 'fa-sync', 'title' => 'Easy Returns', 'color' => 'purple'],
                ['icon' => 'fa-headset', 'title' => '24/7 Support', 'color' => 'indigo']
            ];
            foreach ($features as $feature):
                $color = $feature['color'];
            ?>
            <div class="bg-white dark:bg-gray-900/60 rounded-lg shadow-sm hover:shadow-md transition hover:-translate-y-0.5 p-4 text-center border border-gray-100 dark:border-gray-800">
                <div class="w-12 h-12 bg-<?php echo $color; ?>-100 dark:bg-<?php echo $color; ?>-900/30 rounded-xl flex items-center justify-center mx-auto mb-2 transition group-hover:scale-105">
                    <i class="fas <?php echo $feature['icon']; ?> text-xl text-<?php echo $color; ?>-600 dark:text-<?php echo $color; ?>-400"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white"><?php echo $feature['title']; ?></h3>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================================================================ -->
<!-- ===================== CTA SECTION ============================== -->
<!-- ================================================================ -->

<?php if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])): ?>
<section class="relative py-10 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800"></div>
    <div class="relative z-10 container mx-auto px-4 text-center">
        <h2 class="text-xl sm:text-2xl font-bold text-white dark:text-gray-100 mb-2">
            Ready to Get Started?
        </h2>
        <p class="text-blue-100 dark:text-gray-300 text-sm mb-4 max-w-lg mx-auto">
            Join our community of book lovers and start your reading journey today.
        </p>
        <button id="open-register-modal"
                class="px-6 py-2.5 bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 font-bold rounded-lg shadow-md hover:shadow-lg hover:scale-105 transition inline-flex items-center gap-2 text-sm">
            <i class="fas fa-user-plus"></i> Create Free Account
        </button>
    </div>
</section>
<?php endif; ?>

<!-- ================================================================ -->
<!-- ===================== FOOTER =================================== -->
<!-- ================================================================ -->

<?php
$footerPath = BASE_PATH . '/view/layout/footer.php';
if (file_exists($footerPath)) {
    include $footerPath;
} else {
    $altPaths = [
        __DIR__ . '/layout/footer.php',
        dirname(__DIR__) . '/view/layout/footer.php',
    ];
    foreach ($altPaths as $altPath) {
        if (file_exists($altPath)) {
            include $altPath;
            break;
        }
    }
}
?>