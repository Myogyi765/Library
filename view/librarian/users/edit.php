<?php
$user = $user ?? null;
if (!$user) {
    $_SESSION['error_message'] = 'User not found.';
    header('Location: ' . BASE_URL . '/librarian/users');
    exit;
}
?>
<style>
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
    .dark .form-label { color: #d1d5db; }
    .form-control {
        width: 100%;
        padding: 0.6rem 0.9rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 0.6rem;
        background: #ffffff;
        color: #1f2937;
        font-size: 0.95rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
    }
    .dark .form-control { background: #1e293b; border-color: #374151; color: #e5e7eb; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
    .form-hint { font-size: 0.75rem; color: #6b7280; margin-top: 0.2rem; }
    .dark .form-hint { color: #9ca3af; }
    .form-required { color: #ef4444; font-weight: 700; }
    .btn-primary {
        background: #3b82f6;
        color: white;
        padding: 0.6rem 1.5rem;
        border: none;
        border-radius: 0.6rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
    }
    .btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
    .btn-secondary {
        background: #e5e7eb;
        color: #374151;
        padding: 0.6rem 1.5rem;
        border: none;
        border-radius: 0.6rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
    }
    .dark .btn-secondary { background: #374151; color: #d1d5db; }
    .btn-secondary:hover { background: #d1d5db; }
    .dark .btn-secondary:hover { background: #4b5563; }
</style>

<div class="flex flex-col">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-user-edit text-blue-600 dark:text-blue-400 mr-2"></i>Edit User
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Editing: <strong><?= htmlspecialchars($user->getName()) ?></strong>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/librarian/users" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to Users
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <form action="<?= BASE_URL ?>/librarian/users/update/<?= $user->getId() ?>" method="POST" class="space-y-4">
            <div class="form-group">
                <label for="name" class="form-label"><i class="fas fa-user text-blue-500 mr-1"></i> Full Name <span class="form-required">*</span></label>
                <input type="text" name="name" id="name" required class="form-control" value="<?= htmlspecialchars($user->getName()) ?>">
            </div>

            <div class="form-group">
                <label for="email" class="form-label"><i class="fas fa-envelope text-blue-500 mr-1"></i> Email Address <span class="form-required">*</span></label>
                <input type="email" name="email" id="email" required class="form-control" value="<?= htmlspecialchars($user->getEmail()->getValue()) ?>">
            </div>

            <div class="form-group">
                <label for="password" class="form-label"><i class="fas fa-lock text-blue-500 mr-1"></i> Password</label>
                <input type="password" name="password" id="password" placeholder="Leave blank to keep current" minlength="6" class="form-control">
                <p class="form-hint">Leave blank to keep current password. Minimum 6 characters if changing.</p>
            </div>

            <div class="form-group">
                <label for="phone" class="form-label"><i class="fas fa-phone text-blue-500 mr-1"></i> Phone Number</label>
                <input type="text" name="phone" id="phone" class="form-control" value="<?= $user->getPhone() ? htmlspecialchars($user->getPhone()->getValue()) : '' ?>" placeholder="09xxxxxxxxx">
            </div>

            <div class="form-group">
                <label for="status" class="form-label"><i class="fas fa-circle text-blue-500 mr-1"></i> Status</label>
                <select name="status" id="status" class="form-control">
                    <?php $currentStatus = $user->getStatus()->getValue(); ?>
                    <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="btn-primary"><i class="fas fa-save mr-1"></i> Update User</button>
                <a href="<?= BASE_URL ?>/librarian/users" class="btn-secondary"><i class="fas fa-times mr-1"></i> Cancel</a>
                <span class="ml-auto text-sm text-gray-400 dark:text-gray-500">
                    <a href="<?= BASE_URL ?>/librarian/users/view/<?= $user->getId() ?>" class="text-blue-600 hover:text-blue-800 mr-3"><i class="fas fa-eye"></i> View Profile</a>
                    <form action="<?= BASE_URL ?>/librarian/users/toggle/<?= $user->getId() ?>" method="POST" class="inline">
                        <button type="submit" class="text-purple-600 hover:text-purple-800 bg-transparent border-0 cursor-pointer">
                            <i class="fas <?= $currentStatus === 'active' ? 'fa-pause' : 'fa-play' ?>"></i>
                            <?= $currentStatus === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                </span>
            </div>
        </form>
    </div>
</div>