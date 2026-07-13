<?php
$pageTitle = 'Issue Book';
?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-hand-holding-heart text-blue-600 dark:text-blue-400 mr-2"></i>Issue Book
        </h2>
        <a href="<?= BASE_URL ?>/librarian/loans" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="<?= BASE_URL ?>/librarian/loans/store" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User <span class="text-red-500">*</span></label>
                    <select name="user_id" id="user_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user->getId() ?>"><?= htmlspecialchars($user->getName()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="book_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Book <span class="text-red-500">*</span></label>
                    <select name="book_id" id="book_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="">Select Book</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?= $book->getId() ?>"><?= htmlspecialchars($book->getTitle()) ?> (Available: <?= $book->getAvailableQuantity() ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="due_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due in (days)</label>
                    <input type="number" name="due_days" id="due_days" value="14" min="1" max="30"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="<?= BASE_URL ?>/librarian/loans" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-check"></i> Issue Book
                </button>
            </div>
        </form>
    </div>
</div>