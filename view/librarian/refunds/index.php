<?php
// view/librarian/refunds/index.php
// This file is included by dashboard-content.php when $page === 'refunds'
// Variables available: $refunds, $currentFilter, BASE_URL
?>
<style>
    /* ─── Table styles – matches Payment Records table ─── */
    .refund-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .refund-table thead th {
        background: #f8fafc;
        color: #1e293b;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
        vertical-align: middle;
    }
    .dark .refund-table thead th {
        background: #1e293b;
        color: #e2e8f0;
        border-bottom-color: #334155;
    }
    .refund-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }
    .dark .refund-table tbody tr {
        border-bottom-color: #1e293b;
    }
    .refund-table tbody tr:hover {
        background: #f1f5f9;
    }
    .dark .refund-table tbody tr:hover {
        background: #1e293b;
    }
    .refund-table tbody td {
        padding: 12px 16px;
        vertical-align: middle;
    }

    /* ─── Fixed column widths ─── */
    .refund-table .col-id {
        width: 5%;
        text-align: center;
        font-weight: 600;
        color: #6b7280;
    }
    .dark .refund-table .col-id {
        color: #9ca3af;
    }
    .refund-table .col-user {
        width: 18%;
    }
    .refund-table .col-loan {
        width: 8%;
        text-align: center;
    }
    .refund-table .col-amount {
        width: 12%;
        text-align: right;
        font-weight: 600;
    }
    .refund-table .col-method {
        width: 10%;
        text-align: center;
    }
    .refund-table .col-status {
        width: 12%;
        text-align: center;
    }
    .refund-table .col-reason {
        width: 18%;
        word-break: break-word;
    }
    .refund-table .col-refunded_at {
        width: 12%;
        text-align: center;
    }
    .refund-table .col-actions {
        width: 8%;
        text-align: center;
        white-space: nowrap;
    }

    /* ─── Badges ─── */
    .refund-table .badge {
        display: inline-block;
        padding: 0.2rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
    }
    .refund-table .badge-completed {
        background: #dcfce7;
        color: #166534;
    }
    .dark .refund-table .badge-completed {
        background: #14532d;
        color: #86efac;
    }
    .refund-table .badge-pending {
        background: #fef9c3;
        color: #854d0e;
    }
    .dark .refund-table .badge-pending {
        background: #713f12;
        color: #fde047;
    }
    .refund-table .badge-none {
        background: #f1f5f9;
        color: #475569;
    }
    .dark .refund-table .badge-none {
        background: #334155;
        color: #94a3b8;
    }

    /* ─── Action buttons ─── */
    .refund-table .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        transition: all 0.15s;
        background: transparent;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 1rem;
        margin: 0 2px;
    }
    .refund-table .action-btn:hover {
        background: rgba(0,0,0,0.05);
        transform: scale(1.1);
    }
    .dark .refund-table .action-btn:hover {
        background: rgba(255,255,255,0.08);
    }
    .refund-table .action-btn.approve { color: #22c55e; }
    .refund-table .action-btn.approve:hover { background: #f0fdf4; color: #16a34a; }
    .dark .refund-table .action-btn.approve { color: #4ade80; }
    .dark .refund-table .action-btn.approve:hover { background: #1e293b; color: #86efac; }

    .refund-table .action-btn.reject { color: #ef4444; }
    .refund-table .action-btn.reject:hover { background: #fef2f2; color: #dc2626; }
    .dark .refund-table .action-btn.reject { color: #f87171; }
    .dark .refund-table .action-btn.reject:hover { background: #1e293b; color: #fca5a5; }

    .refund-table .action-btn.view { color: #3b82f6; }
    .refund-table .action-btn.view:hover { background: #eff6ff; color: #2563eb; }
    .dark .refund-table .action-btn.view { color: #60a5fa; }
    .dark .refund-table .action-btn.view:hover { background: #1e293b; color: #93c5fd; }

    .refund-table .completed-text {
        color: #16a34a;
        font-weight: 500;
    }
    .dark .refund-table .completed-text {
        color: #4ade80;
    }

    /* ─── Responsive ─── */
    @media (max-width: 768px) {
        .refund-table thead th, .refund-table tbody td {
            padding: 8px 10px;
            font-size: 0.8rem;
        }
        .refund-table .col-reason {
            max-width: 120px;
        }
    }
</style>

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

<!-- ===== REFUND TABLE – Styled like Payment Records ===== -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="refund-table">
            <thead>
                <tr>
                    <th class="col-id">#</th>
                    <th class="col-user">User</th>
                    <th class="col-loan">Loan</th>
                    <th class="col-amount">Amount</th>
                    <th class="col-method">Method</th>
                    <th class="col-status">Refund Status</th>
                    <th class="col-reason">Refund Reason</th>
                    <th class="col-refunded_at">Refunded At</th>
                    <th class="col-actions">Actions</th>
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
                            $badgeClass = match($refundStatus) {
                                'completed' => 'badge-completed',
                                'pending'   => 'badge-pending',
                                default     => 'badge-none'
                            };
                        ?>
                        <tr>
                            <td class="col-id">#<?= $id ?></td>
                            <td class="col-user">
                                <div class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($userName) ?></div>
                                <?php if ($userEmail): ?>
                                    <div class="text-xs text-gray-400"><?= htmlspecialchars($userEmail) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="col-loan">#<?= $refund['loan_id'] ?? '?' ?></td>
                            <td class="col-amount"><?= number_format($amount, 2) ?> MMK</td>
                            <td class="col-method">
                                <span class="badge badge-none" style="background:#dbeafe;color:#1e40af; dark:background:#1e3a5f; dark:color:#93c5fd;">
                                    <?= htmlspecialchars(ucfirst($paymentMethod)) ?>
                                </span>
                            </td>
                            <td class="col-status">
                                <span class="badge <?= $badgeClass ?>"><?= $refundLabel ?></span>
                            </td>
                            <td class="col-reason text-gray-700 dark:text-gray-300 break-words max-w-[200px]">
                                <?= htmlspecialchars($refundReason) ?>
                            </td>
                            <td class="col-refunded_at text-gray-700 dark:text-gray-300 text-sm">
                                <?= $refundedAt ? date('d M Y, H:i', strtotime($refundedAt)) : '—' ?>
                            </td>
                            <td class="col-actions">
                                <?php if ($refundStatus === 'pending'): ?>
                                    <form action="<?= BASE_URL ?>/librarian/refunds/<?= $id ?>/approve" method="POST" class="inline">
                                        <button type="submit" class="action-btn approve" title="Approve Refund" onclick="return confirm('Approve this refund?')">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/librarian/refunds/<?= $id ?>/reject" method="POST" class="inline">
                                        <button type="submit" class="action-btn reject" title="Reject Refund" onclick="return confirm('Reject this refund?')">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </form>
                                <?php elseif ($refundStatus === 'completed'): ?>
                                    <span class="completed-text"><i class="fas fa-check-circle"></i> Done</span>
                                <?php else: ?>
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/librarian/payments/<?= $id ?>" class="action-btn view" title="View Payment">
                                    <i class="fas fa-eye"></i>
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