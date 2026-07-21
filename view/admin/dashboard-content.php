<?php
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
    <!-- Total Users -->
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

    <!-- Librarians -->
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

    <!-- Total Books -->
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

    <!-- Active Loans -->
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

<!-- Quick Actions & Chart + Table -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- Quick Actions (removed Add Book) -->
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
            <a href="<?= BASE_URL ?>/admin/reports" class="bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 rounded-lg p-4 text-center transition">
                <i class="fas fa-chart-bar text-purple-600 dark:text-purple-400 text-xl mb-1"></i>
                <p class="text-xs text-gray-700 dark:text-gray-300">View Reports</p>
            </a>
            <!-- Added an extra quick action to fill the grid: View All Users -->
            <a href="<?= BASE_URL ?>/admin/users" class="bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg p-4 text-center transition">
                <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl mb-1"></i>
                <p class="text-xs text-gray-700 dark:text-gray-300">View All Users</p>
            </a>
        </div>
    </div>

    <!-- System Summary (kept) -->
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

<!-- ===== NEW SECTION: Charts & Tables ===== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Chart: Book Status Distribution -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-chart-pie text-blue-500 mr-2"></i> Book Status Distribution
        </h3>
        <div class="h-64 relative">
            <canvas id="bookStatusChart"></canvas>
        </div>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400 flex justify-around">
            <span><span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-1"></span> Available (<?= number_format($stats['available']) ?>)</span>
            <span><span class="inline-block w-3 h-3 bg-orange-500 rounded-full mr-1"></span> Borrowed (<?= number_format($stats['borrowed']) ?>)</span>
        </div>
    </div>

    <!-- Table: Recent Loans / Activity -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-clock text-blue-500 mr-2"></i> Recent Activity
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">User</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Action</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Sample rows – replace with real data -->
                    <tr>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">John Doe</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Borrowed "The Great Gatsby"</td>
                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400 text-xs">2 min ago</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Jane Smith</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Returned "1984"</td>
                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400 text-xs">15 min ago</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Admin</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Added new user "Test User"</td>
                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400 text-xs">1 hour ago</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Librarian</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Approved payment #5</td>
                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400 text-xs">2 hours ago</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">User</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Requested loan for "Dune"</td>
                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400 text-xs">3 hours ago</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-3 text-right">
            <a href="<?= BASE_URL ?>/admin/reports" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View all activity →</a>
        </div>
    </div>
</div>

<!-- Chart.js CDN and initialization script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('bookStatusChart').getContext('2d');
    const available = <?= $stats['available'] ?: 0 ?>;
    const borrowed = <?= $stats['borrowed'] ?: 0 ?>;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Borrowed'],
            datasets: [{
                data: [available, borrowed],
                backgroundColor: ['#3b82f6', '#f97316'],
                borderColor: ['#ffffff', '#ffffff'],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false, // we show our own legend outside
                }
            },
            cutout: '70%',
        }
    });
});
</script>