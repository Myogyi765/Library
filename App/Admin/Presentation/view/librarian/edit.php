<?php
// Redirect if librarian data is missing
if (!isset($librarian)) {
    header('Location: ' . BASE_URL . '/admin/librarian');
    exit;
}

// Include layout header
require_once BASE_PATH . '/view/layout/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-2xl animate-fade-in">
    <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden border border-gray-200/50 dark:border-gray-700/50 transition-all duration-300">

        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gray-50/80 dark:bg-gray-900/50 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl">
                    <i class="fas fa-user-edit text-indigo-600 dark:text-indigo-400 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Librarian</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Update staff member information</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="<?= BASE_URL ?>/admin/librarian/edit/<?= $librarian->getId() ?>" method="POST" class="p-6 space-y-5">

            <!-- Error Messages -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-rose-50 dark:bg-rose-900/30 border-l-4 border-rose-500 text-rose-700 dark:text-rose-300 p-4 rounded-r-xl shadow-sm flex items-center gap-3 animate-slide-in">
                    <i class="fas fa-exclamation-circle text-rose-500 text-xl"></i>
                    <span><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Success Message (optional, if you set one) -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 text-emerald-700 dark:text-emerald-300 p-4 rounded-r-xl shadow-sm flex items-center gap-3 animate-slide-in">
                    <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                    <span><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Name -->
            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="fas fa-user mr-2 text-indigo-500 dark:text-indigo-400"></i>Full Name
                </label>
                <div class="relative">
                    <i class="fas fa-user-circle absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="<?= htmlspecialchars($librarian->getName()) ?>"
                        required
                        class="w-full pl-10 pr-4 py-2.5 bg-white/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 outline-none transition-all duration-200 hover:border-indigo-400 dark:hover:border-indigo-500 text-gray-900 dark:text-white">
                </div>
            </div>

            <!-- Department -->
            <div class="space-y-1.5">
                <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="fas fa-building mr-2 text-indigo-500 dark:text-indigo-400"></i>Department
                </label>
                <div class="relative">
                    <i class="fas fa-building absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select
                        name="department"
                        id="department"
                        required
                        class="w-full pl-10 pr-4 py-2.5 bg-white/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 outline-none transition-all duration-200 hover:border-indigo-400 dark:hover:border-indigo-500 text-gray-900 dark:text-white appearance-none">
                        <option value="Fiction" <?= $librarian->getDepartment()->getValue() === 'Fiction' ? 'selected' : '' ?>>Fiction</option>
                        <option value="Non-Fiction" <?= $librarian->getDepartment()->getValue() === 'Non-Fiction' ? 'selected' : '' ?>>Non‑Fiction</option>
                        <option value="Science" <?= $librarian->getDepartment()->getValue() === 'Science' ? 'selected' : '' ?>>Science</option>
                        <option value="History" <?= $librarian->getDepartment()->getValue() === 'History' ? 'selected' : '' ?>>History</option>
                        <option value="Children" <?= $librarian->getDepartment()->getValue() === 'Children' ? 'selected' : '' ?>>Children</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none"></i>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                <a href="<?= BASE_URL ?>/admin/librarian"
                   class="w-full sm:w-auto px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 text-center font-medium">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button
                    type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98]">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>

        </form>
    </div>
</div>

<?php
// Include layout footer
require_once BASE_PATH . '/view/layout/footer.php';
?>

<!-- Enhanced Custom CSS -->
<style>
    /* ─── Animations ─── */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.4s ease-out forwards;
    }
    .animate-slide-in {
        animation: slideIn 0.3s ease-out forwards;
    }

    /* ─── Glass-morphism ─── */
    .backdrop-blur-sm {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    /* ─── Input focus glow ─── */
    input:focus, select:focus {
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
    .dark input:focus, .dark select:focus {
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
    }

    /* ─── Custom select arrow override ─── */
    select {
        background-image: none;
    }

    /* ─── Dark mode contrast ─── */
    .dark .bg-white\/90 {
        background-color: rgba(31, 41, 55, 0.92);
    }
    .dark .bg-gray-50\/80 {
        background-color: rgba(17, 24, 39, 0.8);
    }
    .dark .border-gray-200\/50 {
        border-color: rgba(55, 65, 81, 0.5);
    }
</style>