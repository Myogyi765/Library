<?php
$pageTitle = $pageTitle ?? 'All Activities | Admin';
$activities = $activities ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$startDate = $startDate ?? '';
$endDate = $endDate ?? '';
$userSearch = $userSearch ?? '';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-history text-blue-600 dark:text-blue-400 mr-2"></i> All Activities
        </h2>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">
                Total: <?= number_format($total) ?>
            </span>
            <a href="<?= BASE_URL ?>/admin/reports" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="text-xs font-medium text-gray-700 dark:text-gray-300">From</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" 
                       class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-700 dark:text-gray-300">To</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" 
                       class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-700 dark:text-gray-300">User</label>
                <input type="text" name="user" placeholder="Search user..." value="<?= htmlspecialchars($userSearch) ?>" 
                       class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 w-40">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="<?= BASE_URL ?>/admin/activities" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-times"></i> Clear
            </a>
        </form>
    </div>

    <!-- Activities Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (!empty($activities)): ?>
                        <?php $counter = ($currentPage - 1) * ($perPage ?? 20) + 1; ?>
                        <?php foreach ($activities as $activity): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs"><?= $counter++ ?></td>
                                <td class="px-4 py-3 text-gray-800 dark:text-gray-200 font-medium">
                                    <?= htmlspecialchars($activity['user'] ?? 'System') ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    <?= htmlspecialchars($activity['action'] ?? '') ?>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                                    <?= isset($activity['date']) ? date('M d, Y H:i', strtotime($activity['date'])) : '' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-inbox text-2xl block mb-2"></i>
                                No activities found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Showing page <?= $currentPage ?> of <?= $totalPages ?>
                </span>
                <div class="flex gap-1">
                    <?php if ($currentPage > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>" 
                           class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                           class="px-3 py-1 text-sm rounded transition <?= $i == $currentPage ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>" 
                           class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>