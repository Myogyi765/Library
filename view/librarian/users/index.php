<!-- Users Management -->
<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-users text-purple-600 dark:text-purple-400 mr-2"></i>Users Management
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage library users</p>
        </div>
        <button onclick="alert('Add User form would open here')" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-user-plus"></i> Add User
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                            <td class="px-4 py-3 text-gray-900 dark:text-white"><?= htmlspecialchars($user['name']) ?></td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400"><?= htmlspecialchars($user['phone']) ?></td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300"><?= htmlspecialchars($user['role']) ?></td>
                            <td class="px-4 py-3">
                                <?php if ($user['status'] === 'Active'): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button onclick="alert('Edit user: <?= $user['name'] ?>')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-2">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="if(confirm('Delete this user?')) alert('Deleted')" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>