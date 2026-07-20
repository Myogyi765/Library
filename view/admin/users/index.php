<?php

?>

<style>
    
    .user-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .user-table thead th {
        background: #f8fafc;
        color: #1e293b;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 2px solid #e2e8f0;
        text-align: center;          
        vertical-align: middle;      
    }
    .dark .user-table thead th {
        background: #1e293b;
        color: #e2e8f0;
        border-bottom-color: #334155;
    }
    .user-table tbody td {
        padding: 12px 16px;
        vertical-align: middle;      
        text-align: center;          
    }
    .user-table tbody td.actions-cell {
        text-align: center;
        padding: 2px 2px;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
        color: white;
        background: #2563eb;
        flex-shrink: 0;
        margin-right: 10px;
    }
    .dark .user-avatar {
        background: #3b82f6;
    }

    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        border: none;
    }
    .status-badge i { font-size: 0.6rem; }
    
    
    .status-badge.active { 
        background: #dcfce7; 
        color: #166534; 
    }
    .dark .status-badge.active { 
        background: #14532d; 
        color: #86efac; 
    }
    
    
    .status-badge.inactive { 
        background: #f1f5f9; 
        color: #475569; 
    }
    .dark .status-badge.inactive { 
        background: #334155; 
        color: #94a3b8; 
    }
    
    
    .status-badge.pending { 
        background: #fef3c7; 
        color: #92400e; 
    }
    .dark .status-badge.pending { 
        background: #78350f; 
        color: #fcd34d; 
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        border-radius: 6px;
        transition: all 0.15s;
        background: transparent;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 0.9rem;
        margin: 0;
    }
    .action-btn:hover {
        transform: scale(1.15);
        background: rgba(0,0,0,0.05);
    }
    .dark .action-btn:hover {
        background: rgba(255,255,255,0.08);
    }
    .action-btn.view { color: #3b82f6; }
    .action-btn.view:hover { background: #eff6ff; color: #2563eb; }
    .dark .action-btn.view { color: #60a5fa; }
    .dark .action-btn.view:hover { background: #1e293b; color: #93c5fd; }
    
    .action-btn.edit { color: #f59e0b; }
    .action-btn.edit:hover { background: #fffbeb; color: #d97706; }
    .dark .action-btn.edit { color: #fbbf24; }
    .dark .action-btn.edit:hover { background: #1e293b; color: #fcd34d; }
    
    .action-btn.deactivate { color: #8b5cf6; }
    .action-btn.deactivate:hover { background: #f5f3ff; color: #7c3aed; }
    .dark .action-btn.deactivate { color: #a78bfa; }
    .dark .action-btn.deactivate:hover { background: #1e293b; color: #c4b5fd; }
    
    .action-btn.activate { color: #22c55e; }
    .action-btn.activate:hover { background: #f0fdf4; color: #16a34a; }
    .dark .action-btn.activate { color: #4ade80; }
    .dark .action-btn.activate:hover { background: #1e293b; color: #86efac; }
    
    .action-btn.delete { color: #ef4444; }
    .action-btn.delete:hover { background: #fef2f2; color: #dc2626; }
    .dark .action-btn.delete { color: #f87171; }
    .dark .action-btn.delete:hover { background: #1e293b; color: #fca5a5; }

    .search-input {
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        font-size: 0.9rem;
        width: 220px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .dark .search-input {
        background: #1e293b;
        border-color: #334155;
        color: #e2e8f0;
    }
    .search-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        width: 260px;
    }

    .empty-state-icon {
        opacity: 0.5;
        transition: opacity 0.3s;
    }
    .empty-state-icon:hover { opacity: 0.8; }

    .serial-number {
        text-align: center;
        font-weight: 500;
        color: #6b7280;
        width: 50px;
    }
    .dark .serial-number {
        color: #9ca3af;
    }

    .pagination-btn {
        padding: 0.3rem 0.8rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        color: #475569;
        font-size: 0.85rem;
        transition: all 0.2s;
        cursor: pointer;
    }
    .dark .pagination-btn {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    .pagination-btn:hover:not(:disabled) {
        background: #f1f5f9;
        border-color: #2563eb;
    }
    .pagination-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .pagination-btn.active {
        background: #2563eb;
        border-color: #2563eb;
        color: white;
    }
    .dark .pagination-btn.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    @media (max-width: 640px) {
        .user-table thead th, .user-table tbody td {
            padding: 8px 10px;
            font-size: 0.8rem;
        }
        .search-input { width: 140px; }
        .search-input:focus { width: 180px; }
        .user-avatar { width: 28px; height: 28px; font-size: 0.7rem; margin-right: 6px; }
        .serial-number { width: 35px; }
        .action-btn {
            width: 28px;
            height: 28px;
            font-size: 0.8rem;
        }
    }
</style>

<div class="flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-5">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-users text-blue-600 dark:text-blue-400 mr-2"></i>User Management
            </h1>
            <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-sm font-medium px-3 py-1 rounded-full">
                <?= count($users) ?> users
            </span>
        </div>
        <div class="flex items-center gap-3 mt-3 md:mt-0">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                <input type="text" placeholder="Search users..." class="search-input">
            </div>
            <a href="<?= BASE_URL ?>/admin/users/create" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Add User
            </a>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-2 rounded-lg mb-4 flex items-center justify-between">
            <span><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?></span>
            <button onclick="this.parentElement.style.display='none'" class="text-green-700 dark:text-green-300 hover:text-green-900"><i class="fas fa-times"></i></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-2 rounded-lg mb-4 flex items-center justify-between">
            <span><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?></span>
            <button onclick="this.parentElement.style.display='none'" class="text-red-700 dark:text-red-300 hover:text-red-900"><i class="fas fa-times"></i></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="user-table">
                <thead>
                    <tr>
                        <th class="serial-number">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th class="actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="serial-number"><?= $counter++ ?></td>
                                <td>
                                    <div class="flex items-center justify-center">
                                        <span class="user-avatar">
                                            <?= strtoupper(substr($user->getName(), 0, 1)) ?>
                                        </span>
                                        <span class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($user->getName()) ?></span>
                                    </div>
                                </td>
                                <!-- ✅ Email Column – Truncated if longer than 30 characters -->
                                <td title="<?= htmlspecialchars($user->getEmail()->getValue()) ?>">
                                    <?php 
                                    $email = $user->getEmail()->getValue();
                                    if (strlen($email) > 30) {
                                        echo htmlspecialchars(substr($email, 0, 28)) . '...';
                                    } else {
                                        echo htmlspecialchars($email);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?= $user->getPhone() ? htmlspecialchars($user->getPhone()->getValue()) : '<span class="text-gray-400">—</span>' ?>
                                </td>
                                <td>
                                    <?php
                                    // ✅ Database supports: 'pending', 'active', 'inactive'
                                    $status = $user->getStatus()->getValue();
                                    $statusClass = strtolower($status);
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i class="fas <?= match($status) {
                                            'active'   => 'fa-check-circle',
                                            'inactive' => 'fa-circle',
                                            'pending'  => 'fa-clock',
                                            default    => 'fa-circle'
                                        } ?>"></i>
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td><?= $user->getCreatedAt()->format('M d, Y') ?></td>
                                <td class="actions-cell">
                                    <div class="flex items-center justify-center gap-0.5 flex-nowrap">
                                        <!-- 👁️ View -->
                                        <a href="<?= BASE_URL ?>/admin/users/view/<?= $user->getId() ?>" 
                                           class="action-btn view" title="View User">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- ✏️ Edit -->
                                        <a href="<?= BASE_URL ?>/admin/users/edit/<?= $user->getId() ?>" 
                                           class="action-btn edit" title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- 🔄 Deactivate / Activate (Toggles: active ↔ inactive) -->
                                        <?php
                                        // ✅ Database supports: active, inactive, pending
                                        // Toggle between active and inactive
                                        $isActive = $user->getStatus()->getValue() === 'active';
                                        $actionClass = $isActive ? 'deactivate' : 'activate';
                                        $actionIcon = $isActive ? 'fa-pause-circle' : 'fa-play-circle';
                                        $actionTitle = $isActive ? 'Deactivate User (set to inactive)' : 'Activate User (set to active)';
                                        $actionText = $isActive ? 'deactivate' : 'activate';
                                        ?>
                                        <form action="<?= BASE_URL ?>/admin/users/toggle/<?= $user->getId() ?>" method="POST" class="inline">
                                            <button type="submit" 
                                                    class="action-btn <?= $actionClass ?>" 
                                                    title="<?= $actionTitle ?>"
                                                    onclick="return confirm('Are you sure you want to <?= $actionText ?> user #<?= $user->getId() ?>?')">
                                                <i class="fas <?= $actionIcon ?>"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- 🗑️ Delete -->
                                        <form action="<?= BASE_URL ?>/admin/users/delete/<?= $user->getId() ?>" method="POST" class="inline">
                                            <button type="submit" 
                                                    class="action-btn delete" 
                                                    title="Delete User"
                                                    onclick="return confirm('Are you sure you want to delete user #<?= $user->getId() ?>? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-users empty-state-icon text-4xl text-gray-300 dark:text-gray-600 mb-2 block"></i>
                                <p class="text-lg font-medium">No users found</p>
                                <p class="text-sm">There are currently no registered users.</p>
                                <a href="<?= BASE_URL ?>/admin/users/create" class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                                    <i class="fas fa-plus mr-1"></i> Add First User
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Table footer / Pagination -->
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 flex items-center justify-between text-sm rounded-b-xl">
            <span class="text-gray-600 dark:text-gray-400">
                Showing <strong><?= count($users) ?></strong> user<?= count($users) > 1 ? 's' : '' ?>
            </span>
            <div class="flex items-center gap-2">
                <button class="pagination-btn" disabled>Previous</button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn" disabled>Next</button>
            </div>
        </div>
    </div> 
</div>
