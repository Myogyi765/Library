<?php

$settings = $settings ?? [];

$finePerDay       = $settings['fine_per_day'] ?? 500;
$borrowingFee     = $settings['borrowing_fee'] ?? 0;
$maxBorrowDays    = $settings['max_borrow_days'] ?? 14;
$maxBorrowLimit   = $settings['max_borrow_limit'] ?? 5;
$gracePeriodDays  = $settings['grace_period_days'] ?? 3;
$membershipFee    = $settings['membership_fee'] ?? 0;
// ❌ $lateReturnFee line removed completely
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-coins text-yellow-600 mr-2"></i>Fine & Fee Settings
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Configure library fees, fines, and borrowing rules</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 p-4 rounded-lg mb-4">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 p-4 rounded-lg mb-4">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <form action="<?= BASE_URL ?>/admin/fines/update" method="POST" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Fine per Day -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fine per Day (MMK)
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Amount charged per day for overdue books</p>
                    <input type="number" name="fine_per_day" 
                           value="<?= htmlspecialchars($finePerDay) ?>"
                           min="0" step="100"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Borrowing Fee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Borrowing Fee (MMK)
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Fee per book borrowed</p>
                    <input type="number" name="borrowing_fee" 
                           value="<?= htmlspecialchars($borrowingFee) ?>"
                           min="0" step="100"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Max Borrow Days -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Max Borrow Days
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Maximum days a book can be borrowed</p>
                    <input type="number" name="max_borrow_days" 
                           value="<?= htmlspecialchars($maxBorrowDays) ?>"
                           min="1" max="365"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Max Borrow Limit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Max Books per User
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Maximum number of books a user can borrow at once</p>
                    <input type="number" name="max_borrow_limit" 
                           value="<?= htmlspecialchars($maxBorrowLimit) ?>"
                           min="1" max="50"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Grace Period -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Grace Period (Days)
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Days allowed before fine applies</p>
                    <input type="number" name="grace_period_days" 
                           value="<?= htmlspecialchars($gracePeriodDays) ?>"
                           min="0" max="30"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Membership Fee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Annual Membership Fee (MMK)
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Annual fee for library membership</p>
                    <input type="number" name="membership_fee" 
                           value="<?= htmlspecialchars($membershipFee) ?>"
                           min="0" step="1000"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Late Return Fee – REMOVED completely -->
               
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save Settings
                </button>
                <a href="<?= BASE_URL ?>/admin/dashboard" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Current Settings Summary</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Fine per Day:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= number_format($finePerDay) ?> MMK
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Max Borrow Days:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= $maxBorrowDays ?> days
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Max Books per User:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= $maxBorrowLimit ?>
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Grace Period:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= $gracePeriodDays ?> days
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Borrowing Fee:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= number_format($borrowingFee) ?> MMK
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Membership Fee:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= number_format($membershipFee) ?> MMK
                </span>
            </div>
        </div>
    </div>
</div>