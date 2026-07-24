<?php
// view/payment/librarian/show.php
$pageTitle = 'Payment Details - Librarian';
include BASE_PATH . '/view/layout/header.php';

$payment = $payment ?? null;
if (!$payment) {
    echo '<div class="container mx-auto px-4 py-8"><div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 p-6 rounded-xl text-center">Payment not found.</div></div>';
    include BASE_PATH . '/view/layout/footer.php';
    return;
}

// ✅ Use the user data passed from the controller
$user = $user ?? null;
$userName = $userName ?? null;

// Determine the display name with proper fallback
if ($user) {
    $displayName = $user->getName();
} elseif ($userName) {
    $displayName = $userName;
} else {
    $displayName = 'User #' . $payment->getUserId();
}

$amountValue = $payment->getAmount()->getAmount();
$status = $payment->getStatus()->getValue();
$refundStatus = $payment->getRefundStatus() ?? 'none';
$refundReason = $payment->getRefundReason() ?? null;

// Handle screenshot path
$screenshotPath = $payment->getScreenshotPath();
$screenshotUrl = $screenshotPath ? BASE_URL . '/' . ltrim($screenshotPath, '/') : null;
?>

<div class="container max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
        <h3 class="text-xl font-bold mb-4">Payment Details</h3>

        <dl class="grid grid-cols-2 gap-4">
            <dt class="text-gray-500">User</dt>
            <dd><?= htmlspecialchars($displayName) ?></dd>

            <dt class="text-gray-500">Amount</dt>
            <dd><?= number_format($amountValue, 2) ?> MMK</dd>

            <dt class="text-gray-500">Payment Method</dt>
            <dd>
                <?php if ($payment->getPaymentMethod() === 'kpay'): ?>
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded">KPay</span>
                <?php else: ?>
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">WavePay</span>
                <?php endif; ?>
            </dd>

            <dt class="text-gray-500">Transaction Reference</dt>
            <dd><?= htmlspecialchars($payment->getTransactionReference()) ?></dd>

            <dt class="text-gray-500">Payment Screenshot</dt>
            <dd>
                <?php if ($screenshotUrl): ?>
                    <a href="<?= $screenshotUrl ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-image"></i> View Screenshot
                    </a>
                <?php else: ?>
                    <span class="text-gray-400">No Screenshot Available</span>
                <?php endif; ?>
            </dd>

            <dt class="text-gray-500">Submitted At</dt>
            <dd><?= $payment->getSubmittedAt() ? $payment->getSubmittedAt()->format('Y-m-d H:i') : '—' ?></dd>

            <dt class="text-gray-500">Payment Status</dt>
            <dd>
                <?php
                    $statusLabel = ucfirst(str_replace('_', ' ', $status));
                    $statusColor = match($status) {
                        'pending_approval' => 'text-yellow-600',
                        'completed' => 'text-green-600',
                        'rejected' => 'text-red-600',
                        default => 'text-gray-600'
                    };
                ?>
                <span class="<?= $statusColor ?>"><?= $statusLabel ?></span>
            </dd>

            <dt class="text-gray-500">Refund Status</dt>
            <dd>
                <?php
                    $refundLabel = ucfirst(str_replace('_', ' ', $refundStatus));
                    $refundColor = match($refundStatus) {
                        'completed' => 'text-green-600',
                        'pending' => 'text-yellow-600',
                        'none' => 'text-gray-400',
                        default => 'text-gray-600'
                    };
                ?>
                <span class="<?= $refundColor ?>"><?= $refundLabel ?></span>
            </dd>

            <?php if ($refundStatus === 'completed' && $refundReason): ?>
                <dt class="text-gray-500">Refund Reason</dt>
                <dd><?= htmlspecialchars($refundReason) ?></dd>
            <?php endif; ?>
        </dl>

        <!-- Action Buttons -->
        <?php if ($status === 'pending_approval'): ?>
            <div class="mt-6 flex gap-3 flex-wrap">
                <form action="<?= BASE_URL ?>/librarian/payments/<?= $payment->getId() ?>/approve" method="POST" class="inline">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-check"></i> Approve Payment
                    </button>
                </form>

                <form action="<?= BASE_URL ?>/librarian/payments/<?= $payment->getId() ?>/reject" method="POST" class="inline">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-times"></i> Reject Payment
                    </button>
                </form>
            </div>
        <?php elseif ($status === 'completed' && $refundStatus === 'none'): ?>
            <div class="mt-6 flex gap-3 flex-wrap">
                <a href="<?= BASE_URL ?>/librarian/payments/<?= $payment->getId() ?>/refund" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-undo-alt"></i> Refund Payment
                </a>
            </div>
        <?php elseif ($status === 'completed' && $refundStatus === 'completed'): ?>
            <div class="mt-6 flex gap-3 flex-wrap">
                <span class="bg-green-100 text-green-800 px-4 py-2 rounded-lg">
                    <i class="fas fa-check-circle"></i> Refunded Successfully
                </span>
            </div>
        <?php endif; ?>

        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= BASE_URL ?>/librarian/payments" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-arrow-left mr-1"></i> Back to Payments
            </a>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>