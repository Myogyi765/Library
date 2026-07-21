<?php
$user = $user ?? null;
if (!$user) {
    $_SESSION['error_message'] = 'User not found.';
    header('Location: ' . BASE_URL . '/librarian/users');
    exit;
}
?>
<style>
    .profile-card { transition: all 0.2s ease; }
    .profile-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 700;
        color: white;
        background: #2563eb;
        flex-shrink: 0;
    }
    .dark .profile-avatar { background: #3b82f6; }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark .info-row { border-bottom-color: #1e293b; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #64748b; font-size: 0.85rem; font-weight: 500; }
    .dark .info-label { color: #94a3b8; }
    .info-value { color: #1e293b; font-weight: 600; font-size: 0.9rem; }
    .dark .info-value { color: #e2e8f0; }
    .btn-group { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .btn-group .btn {
        padding: 0.4rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-group .btn:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .btn-edit { background: #f59e0b; color: white; }
    .btn-edit:hover { background: #d97706; }
    .btn-toggle { background: #8b5cf6; color: white; }
    .btn-toggle:hover { background: #7c3aed; }
    .btn-delete { background: #ef4444; color: white; }
    .btn-delete:hover { background: #dc2626; }
    .btn-back { background: #e2e8f0; color: #475569; }
    .dark .btn-back { background: #334155; color: #94a3b8; }
    .btn-back:hover { background: #cbd5e1; }
    .dark .btn-back:hover { background: #475569; }
    .status-badge {
        padding: 0.2rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .status-badge.active { background: #dcfce7; color: #166534; }
    .dark .status-badge.active { background: #14532d; color: #86efac; }
    .status-badge.pending { background: #fef3c7; color: #92400e; }
    .dark .status-badge.pending { background: #78350f; color: #fcd34d; }
    .status-badge.inactive { background: #f1f5f9; color: #475569; }
    .dark .status-badge.inactive { background: #334155; color: #94a3b8; }
</style>

<div class="flex flex-col space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-user text-blue-600 dark:text-blue-400 mr-2"></i>User Profile
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">View and manage user account</p>
        </div>
        <a href="<?= BASE_URL ?>/librarian/users" class="btn btn-back"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="profile-card bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 flex items-center gap-4">
            <div class="profile-avatar"><?= strtoupper(substr($user->getName(), 0, 1)) ?></div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($user->getName()) ?></h2>
                <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($user->getEmail()->getValue()) ?></p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= ucfirst($user->getRole() ?? 'User') ?></span>
                    <span class="status-badge <?= $user->getStatus()->getValue() ?>">
                        <i class="fas <?= match($user->getStatus()->getValue()) {
                            'active'   => 'fa-check-circle',
                            'pending'  => 'fa-clock',
                            'inactive' => 'fa-circle',
                            default    => 'fa-circle'
                        } ?>"></i>
                        <?= ucfirst($user->getStatus()->getValue()) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1">
                <div class="info-row"><span class="info-label"><i class="fas fa-user text-blue-500 mr-2 w-4"></i>Full Name</span><span class="info-value"><?= htmlspecialchars($user->getName()) ?></span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-envelope text-blue-500 mr-2 w-4"></i>Email</span><span class="info-value"><?= htmlspecialchars($user->getEmail()->getValue()) ?></span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-phone text-blue-500 mr-2 w-4"></i>Phone</span><span class="info-value"><?= $user->getPhone() ? htmlspecialchars($user->getPhone()->getValue()) : '—' ?></span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-user-tag text-blue-500 mr-2 w-4"></i>Role</span><span class="info-value"><?= ucfirst($user->getRole() ?? 'User') ?></span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-calendar text-blue-500 mr-2 w-4"></i>Registered</span><span class="info-value"><?= $user->getCreatedAt() ? $user->getCreatedAt()->format('M d, Y') : '—' ?></span></div>
                <div class="info-row"><span class="info-label"><i class="fas fa-clock text-blue-500 mr-2 w-4"></i>Status</span><span class="info-value"><span class="status-badge <?= $user->getStatus()->getValue() ?>"><i class="fas <?= match($user->getStatus()->getValue()) { 'active' => 'fa-check-circle', 'pending' => 'fa-clock', 'inactive' => 'fa-circle', default => 'fa-circle' } ?>"></i> <?= ucfirst($user->getStatus()->getValue()) ?></span></span></div>
            </div>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
            <div class="btn-group">
                <a href="<?= BASE_URL ?>/librarian/users/edit/<?= $user->getId() ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                <form action="<?= BASE_URL ?>/librarian/users/toggle/<?= $user->getId() ?>" method="POST" class="inline">
                    <button type="submit" class="btn btn-toggle">
                        <i class="fas <?= $user->getStatus()->getValue() === 'active' ? 'fa-pause-circle' : 'fa-play-circle' ?>"></i>
                        <?= $user->getStatus()->getValue() === 'active' ? 'Deactivate' : 'Activate' ?>
                    </button>
                </form>
                <form action="<?= BASE_URL ?>/librarian/users/delete/<?= $user->getId() ?>" method="POST" class="inline">
                    <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete user #<?= $user->getId() ?>? This action cannot be undone.')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>