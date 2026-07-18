<?php
// view/librarian/refunds/index.php
// This file is included by dashboard-content.php when $page === 'refunds'
// Variables available: $refunds, $currentFilter, BASE_URL
?>
<!-- ============================================================ -->
<!-- 🔹 REFUNDS PAGE – Manage refund requests                    -->
<!-- ============================================================ -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-undo-alt text-blue-600 dark:text-blue-400 mr-2"></i>Refund Management
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage all refund requests</p>
    </div>
    <a href="<?= BASE_URL ?>/librarian/dashboard?page=payments" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
        <i class="fas fa-arrow-left mr-1"></i> Back to Payments
    </a>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
        <div><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
        <button onclick="this.parentElement.style.display='none'" class="text-green-700 dark:text-green-300 hover:text-green-900"><i class="fas fa-times"></i></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
        <div><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <button onclick="this.parentElement.style.display='none'" class="text-red-700 dark:text-red-300 hover:text-red-900"><i class="fas fa-times"></i></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Filter Tabs for Refunds -->
<div class="flex flex-wrap gap-2 mb-6">
    <a href="?page=refunds&status=all" class="px-4 py-2 rounded-lg transition <?= ($currentFilter ?? 'all') === 'all' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        All
    </a>
    <a href="?page=refunds&status=pending" class="px-4 py-2 rounded-lg transition <?= ($currentFilter ?? 'all') === 'pending' ? 'bg-yellow-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        Pending
    </a>
    <a href="?page=refunds&status=completed" class="px-4 py-2 rounded-lg transition <?= ($currentFilter ?? 'all') === 'completed' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        Completed
    </a>
</div>

<!-- ===== SIMPLE TABLE – No fixed widths, all data visible ===== -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <!-- ===== SIMPLE HEADER ===== -->
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">#</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">User</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Loan</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Amount</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Method</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Refund Status</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Refund Reason</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Refunded At</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($refunds) && is_array($refunds)): ?>
                    <?php foreach ($refunds as $refund): ?>
                        <?php
                            $id = $refund['id'] ?? null;
                            if (!$id) continue;

                            $userName = $refund['user_name'] ?? 'User #' . ($refund['user_id'] ?? '');
                            $userEmail = $refund['user_email'] ?? '';
                            $amount = $refund['amount'] ?? 0;
                            $paymentMethod = $refund['payment_method'] ?? '';
                            $refundStatus = trim($refund['refund_status'] ?? 'none');
                            $refundReason = $refund['refund_reason'] ?? '—';
                            $refundedAt = $refund['refunded_at'] ?? null;

                            $refundLabel = ucfirst(str_replace('_', ' ', $refundStatus));
                            $refundColor = match($refundStatus) {
                                'completed' => 'text-green-600 dark:text-green-400',
                                'pending' => 'text-yellow-600 dark:text-yellow-400',
                                default => 'text-gray-400 dark:text-gray-500'
                            };
                        ?>
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-3 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">#<?= $id ?></td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300">
                                <span class="font-medium"><?= htmlspecialchars($userName) ?></span>
                                <?php if ($userEmail): ?>
                                    <br><span class="text-xs text-gray-400"><?= htmlspecialchars($userEmail) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">#<?= $refund['loan_id'] ?? '?' ?></td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300 font-semibold whitespace-nowrap"><?= number_format($amount, 2) ?> MMK</td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 whitespace-nowrap">
                                    <?= htmlspecialchars(ucfirst($paymentMethod)) ?>
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="<?= $refundColor ?> font-medium whitespace-nowrap"><?= $refundLabel ?></span>
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300 break-words max-w-[200px]">
                                <?= htmlspecialchars($refundReason) ?>
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300 text-sm whitespace-nowrap">
                                <?= $refundedAt ? date('d M Y, H:i', strtotime($refundedAt)) : '—' ?>
                            </td>
                            <td class="px-3 py-3 text-center space-x-1 whitespace-nowrap">
                                <?php if ($refundStatus === 'pending'): ?>
                                    <form action="<?= BASE_URL ?>/librarian/refunds/<?= $id ?>/approve" method="POST" class="inline">
                                        <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition" title="Approve Refund" onclick="return confirm('Approve this refund?')">
                                            <i class="fas fa-check-circle text-base"></i>
                                        </button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/librarian/refunds/<?= $id ?>/reject" method="POST" class="inline">
                                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition" title="Reject Refund" onclick="return confirm('Reject this refund?')">
                                            <i class="fas fa-times-circle text-base"></i>
                                        </button>
                                    </form>
                                <?php elseif ($refundStatus === 'completed'): ?>
                                    <span class="text-xs text-green-600 dark:text-green-400">Completed</span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">—</span>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/librarian/payments/<?= $id ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition" title="View Payment">
                                    <i class="fas fa-eye text-base"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <?php
                                $emptyMessage = match($currentFilter ?? 'all') {
                                    'pending' => 'No pending refund requests.',
                                    'completed' => 'No completed refunds.',
                                    default => 'No refund records found.',
                                };
                            ?>
                            <?= $emptyMessage ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>