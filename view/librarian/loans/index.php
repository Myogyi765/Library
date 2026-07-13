<?php
$pageTitle = $pageTitle ?? 'Manage Loans';
?>
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        <i class="fas fa-hand-holding text-blue-600 dark:text-blue-400 mr-2"></i>Loan Records
    </h2>
    <a href="<?= BASE_URL ?>/librarian/loans/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition">
        <i class="fas fa-plus"></i> Issue Book
    </a>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
        <span><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?></span>
        <button onclick="this.parentElement.style.display='none'" class="text-green-700 dark:text-green-300 hover:text-green-900"><i class="fas fa-times"></i></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
        <span><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?></span>
        <button onclick="this.parentElement.style.display='none'" class="text-red-700 dark:text-red-300 hover:text-red-900"><i class="fas fa-times"></i></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Book</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Borrowed</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Due Date</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($loans)): ?>
                    <?php foreach ($loans as $loan): 
                        // ✅ Get user and book from mapped arrays
                        $userId = $loan->getUserId();
                        $bookId = $loan->getBookId();
                        
                        $user = $users[$userId] ?? null;
                        $book = $books[$bookId] ?? null;
                        
                        // ✅ Debug missing users/books
                        if (!$user) {
                            error_log("⚠️ User not found for ID: " . $userId . " (Loan ID: " . $loan->getId() . ")");
                        }
                        if (!$book) {
                            error_log("⚠️ Book not found for ID: " . $bookId . " (Loan ID: " . $loan->getId() . ")");
                        }
                        
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
                            <td class="px-4 py-3">
                                <?php if ($user): ?>
                                    <?= htmlspecialchars($user->getName()) ?>
                                <?php else: ?>
                                    <span class="text-red-500">User #<?= $userId ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($book): ?>
                                    <?= htmlspecialchars($book->getTitle()) ?>
                                <?php else: ?>
                                    <span class="text-red-500">Book #<?= $bookId ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3"><?= $borrowedAt ?></td>
                            <td class="px-4 py-3"><?= $dueDate ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs rounded-full bg-<?= $statusColor ?>-100 text-<?= $statusColor ?>-800 dark:bg-<?= $statusColor ?>-900/30 dark:text-<?= $statusColor ?>-300">
                                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <?php if ($status === 'pending'): ?>
                                    <form action="<?= BASE_URL ?>/librarian/loans/confirm/<?= $loan->getId() ?>" method="POST" class="inline">
                                        <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 mr-2" title="Confirm">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/librarian/loans/reject/<?= $loan->getId() ?>" method="POST" class="inline">
                                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 mr-2" title="Reject">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </form>
                                <?php elseif ($status === 'awaiting_payment'): ?>
                                    <span class="text-xs text-orange-600 dark:text-orange-400">Awaiting payment</span>
                                <?php elseif ($status === 'active'): ?>
                                    <form action="<?= BASE_URL ?>/librarian/loans/return/<?= $loan->getId() ?>" method="POST" class="inline">
                                        <button type="submit" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-2" title="Return Book">
                                            <i class="fas fa-undo-alt"></i>
                                        </button>
                                    </form>
                                    <a href="<?= BASE_URL ?>/librarian/loans/edit/<?= $loan->getId() ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-2" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php elseif ($status === 'rejected'): ?>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Rejected</span>
                                <?php elseif ($status === 'returned'): ?>
                                    <span class="text-xs text-green-600 dark:text-green-400">Returned</span>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/librarian/loans/delete/<?= $loan->getId() ?>" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete" onclick="return confirm('Delete this loan record?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
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