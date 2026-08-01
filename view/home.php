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
<!-- ✅ MAIN CONTENT WRAPPER – FIXED DARK MODE BACKGROUND            -->
<!-- ================================================================ -->
<div class="bg-slate-50 dark:bg-gray-900 min-h-screen transition-colors duration-300">

    <!-- ================================================================ -->
    <!-- ===================== TOAST NOTIFICATION ======================= -->
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
    <div class="fixed top-20 left-1/2 -translate-x-1/2 z-50 w-full max-w-md px-4 pointer-events-none">
        <div class="space-y-2 pointer-events-auto">
            
            <?php if (isset($_SESSION['register_success'])): ?>
                <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 shadow-xl rounded-xl p-4 flex items-start gap-3 animate-slideDown">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($_SESSION['register_success']); ?></p>
                        <?php if (isset($_SESSION['verification_message'])): ?>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo htmlspecialchars($_SESSION['verification_message']); ?></p>
                        <?php endif; ?>
                    </div>
                    <button onclick="this.closest('div.animate-slideDown').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['register_success'], $_SESSION['verification_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['login_success'])): ?>
                <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 shadow-xl rounded-xl p-4 flex items-start gap-3 animate-slideDown">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($_SESSION['login_success']); ?></p>
                    </div>
                    <button onclick="this.closest('div.animate-slideDown').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['login_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 shadow-xl rounded-xl p-4 flex items-start gap-3 animate-slideDown">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
                    </div>
                    <button onclick="this.closest('div.animate-slideDown').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 shadow-xl rounded-xl p-4 flex items-start gap-3 animate-slideDown">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
                    </div>
                    <button onclick="this.closest('div.animate-slideDown').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['warning_message'])): ?>
                <div class="bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-800 shadow-xl rounded-xl p-4 flex items-start gap-3 animate-slideDown">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($_SESSION['warning_message']); ?></p>
                        <?php if (isset($_SESSION['warning_action'])): ?>
                            <a href="<?php echo htmlspecialchars($_SESSION['warning_action']); ?>" class="inline-block mt-2 text-xs font-semibold text-yellow-700 dark:text-yellow-300 hover:underline">
                                <?php echo htmlspecialchars($_SESSION['warning_action_text'] ?? 'Resend Verification'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <button onclick="this.closest('div.animate-slideDown').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['warning_message'], $_SESSION['warning_action'], $_SESSION['warning_action_text']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['login_errors']) && !empty($_SESSION['login_errors'])): ?>
                <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 shadow-xl rounded-xl p-4 flex items-start gap-3 animate-slideDown">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="flex-1">
                        <ul class="list-disc list-inside text-sm text-gray-900 dark:text-white">
                            <?php foreach ($_SESSION['login_errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <button onclick="this.closest('div.animate-slideDown').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['login_errors']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['register_errors']) && !empty($_SESSION['register_errors'])): ?>
                <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 shadow-xl rounded-xl p-4 flex items-start gap-3 animate-slideDown">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="flex-1">
                        <ul class="list-disc list-inside text-sm text-gray-900 dark:text-white">
                            <?php foreach ($_SESSION['register_errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <button onclick="this.closest('div.animate-slideDown').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['register_errors']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['logout_success'])): ?>
                <div class="bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-800 shadow-xl rounded-xl p-4 flex items-start gap-3 animate-slideDown">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($_SESSION['logout_success']); ?></p>
                    </div>
                    <button onclick="this.closest('div.animate-slideDown').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['logout_success']); ?>
            <?php endif; ?>

        </div>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- ===================== 📺 ORIGINAL HERO BANNER =================== -->
    <!-- ================================================================ -->

    <section class="relative w-full h-[320px] sm:h-[350px] flex items-center justify-center text-white text-center overflow-hidden">
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
                    <!-- ✅ FIXED: Direct link to registration page -->
                    <a href="<?php echo BASE_URL; ?>/register"
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-md text-sm">
                        <i class="fas fa-user-plus mr-1.5"></i>Join Now
                    </a>
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
    <!-- ========== 🎨 CARTOON & WELCOME INTRO SECTION ================== -->
    <!-- ================================================================ -->

    <section class="py-12 bg-white dark:bg-gray-800/60 border-b border-gray-150 dark:border-gray-800">
        <div class="container mx-auto px-4 lg:px-8 max-w-6xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            
            <!-- Left Side: Cute Floating Cartoon Book mascot -->
            <div class="lg:col-span-5 flex justify-center items-center relative">
                <!-- Subtle pulsing backing glow -->
                <div class="absolute w-64 h-64 bg-blue-500/10 dark:bg-blue-500/20 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
                
                <!-- Friendly Book Cartoon Illustration SVG -->
                <svg class="w-64 h-64 sm:w-72 sm:h-72 md:w-80 md:h-80 drop-shadow-xl animate-float" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Magic floating sparkles -->
                    <path d="M120,80 L125,70 L130,80 L140,85 L130,90 L125,100 L120,90 L110,85 Z" fill="#FBBF24" opacity="0.8">
                        <animate attributeName="transform" type="translate" values="0,0; 0,-8; 0,0" dur="3s" repeatCount="indefinite"/>
                    </path>
                    <path d="M410,160 L413,152 L416,160 L424,163 L416,166 L413,174 L410,166 L402,163 Z" fill="#FBBF24" opacity="0.8">
                        <animate attributeName="transform" type="translate" values="0,0; 0,-12; 0,0" dur="4.5s" repeatCount="indefinite"/>
                    </path>

                    <!-- BOOK BASE COVER (Deep indigo) -->
                    <path d="M100,370 Q100,390 120,390 L380,390 Q400,390 400,370 L400,350 L100,350 Z" fill="#312E81"/>
                    <path d="M100,350 L400,350 L380,290 L120,290 Z" fill="#4338CA"/>
                    <path d="M115,370 L385,370 L380,380 L120,380 Z" fill="#F8FAFC"/>
                    <path d="M115,370 L385,370" stroke="#E2E8F0" stroke-width="2"/>

                    <!-- MIDDLE COVER (Bright Teal) -->
                    <path d="M120,280 Q120,300 140,300 L360,300 Q380,300 380,280 L380,260 L120,260 Z" fill="#0D9488"/>
                    <path d="M120,260 L380,260 L360,200 L140,200 Z" fill="#14B8A6"/>
                    <path d="M135,280 L365,280 L360,290 L140,290 Z" fill="#F8FAFC"/>

                    <!-- TOP OPEN BOOK (Vibrant Red-Orange with a cute face!) -->
                    <path d="M150,190 Q250,220 350,190 L350,170 Q250,200 150,170 Z" fill="#991B1B"/>
                    <!-- Left White Page -->
                    <path d="M250,190 Q190,170 150,180 L140,110 Q190,100 250,120 Z" fill="#FFFFFF"/>
                    <!-- Right White Page -->
                    <path d="M250,190 Q310,170 350,180 L360,110 Q310,100 250,120 Z" fill="#FFFFFF"/>
                    <!-- Cover Wings -->
                    <path d="M140,110 L135,115 L145,185 L150,180 Z" fill="#EF4444"/>
                    <path d="M360,110 L365,115 L355,185 L350,180 Z" fill="#EF4444"/>

                    <!-- Cute Eyes -->
                    <circle cx="200" cy="145" r="7.5" fill="#1E293B"/>
                    <circle cx="300" cy="145" r="7.5" fill="#1E293B"/>
                    <circle cx="198" cy="143" r="2.5" fill="#FFFFFF"/>
                    <circle cx="298" cy="143" r="2.5" fill="#FFFFFF"/>
                    <!-- Smiling Mouth -->
                    <path d="M242,148 Q250,157 258,148" stroke="#1E293B" stroke-width="3.5" stroke-linecap="round" fill="none"/>
                    <!-- Blushing Cheeks -->
                    <circle cx="184" cy="153" r="6" fill="#F87171" opacity="0.6"/>
                    <circle cx="316" cy="153" r="6" fill="#F87171" opacity="0.6"/>
                    
                    <!-- Graduation Cap -->
                    <path d="M250,55 L300,70 L250,85 L200,70 Z" fill="#1E293B"/>
                    <rect x="238" y="75" width="24" height="15" fill="#1E293B" rx="2"/>
                    <path d="M300,70 L310,95 M310,95 L314,95" stroke="#FBBF24" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="312" cy="97" r="4" fill="#FBBF24"/>
                </svg>
            </div>

            <!-- Right Side: Engaging Copywriting -->
            <div class="lg:col-span-7 space-y-5 text-center lg:text-left">
                <span class="inline-block px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 rounded-full text-xs font-bold tracking-wider uppercase">
                    ✨ Meet your Reading Buddy
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white leading-tight">
                    Unlock a Whole New Way to <span class="text-blue-600 dark:text-blue-400">Discover & Borrow</span> Books.
                </h2>
                <p class="text-slate-600 dark:text-gray-300 text-sm sm:text-base leading-relaxed">
                    We believe that managing your personal shelf should feel as magical as reading a great story itself. This intelligent companion keeps track of your due dates, simplifies digital reservations, and recommends your next favorite adventure depending on what you love to read!
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="flex items-center gap-3 justify-center lg:justify-start">
                        <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center text-green-600 dark:text-green-400">
                            <i class="fas fa-check text-sm"></i>
                        </div>
                        <span class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">Fast 1-Click Reservations</span>
                    </div>
                    <div class="flex items-center gap-3 justify-center lg:justify-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="fas fa-magic text-sm"></i>
                        </div>
                        <span class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">Intelligent Genre Filters</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Inline Style for Book Animation + Toast -->
    <style>
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(1deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float {
            animation: float 4.5s ease-in-out infinite;
        }

        /* Toast slide-down animation */
        @keyframes slideDown {
            0% { transform: translateY(-20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        .animate-slideDown {
            animation: slideDown 0.3s ease-out;
        }
    </style>

    <!-- ================================================================ -->
    <!-- ===================== STATISTICS =============================== -->
    <!-- ================================================================ -->

    <section class="container mx-auto py-10 px-4 grid grid-cols-1 sm:grid-cols-3 gap-6">
        <?php
        // Use dynamic data from controller
        $totalBooks = $stats['books'] ?? 0;
        $totalUsers = $stats['users'] ?? 0;
        $dailyVisitors = $stats['dailyVisitors'] ?? rand(5, 25);
        
        $statsData = [
            'books' => [
                'icon' => 'fa-book',
                'label' => 'Total Dynamic Volumes',
                'color' => 'blue',
                'value' => number_format($totalBooks)
            ],
            'members' => [
                'icon' => 'fa-users',
                'label' => 'Active Avid Readers',
                'color' => 'green',
                'value' => number_format($totalUsers)
            ],
            'visitors' => [
                'icon' => 'fa-chart-line',
                'label' => 'Interactive Daily Visitors',
                'color' => 'purple',
                'value' => number_format($dailyVisitors)
            ]
        ];
        ?>
        <?php foreach ($statsData as $stat): ?>
            <div class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1 p-6 text-center border border-gray-150 dark:border-gray-700">
                <div class="w-14 h-14 bg-<?php echo $stat['color']; ?>-50 dark:bg-<?php echo $stat['color']; ?>-900/20 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition duration-300">
                    <i class="fas <?php echo $stat['icon']; ?> text-2xl text-<?php echo $stat['color']; ?>-500 dark:text-<?php echo $stat['color']; ?>-400"></i>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 dark:text-white">
                    <?php echo $stat['value']; ?>
                </h2>
                <p class="text-slate-500 dark:text-gray-400 text-xs font-semibold uppercase tracking-wider mt-1"><?php echo $stat['label']; ?></p>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- ================================================================ -->
    <!-- ===================== 🌟 VISUAL GENRES SECTION ================= -->
    <!-- ================================================================ -->

    <section class="py-10 container mx-auto px-4">
        <div class="text-center max-w-xl mx-auto mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white mb-2">Explore Curated Genres</h2>
            <p class="text-slate-500 dark:text-gray-400 text-sm">Dive straight into our handpicked catalogs. There is a perfect genre for every mood.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Genre 1 -->
            <a href="<?php echo BASE_URL; ?>/books?q=Fiction" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 p-6 text-white shadow-md hover:shadow-xl transition duration-300 hover:-translate-y-1">
                <div class="absolute -right-6 -bottom-6 opacity-15 text-9xl transition duration-300 group-hover:scale-110">
                    <i class="fas fa-magic"></i>
                </div>
                <h3 class="text-lg font-bold mb-1">Fiction & Fantasy</h3>
                <p class="text-xs text-pink-100 mb-4 opacity-90">Magical universes and epic journeys.</p>
                <span class="text-xs font-semibold flex items-center gap-1 group-hover:translate-x-1 transition duration-300">Explore <i class="fas fa-arrow-right text-[10px]"></i></span>
            </a>

            <!-- Genre 2 -->
            <a href="<?php echo BASE_URL; ?>/books?q=Sci-Fi" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 p-6 text-white shadow-md hover:shadow-xl transition duration-300 hover:-translate-y-1">
                <div class="absolute -right-6 -bottom-6 opacity-15 text-9xl transition duration-300 group-hover:scale-110">
                    <i class="fas fa-user-astronaut"></i>
                </div>
                <h3 class="text-lg font-bold mb-1">Sci-Fi & Tech</h3>
                <p class="text-xs text-blue-100 mb-4 opacity-90">Explore distant planets and advanced logic.</p>
                <span class="text-xs font-semibold flex items-center gap-1 group-hover:translate-x-1 transition duration-300">Explore <i class="fas fa-arrow-right text-[10px]"></i></span>
            </a>

            <!-- Genre 3 -->
            <a href="<?php echo BASE_URL; ?>/books?q=Mystery" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 p-6 text-white shadow-md hover:shadow-xl transition duration-300 hover:-translate-y-1">
                <div class="absolute -right-6 -bottom-6 opacity-15 text-9xl transition duration-300 group-hover:scale-110">
                    <i class="fas fa-mask"></i>
                </div>
                <h3 class="text-lg font-bold mb-1">Mystery & Thriller</h3>
                <p class="text-xs text-purple-100 mb-4 opacity-90">Suspenseful tales that keep you guessing.</p>
                <span class="text-xs font-semibold flex items-center gap-1 group-hover:translate-x-1 transition duration-300">Explore <i class="fas fa-arrow-right text-[10px]"></i></span>
            </a>

            <!-- Genre 4 -->
            <a href="<?php echo BASE_URL; ?>/books?q=Biography" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-md hover:shadow-xl transition duration-300 hover:-translate-y-1">
                <div class="absolute -right-6 -bottom-6 opacity-15 text-9xl transition duration-300 group-hover:scale-110">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="text-lg font-bold mb-1">History & Biography</h3>
                <p class="text-xs text-emerald-100 mb-4 opacity-90">Real-life wisdom and rich memoirs.</p>
                <span class="text-xs font-semibold flex items-center gap-1 group-hover:translate-x-1 transition duration-300">Explore <i class="fas fa-arrow-right text-[10px]"></i></span>
            </a>
        </div>
    </section>

    <!-- ================================================================ -->
    <!-- ===================== LATEST BOOKS SECTION ====================== -->
    <!-- ================================================================ -->

    <?php if (isset($latestBooks) && is_array($latestBooks) && count($latestBooks) > 0): ?>
    <section class="py-14 bg-white dark:bg-gray-800/40 border-y border-gray-150 dark:border-gray-800">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                        📚 The Latest Arrivals
                    </h2>
                    <p class="text-slate-500 dark:text-gray-400 text-xs mt-0.5">Freshly stocked and ready for you to enjoy.</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/books" 
                   class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-bold flex items-center gap-1 bg-blue-50 dark:bg-blue-900/20 px-3 py-1.5 rounded-lg transition shadow-sm hover:shadow">
                    View All <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <!-- Compact grid: Shows 2 items side-by-side even on mobile, going up to 4 on large screens -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                <?php foreach (array_slice($latestBooks, 0, 4) as $book): ?>
                    <!-- Compact card layout with tighter padding and clean sizing -->
                    <div class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1 overflow-hidden border border-gray-150 dark:border-gray-700 flex flex-col justify-between">
                        <div>
                            <!-- Compact Book Cover Wrapper (Reduced height: h-40 on mobile, h-48 on desktop) -->
                            <div class="relative h-40 sm:h-48 overflow-hidden bg-slate-100 dark:bg-gray-700/50 flex items-center justify-center border-b border-gray-100 dark:border-gray-700/50">
                                <?php 
                                $cover = $book['cover_image'] ?? '';
                                $coverSrc = '';
                                if (!empty($cover)) {
                                    if (filter_var($cover, FILTER_VALIDATE_URL)) {
                                        $coverSrc = $cover;
                                    } else {
                                        $filename = basename($cover);
                                        $coverSrc = BASE_URL . '/uploads/books/' . $filename;
                                    }
                                }
                                ?>
                                <?php if (!empty($coverSrc)): ?>
                                    <img src="<?php echo htmlspecialchars($coverSrc); ?>" 
                                         alt="<?php echo htmlspecialchars($book['title']); ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <?php else: ?>
                                    <!-- Compact icon fallback -->
                                    <div class="text-slate-400 dark:text-gray-500 text-center p-4">
                                        <i class="fas fa-book-open text-3xl sm:text-4xl mb-1.5 animate-pulse"></i>
                                        <p class="text-[10px] font-semibold">No Cover</p>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Compact Top Badge -->
                                <?php if (!empty($book['is_new']) || !empty($book['featured'])): ?>
                                    <span class="absolute top-2 right-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm uppercase tracking-wider">
                                        NEW
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Compact Book Info -->
                            <div class="p-3.5">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white tracking-tight leading-snug truncate" title="<?php echo htmlspecialchars($book['title']); ?>">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </h3>
                                <p class="text-[11px] font-medium text-slate-500 dark:text-gray-400 truncate mt-1 flex items-center gap-1">
                                    <i class="fas fa-user-edit text-[9px] text-blue-500"></i>
                                    <?php echo htmlspecialchars($book['author'] ?? 'Unknown Author'); ?>
                                </p>
                                
                                <!-- Rating Integration -->
                                <?php if (isset($book['rating'])): ?>
                                    <div class="flex items-center gap-1 mt-2">
                                        <span class="text-yellow-400 text-xs">★</span>
                                        <span class="text-[10px] font-bold text-slate-700 dark:text-gray-300"><?php echo number_format($book['rating'], 1); ?></span>
                                        <span class="text-[9px] text-slate-400">(<?php echo $book['review_count'] ?? 0; ?>)</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Compact Action Button -->
                        <div class="px-3.5 pb-3.5">
                            <a href="<?php echo BASE_URL; ?>/books/<?php echo $book['id']; ?>" 
                               class="inline-block w-full text-center bg-slate-50 hover:bg-blue-600 dark:bg-gray-700/60 dark:hover:bg-blue-600 text-slate-600 hover:text-white dark:text-slate-200 dark:hover:text-white font-semibold py-1.5 rounded-lg transition duration-300 text-xs border border-gray-100 dark:border-transparent">
                                <i class="fas fa-eye mr-1 text-[10px]"></i> Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- ===================== 🌟 HOW IT WORKS SECTION ================== -->
    <!-- ================================================================ -->

    <section class="py-14 container mx-auto px-4">
        <div class="text-center max-w-xl mx-auto mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white mb-2">Borrow in 3 Easy Steps</h2>
            <p class="text-slate-500 dark:text-gray-400 text-sm">Our modern library platform makes borrowing books completely frictionless.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <!-- Connect Line on large screens -->
            <div class="hidden md:block absolute top-12 left-[15%] right-[15%] h-0.5 bg-slate-200 dark:bg-slate-700 z-0"></div>

            <!-- Step 1 -->
            <div class="relative z-10 text-center flex flex-col items-center">
                <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-500/20 mb-4 transition duration-300 hover:scale-105">
                    1
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Locate Your Book</h3>
                <p class="text-sm text-slate-500 dark:text-gray-400 max-w-xs leading-relaxed">
                    Use our ultra-fast search engine to discover specific titles, genres, or authors.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="relative z-10 text-center flex flex-col items-center">
                <div class="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-indigo-500/20 mb-4 transition duration-300 hover:scale-105">
                    2
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Reserve Instantly</h3>
                <p class="text-sm text-slate-500 dark:text-gray-400 max-w-xs leading-relaxed">
                    Check availability, view reservation dates, and click the reserve button on your dashboard.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="relative z-10 text-center flex flex-col items-center">
                <div class="w-16 h-16 bg-purple-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-purple-500/20 mb-4 transition duration-300 hover:scale-105">
                    3
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Pick Up & Enjoy</h3>
                <p class="text-sm text-slate-500 dark:text-gray-400 max-w-xs leading-relaxed">
                    Swing by the front desk or access modern digital copies depending on format. Happy reading!
                </p>
            </div>
        </div>
    </section>

    <!-- ================================================================ -->
    <!-- ===================== FEATURES SECTION ========================= -->
    <!-- ================================================================ -->

    <section class="bg-white dark:bg-gray-800/40 border-y border-gray-150 dark:border-gray-800 py-14">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl sm:text-3xl font-bold text-center text-slate-800 dark:text-white mb-8">Designed for Perfect Reading</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                <?php
                $features = [
                    ['icon' => 'fa-clock', 'title' => '24/7 Access', 'desc' => 'Manage requests anytime', 'color' => 'blue'],
                    ['icon' => 'fa-shield-alt', 'title' => 'Secure & Safe', 'desc' => 'Your safe dynamic catalog', 'color' => 'green'],
                    ['icon' => 'fa-sync', 'title' => 'Easy Returns', 'desc' => 'Extend with one tap', 'color' => 'purple'],
                    ['icon' => 'fa-headset', 'title' => 'Avid Support', 'desc' => 'Always here for you', 'color' => 'indigo']
                ];
                foreach ($features as $feature):
                    $color = $feature['color'];
                ?>
                <div class="bg-slate-50 dark:bg-gray-800/80 rounded-xl p-5 text-center border border-gray-150 dark:border-gray-700 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-<?php echo $color; ?>-50 dark:bg-<?php echo $color; ?>-900/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas <?php echo $feature['icon']; ?> text-xl text-<?php echo $color; ?>-500 dark:text-<?php echo $color; ?>-400"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white"><?php echo $feature['title']; ?></h3>
                    <p class="text-slate-400 dark:text-slate-500 text-[11px] mt-1"><?php echo $feature['desc']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ================================================================ -->
    <!-- ===================== 🌟 READER TESTIMONIALS ==================== -->
    <!-- ================================================================ -->

    <!-- <section class="py-14 container mx-auto px-4">
        <div class="text-center max-w-xl mx-auto mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white mb-2">What Our Readers Say</h2>
            <p class="text-slate-500 dark:text-gray-400 text-sm">Real reviews from our awesome community of book lovers.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm relative flex flex-col justify-between">
                <div>
                    <div class="text-yellow-400 text-sm mb-3">★★★★★</div>
                    <p class="text-slate-600 dark:text-gray-300 text-sm leading-relaxed italic">
                        "The interface is so clean! I love that I can browse for books at night in full dark mode without hurting my eyes. The reserve system is extremely fast."
                    </p>
                </div>
                <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center font-bold text-sm text-blue-600 dark:text-blue-400">SM</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white">Sarah Mitchell</h4>
                        <span class="text-[10px] text-slate-400">Premium Member</span>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm relative flex flex-col justify-between">
                <div>
                    <div class="text-yellow-400 text-sm mb-3">★★★★★</div>
                    <p class="text-slate-600 dark:text-gray-300 text-sm leading-relaxed italic">
                        "Finding history biographies used to take me forever. Now, I just log in, filter by history, and pick my book up the next afternoon. Super efficient!"
                    </p>
                </div>
                <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center font-bold text-sm text-indigo-600 dark:text-indigo-400">DK</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white">David K.</h4>
                        <span class="text-[10px] text-slate-400">History Major</span>
                    </div>
                </div>
            </div>

           
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm relative flex flex-col justify-between">
                <div>
                    <div class="text-yellow-400 text-sm mb-3">★★★★★</div>
                    <p class="text-slate-600 dark:text-gray-300 text-sm leading-relaxed italic">
                        "The platform is completely seamless. I received warnings about my return dates nicely and avoided any late fees. Strongly recommended!"
                    </p>
                </div>
                <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="w-9 h-9 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center font-bold text-sm text-purple-600 dark:text-purple-400">AL</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white">Amara Lawson</h4>
                        <span class="text-[10px] text-slate-400">Active Reader</span>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- ================================================================ -->
    <!-- ===================== 🌟 FAQ ACCORDIONS ========================= -->
    <!-- ================================================================ -->

    <section class="py-14 bg-white dark:bg-gray-800/40 border-t border-gray-150 dark:border-gray-800">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="text-center mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white mb-2">Got Questions? We Have Answers.</h2>
                <p class="text-slate-500 dark:text-gray-400 text-sm">Everything you need to know about navigating your dynamic library.</p>
            </div>

            <div class="space-y-4">
                <!-- FAQ 1 -->
                <details class="group bg-slate-50 dark:bg-gray-800 p-5 rounded-xl border border-gray-150 dark:border-gray-700/80 [&_summary::-webkit-details-marker]:hidden transition duration-300">
                    <summary class="flex justify-between items-center cursor-pointer focus:outline-none">
                        <h3 class="text-sm sm:text-base font-bold text-slate-800 dark:text-white">
                            How do I register or join the library?
                        </h3>
                        <span class="text-xs transition group-open:rotate-180">
                            <i class="fas fa-chevron-down text-slate-500"></i>
                        </span>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-3 leading-relaxed">
                        Joining is completely free. Simply click the "Join Now" or "Create Free Account" button, fill in your details, and verify your email. Once complete, you can log in and immediately start reserving books.
                    </p>
                </details>

                <!-- FAQ 2 -->
                <details class="group bg-slate-50 dark:bg-gray-800 p-5 rounded-xl border border-gray-150 dark:border-gray-700/80 [&_summary::-webkit-details-marker]:hidden transition duration-300">
                    <summary class="flex justify-between items-center cursor-pointer focus:outline-none">
                        <h3 class="text-sm sm:text-base font-bold text-slate-800 dark:text-white">
                            How long can I borrow a physical volume for?
                        </h3>
                        <span class="text-xs transition group-open:rotate-180">
                            <i class="fas fa-chevron-down text-slate-500"></i>
                        </span>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-3 leading-relaxed">
                        The standard borrow window is exactly 14 days. If you require more time, you can extend your return window via your personal "User Dashboard" off-heat before the due date, provided there is no queue for the book.
                    </p>
                </details>

                <!-- FAQ 3 -->
                <details class="group bg-slate-50 dark:bg-gray-800 p-5 rounded-xl border border-gray-150 dark:border-gray-700/80 [&_summary::-webkit-details-marker]:hidden transition duration-300">
                    <summary class="flex justify-between items-center cursor-pointer focus:outline-none">
                        <h3 class="text-sm sm:text-base font-bold text-slate-800 dark:text-white">
                            What happens if I return a book late?
                        </h3>
                        <span class="text-xs transition group-open:rotate-180">
                            <i class="fas fa-chevron-down text-slate-500"></i>
                        </span>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-3 leading-relaxed">
                        We offer a gracious 3-day buffer window. Following that, a minor late fine may apply depending on individual library policies. You will receive dynamic dashboard alerts as the deadline approaches.
                    </p>
                </details>
            </div>
        </div>
    </section>

    <!-- ================================================================ -->
    <!-- ===================== CTA SECTION ============================== -->
    <!-- ================================================================ -->

    <?php if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])): ?>
    <section class="relative py-14 overflow-hidden border-t border-gray-150 dark:border-gray-800">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 dark:from-slate-900 dark:via-slate-950 dark:to-slate-900"></div>
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 container mx-auto px-4 text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-2">
                Ready to Begin Your Next Adventure?
            </h2>
            <p class="text-blue-100 dark:text-slate-300 text-sm mb-6 max-w-lg mx-auto leading-relaxed">
                Unlock instant access to thousands of titles. Join our reading circle and revolutionize how you manage your bookshelf.
            </p>
            <!-- ✅ FIXED: Direct link to registration page -->
            <a href="<?php echo BASE_URL; ?>/register"
               class="px-8 py-3 bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 font-bold rounded-xl shadow-lg hover:shadow-xl hover:scale-105 transition duration-300 inline-flex items-center gap-2 text-sm sm:text-base">
                <i class="fas fa-user-plus"></i> Create Your Free Account
            </a>
        </div>
    </section>
    <?php endif; ?>

</div> <!-- End of main content wrapper -->

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