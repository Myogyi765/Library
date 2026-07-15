<?php
$pageTitle = 'Process Refund - Librarian';
include BASE_PATH . '/view/layout/header.php';

$payment = $payment ?? null;
if (!$payment) {
    echo '<div class="container mx-auto px-4 py-8">Payment not found.</div>';
    include BASE_PATH . '/view/layout/footer.php';
    return;
}

$user = $users[$payment->getUserId()] ?? null;
$amount = $payment->getAmount()->getAmount();
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-undo-alt text-blue-600 mr-2"></i>Process Refund
            </h2>
            <a href="<?= BASE_URL ?>/librarian/dashboard?page=payments" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <!-- Payment Info -->
        <div class="bg-gray-50 dark:bg-gray-900/30 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Payment ID</span>
                    <p class="font-semibold text-gray-900 dark:text-white">#<?= $payment->getId() ?></p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">User</span>
                    <p class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($user ? $user->getName() : 'Unknown') ?></p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Amount</span>
                    <p class="font-semibold text-gray-900 dark:text-white"><?= number_format($amount, 2) ?> MMK</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Payment Method</span>
                    <p class="font-semibold text-gray-900 dark:text-white"><?= ucfirst($payment->getPaymentMethod()) ?></p>
                </div>
            </div>
        </div>

        <!-- Refund Form -->
        <form action="<?= BASE_URL ?>/librarian/payments/<?= $payment->getId() ?>/refund" method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Refund Reason <span class="text-red-500">*</span>
                </label>
                <textarea 
                    name="refund_reason" 
                    required
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Explain why this payment is being refunded..."
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    This reason will be recorded for audit purposes.
                </p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-check"></i> Process Refund
                </button>
                <a href="<?= BASE_URL ?>/librarian/dashboard?page=payments" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>