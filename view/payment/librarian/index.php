<?php
// view/payment/librarian/index.php
$pageTitle = 'Payment Approval - Librarian';
include BASE_PATH . '/view/layout/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-4">Payment Approval</h2>

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
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Amount</th>
                    <th class="px-4 py-3 text-left">Payment Method</th>
                    <th class="px-4 py-3 text-left">Transaction Reference</th>
                    <th class="px-4 py-3 text-left">Submitted At</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Refund Status</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): 
                        // Optional: fetch user name if you have $users array in scope
                        $user = $users[$payment->getUserId()] ?? null;
                        $amountValue = $payment->getAmount()->getAmount();
                        $status = $payment->getStatus()->getValue();
                        $refundStatus = $payment->getRefundStatus() ?? 'none';
                    ?>
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-3"><?= $payment->getId() ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user ? $user->getName() : 'User #' . $payment->getUserId()) ?></td>
                            <td class="px-4 py-3"><?= number_format($amountValue, 2) ?> MMK</td>
                            <td class="px-4 py-3"><?= ucfirst($payment->getPaymentMethod()) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($payment->getTransactionReference()) ?></td>
                            <td class="px-4 py-3"><?= $payment->getSubmittedAt() ? $payment->getSubmittedAt()->format('Y-m-d H:i') : '—' ?></td>
                            <td class="px-4 py-3">
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
                            </td>
                            <td class="px-4 py-3">
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
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($status === 'pending_approval'): ?>
                                    <form action="<?= BASE_URL ?>/librarian/payments/<?= $payment->getId() ?>/approve" method="POST" class="inline">
                                        <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 mr-2" title="Approve">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/librarian/payments/<?= $payment->getId() ?>/reject" method="POST" class="inline">
                                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Reject">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </form>
                                <?php elseif ($status === 'completed' && $refundStatus === 'none'): ?>
                                    <a href="<?= BASE_URL ?>/librarian/payments/<?= $payment->getId() ?>/refund" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Refund">
                                        <i class="fas fa-undo-alt"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            No pending payments available for approval.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>