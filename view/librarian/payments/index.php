<?php
// view/librarian/payments/index.php
// This file is included by dashboard-content.php when $page === 'payments'
// Variables available: $payments, $currentFilter, BASE_URL
?>
<!-- ============================================================ -->
<!-- 🔹 PAYMENTS PAGE – Full details with filters & invoice       -->
<!-- ============================================================ -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-credit-card text-blue-600 dark:text-blue-400 mr-2"></i>Payment Records
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage all payments</p>
    </div>
    <a href="<?= BASE_URL ?>/librarian/dashboard?page=refunds" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition shadow-md hover:shadow-lg">
        <i class="fas fa-undo-alt"></i> Manage Refunds
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

<!-- Filter Tabs -->
<div class="flex flex-wrap gap-2 mb-6">
    <a href="?page=payments&status=all" class="px-4 py-2 rounded-lg transition <?= $currentFilter === 'all' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        All
    </a>
    <a href="?page=payments&status=pending" class="px-4 py-2 rounded-lg transition <?= $currentFilter === 'pending' ? 'bg-yellow-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        Pending
    </a>
    <a href="?page=payments&status=approved" class="px-4 py-2 rounded-lg transition <?= $currentFilter === 'approved' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        Approved
    </a>
    <a href="?page=payments&status=rejected" class="px-4 py-2 rounded-lg transition <?= $currentFilter === 'rejected' ? 'bg-red-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        Rejected
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
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Transaction Ref</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Book</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Submitted At</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Status</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Refund</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-gray-800 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments) && is_array($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                        <?php
                            $id = $payment['id'] ?? null;
                            if (!$id) continue;

                            $userName = $payment['user_name'] ?? 'User #' . ($payment['user_id'] ?? '');
                            $userEmail = $payment['user_email'] ?? '';
                            $amount = $payment['amount'] ?? 0;
                            $paymentMethod = $payment['payment_method'] ?? '';
                            $transactionRef = $payment['transaction_reference'] ?? '';
                            $bookTitle = $payment['book_title'] ?? 'N/A';
                            $submittedAt = $payment['submitted_at'] ?? null;

                            $status = trim($payment['status'] ?? 'pending_approval');
                            $refundStatus = !empty($payment['refund_status']) ? trim($payment['refund_status']) : 'none';

                            $statusLabel = ucfirst(str_replace('_', ' ', $status));
                            $statusColor = match($status) {
                                'pending_approval' => 'text-yellow-600 dark:text-yellow-400',
                                'approved' => 'text-green-600 dark:text-green-400',
                                'completed' => 'text-green-600 dark:text-green-400',
                                'rejected' => 'text-red-600 dark:text-red-400',
                                default => 'text-gray-600 dark:text-gray-400'
                            };

                            $refundLabel = ucfirst(str_replace('_', ' ', $refundStatus));
                            $refundColor = match($refundStatus) {
                                'completed' => 'text-green-600 dark:text-green-400',
                                'pending' => 'text-yellow-600 dark:text-yellow-400',
                                'none' => 'text-gray-400 dark:text-gray-500',
                                default => 'text-gray-600 dark:text-gray-400'
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
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">#<?= $payment['loan_id'] ?? '?' ?></td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300 font-semibold whitespace-nowrap"><?= number_format($amount, 2) ?> MMK</td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 whitespace-nowrap">
                                    <?= htmlspecialchars(ucfirst($paymentMethod)) ?>
                                </span>
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300 font-mono text-xs break-all max-w-[150px]">
                                <?= htmlspecialchars($transactionRef) ?>
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300 break-words max-w-[150px]">
                                <?= htmlspecialchars($bookTitle) ?>
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300 text-sm whitespace-nowrap">
                                <?= $submittedAt ? date('d M Y, H:i', strtotime($submittedAt)) : '—' ?>
                            </td>
                            <td class="px-3 py-3">
                                <span class="<?= $statusColor ?> font-semibold whitespace-nowrap"><?= $statusLabel ?></span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="<?= $refundColor ?> font-medium whitespace-nowrap"><?= $refundLabel ?></span>
                            </td>
                            <td class="px-3 py-3 text-center space-x-1 whitespace-nowrap">
                                <?php if ($status === 'pending_approval'): ?>
                                    <form action="<?= BASE_URL ?>/librarian/payments/<?= $id ?>/approve" method="POST" class="inline">
                                        <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition" title="Approve" onclick="return confirm('Approve this payment?')">
                                            <i class="fas fa-check-circle text-base"></i>
                                        </button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/librarian/payments/<?= $id ?>/reject" method="POST" class="inline">
                                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition" title="Reject" onclick="return confirm('Reject this payment?')">
                                            <i class="fas fa-times-circle text-base"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (($status === 'approved' || $status === 'completed') && $refundStatus === 'none'): ?>
                                    <a href="<?= BASE_URL ?>/librarian/payments/<?= $id ?>/refund" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 transition" title="Refund">
                                        <i class="fas fa-undo-alt text-base"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if ($status === 'approved' || $status === 'completed'): ?>
                                    <a href="<?= BASE_URL ?>/librarian/payments/invoice/<?= $id ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition" title="Invoice">
                                        <i class="fas fa-file-invoice text-base"></i>
                                    </a>
                                <?php endif; ?>

                                <a href="<?= BASE_URL ?>/librarian/payments/<?= $id ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition" title="Details">
                                    <i class="fas fa-eye text-base"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <?php
                                $emptyMessage = match($currentFilter) {
                                    'pending' => 'No pending payments available for approval.',
                                    'approved' => 'No approved payments found.',
                                    'rejected' => 'No rejected payments found.',
                                    default => 'No payments found.',
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