<?php
// view/librarian/payments/index.php
// Variables available: $payments, $currentFilter, BASE_URL
?>
<!-- ============================================================ -->
<!-- 🔹 PAYMENTS PAGE – Full details with filters                 -->
<!-- ============================================================ -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-credit-card text-blue-600 dark:text-blue-400 mr-2"></i>Payment Records
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage all payments</p>
    </div>
</div>

<!-- Flash Messages -->
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
    <a href="?page=payments&status=all" class="px-4 py-2 rounded-lg text-sm font-medium transition <?= $currentFilter === 'all' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        All
    </a>
    <a href="?page=payments&status=pending" class="px-4 py-2 rounded-lg text-sm font-medium transition <?= $currentFilter === 'pending' ? 'bg-yellow-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        Pending
    </a>
    <a href="?page=payments&status=approved" class="px-4 py-2 rounded-lg text-sm font-medium transition <?= $currentFilter === 'approved' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        Approved
    </a>
    <a href="?page=payments&status=rejected" class="px-4 py-2 rounded-lg text-sm font-medium transition <?= $currentFilter === 'rejected' ? 'bg-red-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
        Rejected
    </a>
</div>

<!-- ===== TABLE – Full width, no right-side gap ===== -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-fixed">
            <colgroup>
                <col style="width: 5%;">    <!-- ID -->
                <col style="width: 16%;">   <!-- User -->
                <col style="width: 6%;">     <!-- Loan -->
                <col style="width: 10%;">    <!-- Amount -->
                <col style="width: 9%;">     <!-- Method -->
                <col style="width: 10%;">    <!-- Ref -->
                <col style="width: 12%;">    <!-- Book -->
                <col style="width: 10%;">    <!-- Submitted -->
                <col style="width: 8%;">     <!-- Status -->
                <col style="width: 8%;">     <!-- Refund -->
                <col style="width: 2%;">     <!-- Actions -->
            </colgroup>
            <!-- Header -->
            <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-2 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400 truncate">ID</th>
                    <th class="px-2 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400 truncate">User</th>
                    <th class="px-2 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400 truncate hidden md:table-cell">Loan</th>
                    <th class="px-2 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400 truncate">Amount</th>
                    <th class="px-2 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400 truncate hidden sm:table-cell">Method</th>
                    <th class="px-2 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400 truncate hidden lg:table-cell">Ref</th>
                    <th class="px-2 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400 truncate hidden xl:table-cell">Book</th>
                    <th class="px-2 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400 truncate hidden md:table-cell">Submitted</th>
                    <th class="px-2 py-3 text-center text-xs font-semibold uppercase text-gray-800 dark:text-gray-400 truncate">Status</th>
                    <th class="px-2 py-3 text-left text-xs font-bold uppercase text-gray-800 dark:text-gray-400 truncate hidden sm:table-cell">Refund</th>
                    <th class="px-2 py-3 text-left text-xs font-semibold uppercase text-gray-800 dark:text-gray-400 ">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
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
                                'pending_approval' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                'approved', 'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                            };

                            $refundLabel = ucfirst(str_replace('_', ' ', $refundStatus));
                            $refundColor = match($refundStatus) {
                                'completed' => 'text-green-600 dark:text-green-400',
                                'pending' => 'text-yellow-600 dark:text-yellow-400',
                                'none' => 'text-gray-400 dark:text-gray-500',
                                default => 'text-gray-600 dark:text-gray-400'
                            };
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition duration-150">
                            <td class="px-2 py-3 font-medium text-gray-900 dark:text-white truncate"><?= $id ?></td>
                            <td class="px-2 py-3 text-gray-700 dark:text-gray-300 overflow-hidden">
                                <div class="font-medium text-gray-900 dark:text-white truncate" title="<?= htmlspecialchars($userName) ?>"><?= htmlspecialchars($userName) ?></div>
                                <?php if ($userEmail): ?>
                                    <div class="text-xs text-gray-500 truncate" title="<?= htmlspecialchars($userEmail) ?>"><?= htmlspecialchars($userEmail) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-2 py-3 text-gray-700 dark:text-gray-300 truncate hidden md:table-cell"><?= $payment['loan_id'] ?? '?' ?></td>
                            <td class="px-2 py-3 text-gray-900 dark:text-white font-semibold truncate"><?= number_format($amount, 2) ?> MMK</td>
                            <td class="px-2 py-3 hidden sm:table-cell">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 truncate">
                                    <?= htmlspecialchars(ucfirst($paymentMethod)) ?>
                                </span>
                            </td>
                            <td class="px-2 py-3 text-gray-800 dark:text-gray-200 font-mono text-xs truncate hidden lg:table-cell" title="<?= htmlspecialchars($transactionRef) ?>">
                                <?= htmlspecialchars($transactionRef) ?>
                            </td>
                            <td class="px-2 py-3 text-gray-800 dark:text-gray-200 truncate hidden xl:table-cell" title="<?= htmlspecialchars($bookTitle) ?>">
                                <?= htmlspecialchars($bookTitle) ?>
                            </td>
                            <td class="px-2 py-3 text-gray-900 dark:text-gray-200 text-xs truncate hidden md:table-cell">
                                <?= $submittedAt ? date('d M Y', strtotime($submittedAt)) : '—' ?>
                            </td>
                            <td class="px-2 py-3 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium <?= $statusColor ?> truncate">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="px-2 py-3 hidden sm:table-cell">
                                <span class="text-sm font-bold <?= $refundColor ?> truncate"><?= $refundLabel ?></span>
                            </td>
                            <td class="px-2 py-3 text-center">
                                <div class="flex items-center justify-center gap-1 whitespace-nowrap">
                                    <?php if ($status === 'pending_approval'): ?>
                                        <form action="<?= BASE_URL ?>/librarian/payments/<?= $id ?>/approve" method="POST" class="inline">
                                            <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition p-1" title="Approve" onclick="return confirm('Approve this payment?')">
                                                <i class="fas fa-check-circle text-base"></i>
                                            </button>
                                        </form>
                                        <form action="<?= BASE_URL ?>/librarian/payments/<?= $id ?>/reject" method="POST" class="inline">
                                            <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition p-1" title="Reject" onclick="return confirm('Reject this payment?')">
                                                <i class="fas fa-times-circle text-base"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (($status === 'approved' || $status === 'completed') && $refundStatus === 'none'): ?>
                                        <a href="<?= BASE_URL ?>/librarian/payments/<?= $id ?>/refund" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 transition p-1" title="Refund">
                                            <i class="fas fa-undo-alt text-base"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($status === 'approved' || $status === 'completed'): ?>
                                        <a href="<?= BASE_URL ?>/librarian/payments/invoice/<?= $id ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition p-1" title="Invoice">
                                            <i class="fas fa-file-invoice text-base"></i>
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?= BASE_URL ?>/librarian/payments/<?= $id ?>" class="text-gray-500 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition p-1" title="Details">
                                        <i class="fas fa-eye text-base"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                            <?php
                                $emptyMessage = match($currentFilter) {
                                    'pending' => 'No pending payments available for approval.',
                                    'approved' => 'No approved payments found.',
                                    'rejected' => 'No rejected payments found.',
                                    default => 'No payments found.',
                                };
                            ?>
                            <i class="fas fa-credit-card text-3xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                            <?= $emptyMessage ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>