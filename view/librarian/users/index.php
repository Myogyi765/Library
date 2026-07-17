<?php
// Filter only users with role = 'user'
$filteredUsers = array_filter($users ?? [], function($u) {
    return $u->getRole() === 'user';
});
$filteredUsers = array_values($filteredUsers);
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
                    <?php if (!empty($filteredUsers)): ?>
                        <?php foreach ($filteredUsers as $user): ?>
                            <?php
                                $name  = $user->getName() ?? '';
                                $email = $user->getEmail() ?? '';
                                // Phone: if empty, show placeholder
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
    </div>
</div>