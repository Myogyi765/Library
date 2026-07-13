<?php
$pageTitle = 'My Payments';
include BASE_PATH . '/view/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-4">My Payment History</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Amount</th>
                    <th class="px-4 py-3 text-left">Method</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Refund Status</th>  <!-- ✅ ဒီကော်လံထည့် -->
                    <th class="px-4 py-3 text-left">Submitted At</th>
                    <th class="px-4 py-3 text-center">Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-3"><?= $payment->getId() ?></td>
                            <td class="px-4 py-3"><?= number_format($payment->getAmount()->getAmount(), 2) ?> MMK</td>
                            <td class="px-4 py-3"><?= ucfirst($payment->getPaymentMethod()) ?></td>
                            <td class="px-4 py-3">
                                <?php
                                    $status = $payment->getStatus()->getValue();
                                    $color = match($status) {
                                        'pending_approval' => 'text-yellow-600',
                                        'completed' => 'text-green-600',
                                        'rejected' => 'text-red-600',
                                        default => 'text-gray-600'
                                    };
                                ?>
                                <span class="<?= $color ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <?php
                                    $refundStatus = $payment->getRefundStatus() ?? 'none';
                                    $refundColor = match($refundStatus) {
                                        'completed' => 'text-green-600',
                                        'pending' => 'text-yellow-600',
                                        'none' => 'text-gray-400',
                                        default => 'text-gray-600'
                                    };
                                ?>
                                <span class="<?= $refundColor ?>"><?= ucfirst(str_replace('_', ' ', $refundStatus)) ?></span>
                            </td>
                            <td class="px-4 py-3"><?= $payment->getSubmittedAt() ? $payment->getSubmittedAt()->format('Y-m-d H:i') : '—' ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($payment->getStatus()->getValue() === 'completed'): ?>
                                    <a href="<?= BASE_URL ?>/invoice/<?= $payment->getId() ?>" class="text-blue-600 hover:text-blue-800">View</a>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">You have no payments yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>