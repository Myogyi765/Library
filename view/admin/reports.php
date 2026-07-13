<?php
// view/admin/reports.php
// Data is passed via $reportData from controller
$pageTitle = 'Reports | Admin';

// Default data if not provided
$reportData = $reportData ?? [
    'totalBooks' => 0,
    'availableBooks' => 0,
    'borrowedBooks' => 0,
    'totalUsers' => 0,
    'activeLoans' => 0,
    'overdueLoans' => 0,
    'monthlyLoans' => [],
    'popularBooks' => [],
    'recentActivities' => []
];
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-chart-bar text-blue-600 dark:text-blue-400 mr-2"></i>Reports & Analytics
        </h2>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="<?= BASE_URL ?>/admin/reports/export/csv" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
            <button onclick="location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Books</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= number_format($reportData['totalBooks']) ?></p>
                </div>
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                    <i class="fas fa-book text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Available Books</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= number_format($reportData['availableBooks']) ?></p>
                </div>
                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-full">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Borrowed Books</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= number_format($reportData['borrowedBooks']) ?></p>
                </div>
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
                    <i class="fas fa-hand-holding text-yellow-600 dark:text-yellow-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Overdue Loans</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= number_format($reportData['overdueLoans']) ?></p>
                </div>
                <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Two-column layout: Chart & Popular Books -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Loan Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                <i class="fas fa-calendar-alt mr-1 text-gray-400"></i> Monthly Loan Activity
            </h3>
            <?php if (!empty($reportData['monthlyLoans'])): ?>
                <div class="space-y-2">
                    <?php 
                    $max = max($reportData['monthlyLoans']) ?: 1;
                    foreach ($reportData['monthlyLoans'] as $month => $count): 
                        $percent = round(($count / $max) * 100);
                    ?>
                        <div class="flex items-center gap-2">
                            <span class="w-8 text-xs font-medium text-gray-600 dark:text-gray-400"><?= $month ?></span>
                            <div class="flex-1 h-5 bg-gray-200 dark:bg-gray-700 rounded overflow-hidden">
                                <div class="h-full bg-blue-500 rounded transition-all" style="width: <?= $percent ?>%;"></div>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 w-8 text-right"><?= $count ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 dark:text-gray-400 text-sm">No monthly loan data available.</p>
            <?php endif; ?>
        </div>

        <!-- Popular Books -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                <i class="fas fa-star mr-1 text-yellow-400"></i> Most Popular Books
            </h3>
            <?php if (!empty($reportData['popularBooks'])): ?>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($reportData['popularBooks'] as $book): ?>
                        <li class="py-2 flex items-center justify-between">
                            <span class="text-sm text-gray-800 dark:text-gray-200"><?= htmlspecialchars($book['title']) ?></span>
                            <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 px-2 py-0.5 rounded-full"><?= $book['borrows'] ?> borrows</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500 dark:text-gray-400 text-sm">No popular book data available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activities Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                <i class="fas fa-clock mr-1 text-gray-400"></i> Recent Activities
            </h3>
            <a href="<?= BASE_URL ?>/admin/activities" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View All</a>
        </div>
        <?php if (!empty($reportData['recentActivities'])): ?>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">User</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Action</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($reportData['recentActivities'] as $activity): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-200"><?= htmlspecialchars($activity['user']) ?></td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300"><?= htmlspecialchars($activity['action']) ?></td>
                            <td class="px-4 py-2 text-gray-500 dark:text-gray-400 text-xs"><?= date('M d, Y H:i', strtotime($activity['date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                No recent activities found.
            </div>
        <?php endif; ?>
    </div>
</div>