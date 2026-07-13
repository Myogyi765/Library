<?php
$pageTitle = 'Edit Loan';
?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-edit text-blue-600 dark:text-blue-400 mr-2"></i>Edit Loan
        </h2>
        <a href="<?= BASE_URL ?>/librarian/loans" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="<?= BASE_URL ?>/librarian/loans/update/<?= $loan->getId() ?>" method="POST">
            <input type="hidden" name="action" value="update">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User</label>
                    <p class="text-gray-900 dark:text-white"><?= htmlspecialchars($users[$loan->getUserId()]?->getName() ?? 'Unknown') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Book</label>
                    <p class="text-gray-900 dark:text-white"><?= htmlspecialchars($books[$loan->getBookId()]?->getTitle() ?? 'Unknown') ?></p>
                </div>
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                    <input type="date" name="due_date" id="due_date" required value="<?= $loan->getDueDate()->format('Y-m-d') ?>"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="<?= BASE_URL ?>/librarian/loans" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>