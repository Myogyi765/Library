<?php
// view/admin/dashboard-content.php
$pageTitle = $pageTitle ?? 'Admin Dashboard';
$stats = $stats ?? [
    'users' => 0,
    'librarian' => 0,
    'books' => 0,
    'available' => 0,
    'borrowed' => 0,
    'activeLoans' => 0,
    'overdue' => 0,
];
?>
<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Overview of your library system</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500 dark:text-gray-400"><?= date('F j, Y') ?></span>
        <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="fas fa-bell text-gray-600 dark:text-gray-300"></i>
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Users</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?= number_format($stats['users']) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-xs text-gray-400">+<?= rand(1, 20) ?> this month</div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Librarians</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?= number_format($stats['librarian']) ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                <i class="fas fa-user-graduate text-green-600 dark:text-green-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-xs text-gray-400">+<?= rand(0, 3) ?> this month</div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Books</p>
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"><?= number_format($stats['books']) ?></p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                <i class="fas fa-book text-yellow-600 dark:text-yellow-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-xs text-gray-400">Available: <?= number_format($stats['available']) ?></div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Active Loans</p>
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"><?= number_format($stats['activeLoans']) ?></p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                <i class="fas fa-hand-holding text-indigo-600 dark:text-indigo-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-xs text-red-500 dark:text-red-400">Overdue: <?= number_format($stats['overdue']) ?></div>
    </div>
</div>

<!-- Quick Actions & Additional Stats (optional) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="<?= BASE_URL ?>/admin/users/create" class="bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg p-4 text-center transition">
                <i class="fas fa-user-plus text-blue-600 dark:text-blue-400 text-xl mb-1"></i>
                <p class="text-xs text-gray-700 dark:text-gray-300">Add User</p>
            </a>
            <a href="<?= BASE_URL ?>/admin/librarian/create" class="bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg p-4 text-center transition">
                <i class="fas fa-user-graduate text-green-600 dark:text-green-400 text-xl mb-1"></i>
                <p class="text-xs text-gray-700 dark:text-gray-300">Add Librarian</p>
            </a>
            <a href="<?= BASE_URL ?>/admin/books/create" class="bg-yellow-50 dark:bg-yellow-900/20 hover:bg-yellow-100 dark:hover:bg-yellow-900/30 rounded-lg p-4 text-center transition">
                <i class="fas fa-book text-yellow-600 dark:text-yellow-400 text-xl mb-1"></i>
                <p class="text-xs text-gray-700 dark:text-gray-300">Add Book</p>
            </a>
            <a href="<?= BASE_URL ?>/admin/reports" class="bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 rounded-lg p-4 text-center transition">
                <i class="fas fa-chart-bar text-purple-600 dark:text-purple-400 text-xl mb-1"></i>
                <p class="text-xs text-gray-700 dark:text-gray-300">View Reports</p>
            </a>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><i class="fas fa-chart-simple text-green-500 mr-2"></i> System Summary</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">Borrowed Books</span>
                <span class="font-bold text-gray-900 dark:text-white"><?= number_format($stats['borrowed']) ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">Return Rate</span>
                <?php 
                    $total = ($stats['borrowed'] + $stats['available']);
                    $returnRate = $total > 0 ? round(($stats['available'] / $total) * 100) : 0;
                ?>
                <span class="font-bold text-blue-600 dark:text-blue-400"><?= $returnRate ?>%</span>
            </div>
            <div class="flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-3">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total Loans</span>
                <span class="font-bold text-gray-900 dark:text-white"><?= number_format($stats['activeLoans'] + $stats['overdue']) ?></span>
            </div>
        </div>
    </div>
</div>