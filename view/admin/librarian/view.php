<?php
$librarian = $librarian ?? null;
if (!$librarian) {
    $_SESSION['error_message'] = 'Librarian not found.';
    header('Location: ' . BASE_URL . '/admin/librarian');
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
    .btn-delete { background: #ef4444; color: white; }
    .btn-delete:hover { background: #dc2626; }
    .btn-back { background: #e2e8f0; color: #475569; }
    .dark .btn-back { background: #334155; color: #94a3b8; }
    .btn-back:hover { background: #cbd5e1; }
    .dark .btn-back:hover { background: #475569; }
    .dept-badge {
        padding: 0.2rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #dbeafe;
        color: #1e40af;
        display: inline-block;
    }
    .dark .dept-badge { background: #1e3a5f; color: #93c5fd; }
</style>

<div class="flex flex-col space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-user-graduate text-blue-600 dark:text-blue-400 mr-2"></i>Librarian Profile
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">View librarian details</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/librarian" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="profile-card bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 flex items-center gap-4">
            <div class="profile-avatar">
                <?= strtoupper(substr($librarian->getName(), 0, 1)) ?>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($librarian->getName()) ?></h2>
                <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($librarian->getEmail()->getValue()) ?></p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="dept-badge">
                        <?= htmlspecialchars($librarian->getDepartment()->getValue()) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-user text-blue-500 mr-2 w-4"></i>Full Name</span>
                    <span class="info-value"><?= htmlspecialchars($librarian->getName()) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-envelope text-blue-500 mr-2 w-4"></i>Email</span>
                    <span class="info-value"><?= htmlspecialchars($librarian->getEmail()->getValue()) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-building text-blue-500 mr-2 w-4"></i>Department</span>
                    <span class="info-value">
                        <span class="dept-badge"><?= htmlspecialchars($librarian->getDepartment()->getValue()) ?></span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-calendar text-blue-500 mr-2 w-4"></i>Hired Date</span>
                    <span class="info-value">
                        <?= $librarian->getHiredAt() ? $librarian->getHiredAt()->format('M d, Y') : '—' ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
            <div class="btn-group">
                <a href="<?= BASE_URL ?>/admin/librarian/edit/<?= $librarian->getId() ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="<?= BASE_URL ?>/admin/librarian/delete/<?= $librarian->getId() ?>" method="POST" class="inline">
                    <button type="submit" class="btn btn-delete" 
                            onclick="return confirm('Are you sure you want to delete this librarian? This action cannot be undone.')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>