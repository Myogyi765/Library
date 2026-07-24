<?php
// This is a partial view – it expects $librarian (Librarian entity)
$librarian = $librarian ?? null;
if (!$librarian) {
    $_SESSION['error_message'] = 'Librarian not found.';
    header('Location: ' . BASE_URL . '/admin/librarian');
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
                <i class="fas fa-user-edit text-blue-600 dark:text-blue-400 mr-2"></i>Edit Librarian
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Editing: <strong><?= htmlspecialchars($librarian->getName()) ?></strong>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/admin/librarian" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <form action="<?= BASE_URL ?>/admin/librarian/edit/<?= $librarian->getId() ?>" method="POST">
            <!-- Name -->
            <div class="form-group">
                <label for="name" class="form-label"><i class="fas fa-user text-blue-500 mr-1"></i> Name</label>
                <input type="text" name="name" id="name" required 
                       value="<?= htmlspecialchars($librarian->getName()) ?>" class="form-control">
            </div>

            <!-- Email (read-only) -->
            <div class="form-group">
                <label for="email" class="form-label"><i class="fas fa-envelope text-blue-500 mr-1"></i> Email</label>
                <input type="email" name="email" id="email" 
                       value="<?= htmlspecialchars($librarian->getEmail()->getValue()) ?>" 
                       class="form-control" disabled readonly>
            </div>

            <!-- Department – Fixed to Department 1, 2, 3 -->
            <div class="form-group">
                <label for="department" class="form-label"><i class="fas fa-building text-blue-500 mr-1"></i> Department</label>
                <select name="department" id="department" class="form-control">
                    <?php
                    // Get current department value, default to empty if not set
                    $currentDept = '';
                    if ($librarian->getDepartment()) {
                        $currentDept = $librarian->getDepartment()->getValue();
                    }
                    $departments = ['Department 1', 'Department 2', 'Department 3'];
                    foreach ($departments as $dept):
                    ?>
                        <option value="<?= $dept ?>" <?= $dept === $currentDept ? 'selected' : '' ?>><?= $dept ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="btn-primary"><i class="fas fa-save mr-1"></i> Update</button>
                <a href="<?= BASE_URL ?>/admin/librarian" class="btn-secondary"><i class="fas fa-times mr-1"></i> Cancel</a>
                <span class="ml-auto text-sm text-gray-400 dark:text-gray-500">
                    <a href="<?= BASE_URL ?>/admin/librarian/view/<?= $librarian->getId() ?>" class="text-blue-600 hover:text-blue-800 mr-3"><i class="fas fa-eye"></i> View</a>
                </span>
            </div>
        </form>
    </div>
</div>