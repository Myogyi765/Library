<?php
// view/librarian/loans/index.php
// This file is included by dashboard-content.php when $page === 'loans'
// Variables available: $loans, $users, $books, BASE_URL
?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-hand-holding text-blue-600 mr-2"></i>Loan Records
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage all library loans</p>
    </div>
    <!-- <a href="<?= BASE_URL ?>/librarian/loans/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition">
        <i class="fas fa-plus"></i> Issue Book
    </a> -->
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
        <div><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <button onclick="this.parentElement.style.display='none'" class="text-green-700 dark:text-green-300 hover:text-green-900"><i class="fas fa-times"></i></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
        <div><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?></div>
        <button onclick="this.parentElement.style.display='none'" class="text-red-700 dark:text-red-300 hover:text-red-900"><i class="fas fa-times"></i></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<!-- Loans Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Id</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Book</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Borrowed</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Due Date</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($loans)): ?>
                    <?php foreach ($loans as $loan): 
                        $user = $users[$loan->getUserId()] ?? null;
                        $book = $books[$loan->getBookId()] ?? null;
                        $status = $loan->getStatus()->getValue();
                        
                        $statusColor = match($status) {
                            'pending'           => 'purple',
                            'awaiting_payment'  => 'orange',
                            'active'            => 'green',
                            'returned'          => 'blue',
                            'rejected'          => 'gray',
                            'overdue'           => 'red',
                            default             => 'gray'
                        };
                        
                        $borrowedAt = $loan->getBorrowedAt() ? $loan->getBorrowedAt()->format('Y-m-d') : '—';
                        $dueDate = $loan->getDueDate() ? $loan->getDueDate()->format('Y-m-d') : '—';
                    ?>
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                            <td class="px-4 py-3"><?= $loan->getId() ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user ? $user->getName() : 'Unknown') ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($book ? $book->getTitle() : 'Unknown') ?></td>
                            <td class="px-4 py-3"><?= $borrowedAt ?></td>
                            <td class="px-4 py-3"><?= $dueDate ?></td>
                            <td class="px-4 py-3 text-center">
                                <!-- ✅ Fixed alignment and forced inline-block with no wrapping -->
                                <span class="inline-block whitespace-nowrap px-2 py-1 text-xs rounded-full bg-<?= $statusColor ?>-100 text-<?= $statusColor ?>-800 dark:bg-<?= $statusColor ?>-900/30 dark:text-<?= $statusColor ?>-300">
                                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3 flex-nowrap">
                                    <?php if ($status === 'pending'): ?>
                                        <!-- Confirm icon -->
                                        <form action="<?= BASE_URL ?>/librarian/loans/confirm/<?= $loan->getId() ?>" method="POST" class="inline">
                                            <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300" title="Confirm">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        </form>
                                        <!-- Reject icon -->
                                        <form action="<?= BASE_URL ?>/librarian/loans/reject/<?= $loan->getId() ?>" method="POST" class="inline">
                                            <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Reject">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    <?php elseif ($status === 'active'): ?>
                                        <!-- Return icon -->
                                        <form action="<?= BASE_URL ?>/librarian/loans/return/<?= $loan->getId() ?>" method="POST" class="inline">
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Return Book">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                        </form>
                                        <!-- Edit icon -->
                                        <a href="<?= BASE_URL ?>/librarian/loans/edit/<?= $loan->getId() ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                    <!-- Delete icon (always visible) -->
                                    <a href="<?= BASE_URL ?>/librarian/loans/delete/<?= $loan->getId() ?>" 
                                       class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" 
                                       title="Delete" 
                                       onclick="return confirm('Delete this loan record?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No loans recorded.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>