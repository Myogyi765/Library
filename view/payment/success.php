<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = 'Payment Success';
include BASE_PATH . '/view/layout/header.php';
?>

<!-- ✅ Full-height background that auto‑switches with dark mode -->
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-10 px-4">
    <div class="container max-w-md mx-auto text-center">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
            <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-4xl text-green-600 dark:text-green-400"></i>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Submission Successful
            </h2>

            <p class="text-gray-500 dark:text-gray-400 mb-6">
                Your payment proof has been received successfully. It will be reviewed by an administrator shortly. Please wait for the verification process to be completed.
            </p>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 p-4 rounded-lg mb-4">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/user-dashboard"
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> View Loan History
            </a>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>