<?php
// This is a partial view – it expects $librarians (array of Librarian entities)
// It will be rendered inside the main layout. No header/footer here.
?>

<style>
    /* ===== Clean & Modern Design – Blue Accent (matching User List) ===== */
    .librarian-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .librarian-table thead th {
        background: #f8fafc;
        color: #1e293b;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
    }
    .dark .librarian-table thead th {
        background: #1e293b;
        color: #e2e8f0;
        border-bottom-color: #334155;
    }
    .librarian-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }
    .dark .librarian-table tbody tr {
        border-bottom-color: #1e293b;
    }
    .librarian-table tbody tr:hover {
        background: #f1f5f9;
    }
    .dark .librarian-table tbody tr:hover {
        background: #1e293b;
    }
    .librarian-table tbody td {
        padding: 12px 16px;
        vertical-align: middle;
    }

    /* Avatar – Blue for librarians (matching user avatar) */
    .librarian-avatar {
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
    .dark .librarian-avatar {
        background: #3b82f6;
    }

    /* Department Badge – Blue variant */
    .dept-badge {
        display: inline-flex;
        padding: 0.2rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background: #dbeafe;
        color: #1e40af;
    }
    .dark .dept-badge {
        background: #1e3a5f;
        color: #93c5fd;
    }

    /* Action Buttons – Blue accent */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 6px;
        transition: all 0.15s;
        color: #94a3b8;
        background: transparent;
        border: none;
        cursor: pointer;
    }
    .action-btn:hover { background: #f1f5f9; color: #2563eb; }
    .action-btn.delete:hover { color: #dc2626; }
    .dark .action-btn { color: #64748b; }
    .dark .action-btn:hover { background: #1e293b; color: #60a5fa; }
    .dark .action-btn.delete:hover { color: #f87171; }

    /* Primary Button – Blue */
    .btn-primary {
        padding: 0.5rem 1.2rem;
        background: #2563eb;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    .btn-primary:hover { background: #1d4ed8; }

    /* Empty State Icon */
    .empty-state-icon {
        opacity: 0.5;
        transition: opacity 0.3s;
    }
    .empty-state-icon:hover { opacity: 0.8; }

    /* Serial number column – center aligned */
    .serial-number {
        text-align: center;
        font-weight: 500;
        color: #6b7280;
    }
    .dark .serial-number {
        color: #9ca3af;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .librarian-table thead th, .librarian-table tbody td {
            padding: 8px 10px;
            font-size: 0.8rem;
        }
        .librarian-avatar { width: 28px; height: 28px; font-size: 0.7rem; margin-right: 6px; }
    }
</style>

<div class="flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-5">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-user-graduate text-blue-600 dark:text-blue-400 mr-2"></i>Librarian Management
            </h1>
            <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-sm font-medium px-3 py-1 rounded-full">
                <?= count($librarians) ?> librarians
            </span>
        </div>
        <div class="flex items-center gap-3 mt-3 md:mt-0">
            <a href="<?= BASE_URL ?>/admin/librarian/create" class="btn-primary">
                <i class="fas fa-user-plus"></i> Add Librarian
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
            <table class="librarian-table">
                <thead>
                    <tr>
                        <th class="serial-number" style="width: 50px;">#</th>   <!-- Serial Number column -->
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Hired</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($librarians)): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($librarians as $lib): ?>
                            <tr>
                                <td class="serial-number"><?= $counter++ ?></td>
                                <td>
                                    <div class="flex items-center">
                                        <span class="librarian-avatar">
                                            <?= strtoupper(substr($lib->getName(), 0, 1)) ?>
                                        </span>
                                        <span class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($lib->getName()) ?></span>
                                    </div>
                                </td>
                                <td class="text-gray-600 dark:text-gray-300"><?= htmlspecialchars($lib->getEmail()->getValue()) ?></td>
                                <td>
                                    <span class="dept-badge">
                                        <?= htmlspecialchars($lib->getDepartment()->getValue()) ?>
                                    </span>
                                </td>
                                <td class="text-gray-500 dark:text-gray-400 text-sm">
                                    <?= $lib->getHiredAt() ? $lib->getHiredAt()->format('M d, Y') : '—' ?>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="<?= BASE_URL ?>/admin/librarian/edit/<?= $lib->getId() ?>"
                                           class="action-btn" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $lib->getId() ?>)"
                                                class="action-btn delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-user-graduate empty-state-icon text-4xl text-gray-300 dark:text-gray-600 mb-2 block"></i>
                                <p class="text-lg font-medium">No librarians found</p>
                                <p class="text-sm">There are currently no registered librarians.</p>
                                <a href="<?= BASE_URL ?>/admin/librarian/create" class="inline-block mt-4 btn-primary">
                                    <i class="fas fa-user-plus"></i> Add Librarian
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Table footer -->
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 flex items-center justify-between text-sm rounded-b-xl">
            <span class="text-gray-600 dark:text-gray-400">
                Showing <strong><?= count($librarians) ?></strong> librarian<?= count($librarians) > 1 ? 's' : '' ?>
            </span>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this librarian?')) {
        window.location.href = '<?= BASE_URL ?>/admin/librarian/delete/' + id;
    }
}
</script>