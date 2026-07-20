<?php
// Variables passed from controller:
// $users (paginated list), $currentPage, $totalPages, $totalUsers, $perPage, $search
// Ensure numeric values are integers
$currentPage = (int) ($currentPage ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$totalUsers = (int) ($totalUsers ?? 0);
$perPage = (int) ($perPage ?? 10);
$search = $search ?? '';
?>

<!-- Users Management -->
<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-users text-blue-600 dark:text-blue-400 mr-2"></i>Users Management
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage library members (users only)</p>
        </div>
        <a href="<?= BASE_URL ?>/librarian/users/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition shadow-md hover:shadow-lg">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>

    <!-- Search Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form action="<?= BASE_URL ?>/librarian/users" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Search by name or email..." 
                       class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                <i class="fas fa-search mr-1"></i> Search
            </button>
            <a href="<?= BASE_URL ?>/librarian/users" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 text-sm">
                <i class="fas fa-times"></i> Clear
            </a>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-800 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (!empty($users)): ?>
                        <?php $counter = (($currentPage - 1) * $perPage) + 1; ?>
                        <?php foreach ($users as $user): ?>
                            <?php
                                $name  = $user->getName() ?? '';
                                $email = $user->getEmail() ?? '';
                                $phone = $user->getPhone() ?? '';
                                if (empty($phone) || $phone === '+95') {
                                    $phoneDisplay = '+95 -------';
                                } else {
                                    $phoneDisplay = $phone;
                                }
                                $role   = $user->getRole() ?? 'user';
                                $status = $user->getStatus() ?? 'pending';
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                                <td class="px-4 py-3 text-gray-900 dark:text-white"><?= htmlspecialchars($name) ?></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300"><?= htmlspecialchars($email) ?></td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400"><?= htmlspecialchars($phoneDisplay) ?></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300"><?= htmlspecialchars($role) ?></td>
                                <td class="px-4 py-3">
                                    <?php if (strtolower($status) === 'active'): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300" title="User account is inactive – cannot log in or borrow books.">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="<?= BASE_URL ?>/librarian/users/edit/<?= $user->getId() ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/librarian/users/delete/<?= $user->getId() ?>" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Delete this user?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php $counter++; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No users found. <a href="<?= BASE_URL ?>/librarian/users/create" class="text-blue-600 dark:text-blue-400 hover:underline">Add your first user</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ─── PAGINATION ─── -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 flex flex-col sm:flex-row items-center justify-between gap-3 rounded-b-xl">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Showing <strong><?= count($users) ?></strong> of <strong><?= number_format($totalUsers) ?></strong> users
                <span class="text-xs text-gray-400 dark:text-gray-500">(Page <?= $currentPage ?> of <?= $totalPages ?>)</span>
            </span>
            
            <div class="flex items-center gap-1.5 flex-wrap">
                <?php
                $queryParams = http_build_query([
                    'search' => $search,
                ]);
                ?>
                
                <!-- Previous -->
                <?php if ($currentPage > 1): ?>
                    <a href="?<?= $queryParams ?>&page_num=<?= $currentPage - 1 ?>" 
                       class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php else: ?>
                    <button class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 opacity-40 cursor-not-allowed text-sm" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                <?php endif; ?>

                <?php
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);
                ?>
                
                <?php if ($start > 1): ?>
                    <a href="?<?= $queryParams ?>&page_num=1" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm">1</a>
                    <?php if ($start > 2): ?>
                        <span class="text-gray-400 dark:text-gray-500 px-1">…</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="px-3 py-1.5 rounded-lg bg-blue-600 text-white border border-blue-600 text-sm font-bold"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= $queryParams ?>&page_num=<?= $i ?>" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?>
                        <span class="text-gray-400 dark:text-gray-500 px-1">…</span>
                    <?php endif; ?>
                    <a href="?<?= $queryParams ?>&page_num=<?= $totalPages ?>" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm"><?= $totalPages ?></a>
                <?php endif; ?>

                <!-- Next -->
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?= $queryParams ?>&page_num=<?= $currentPage + 1 ?>" 
                       class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <button class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 opacity-40 cursor-not-allowed text-sm" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>