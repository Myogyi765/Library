<?php
$settings = $settings ?? [];
$settingValues = [];
foreach ($settings as $setting) {
    $settingValues[$setting['setting_key']] = $setting['setting_value'];
}
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
                    <input type="number" name="settings[fine_per_day]" 
                           value="<?= $settingValues['fine_per_day'] ?? 500 ?>"
                           min="0" step="100"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Borrowing Fee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Borrowing Fee (MMK)
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Fee per book borrowed</p>
                    <input type="number" name="settings[borrowing_fee]" 
                           value="<?= $settingValues['borrowing_fee'] ?? 0 ?>"
                           min="0" step="100"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Max Borrow Days -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Max Borrow Days
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Maximum days a book can be borrowed</p>
                    <input type="number" name="settings[max_borrow_days]" 
                           value="<?= $settingValues['max_borrow_days'] ?? 14 ?>"
                           min="1" max="365"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Max Borrow Limit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Max Books per User
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Maximum number of books a user can borrow at once</p>
                    <input type="number" name="settings[max_borrow_limit]" 
                           value="<?= $settingValues['max_borrow_limit'] ?? 5 ?>"
                           min="1" max="50"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Grace Period -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Grace Period (Days)
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Days allowed before fine applies</p>
                    <input type="number" name="settings[grace_period_days]" 
                           value="<?= $settingValues['grace_period_days'] ?? 3 ?>"
                           min="0" max="30"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Membership Fee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Annual Membership Fee (MMK)
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Annual fee for library membership</p>
                    <input type="number" name="settings[membership_fee]" 
                           value="<?= $settingValues['membership_fee'] ?? 0 ?>"
                           min="0" step="1000"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>

                <!-- Late Return Fee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Late Return Fee (MMK)
                    </label>
                    <p class="text-xs text-gray-400 mb-1">Fixed fee for late returns</p>
                    <input type="number" name="settings[late_return_fee]" 
                           value="<?= $settingValues['late_return_fee'] ?? 0 ?>"
                           min="0" step="100"
                           class="w-full border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600">
                </div>
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
                    <?= number_format($settingValues['fine_per_day'] ?? 500) ?> MMK
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Max Borrow Days:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= $settingValues['max_borrow_days'] ?? 14 ?> days
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Max Books per User:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= $settingValues['max_borrow_limit'] ?? 5 ?>
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Grace Period:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= $settingValues['grace_period_days'] ?? 3 ?> days
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Borrowing Fee:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= number_format($settingValues['borrowing_fee'] ?? 0) ?> MMK
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Membership Fee:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">
                    <?= number_format($settingValues['membership_fee'] ?? 0) ?> MMK
                </span>
            </div>
        </div>
    </div>
</div>