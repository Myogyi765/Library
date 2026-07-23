<?php
$pageTitle = 'Verify Phone Number';
include BASE_PATH . '/view/layout/header.php';

$phone = $phone ?? $_SESSION['verification_phone'] ?? '';
$debugCode = $debugCode ?? $_SESSION['verification_code'] ?? null;

// ✅ Environment detection (optional - we will just check debugCode)
$isDevelopment = false;
$env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'production');
if ($env === 'development' || $env === 'testing' || $env === 'dev') {
    $isDevelopment = true;
}
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">

        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/30 mb-4">
                <i class="fas fa-phone text-3xl text-blue-600 dark:text-blue-400"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Verify Your Phone Number</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                We sent a verification code to
                <strong class="text-blue-600 dark:text-blue-400"><?= htmlspecialchars($phone) ?></strong>
            </p>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mt-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mt-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Code Input Form -->
        <form method="POST" action="<?= BASE_URL ?>/verify-phone" class="mt-6 space-y-4">
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Verification Code (6 digits)
                </label>
                <input type="text"
                       id="code"
                       name="code"
                       maxlength="6"
                       pattern="\d{6}"
                       required
                       placeholder="Enter 6-digit code"
                       class="mt-1 block w-full px-4 py-3 text-center text-2xl font-bold tracking-widest border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                <i class="fas fa-check-circle mr-2"></i> Verify Phone
            </button>
        </form>

        <!-- Resend Code -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Didn't receive the code?
                <a href="<?= BASE_URL ?>/resend-phone-code"
                   class="text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium" id="resendLink">
                    Resend Code
                </a>
                <span id="timer" class="text-xs text-gray-500 ml-1"></span>
            </p>
        </div>

        <!-- Debug Mode – show code if available (regardless of environment) -->
        <?php if ($debugCode): ?>
            <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-400 dark:border-yellow-700 rounded-lg">
                <div class="flex items-center gap-2 text-yellow-800 dark:text-yellow-300">
                    <i class="fas fa-bug"></i>
                    <span class="font-semibold">Debug Mode</span>
                </div>
                <p class="text-sm text-yellow-700 dark:text-yellow-200 mt-1">
                    Your verification code is:
                    <span class="font-mono font-bold text-lg tracking-widest"><?= htmlspecialchars($debugCode) ?></span>
                </p>
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                    This will be sent via SMS in production.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Timer for resend button (60 seconds cooldown)
    (function() {
        const timerEl = document.getElementById('timer');
        const resendLink = document.getElementById('resendLink');
        let seconds = 60;

        function startTimer() {
            if (seconds > 0) {
                timerEl.textContent = `(${seconds}s)`;
                resendLink.style.pointerEvents = 'none';
                resendLink.style.opacity = '0.5';
                seconds--;
                setTimeout(startTimer, 1000);
            } else {
                timerEl.textContent = '';
                resendLink.style.pointerEvents = 'auto';
                resendLink.style.opacity = '1';
                seconds = 60;
            }
        }

        // Start timer on page load if code was just sent
        <?php if (isset($_SESSION['success_message']) && strpos($_SESSION['success_message'], 'sent') !== false): ?>
            startTimer();
        <?php endif; ?>

        // When resend link is clicked, start timer
        resendLink.addEventListener('click', function(e) {
            if (timerEl.textContent === '') {
                startTimer();
            }
        });
    })();
</script>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>