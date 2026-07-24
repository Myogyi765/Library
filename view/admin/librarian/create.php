<?php
// Include layout header
require_once BASE_PATH . '/view/layout/header.php';

// Check for old input data (optional)
$oldName = $_SESSION['old_name'] ?? '';
$oldEmail = $_SESSION['old_email'] ?? '';
$oldDepartment = $_SESSION['old_department'] ?? '';
unset($_SESSION['old_name'], $_SESSION['old_email'], $_SESSION['old_department']);
?>

<div class="container mx-auto px-4 py-8 max-w-2xl animate-fade-in">
    <div
        class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden border border-gray-200/50 dark:border-gray-700/50 transition-all duration-300">

        <!-- Header -->
        <div
            class="px-6 py-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gray-50/80 dark:bg-gray-900/50 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/40 rounded-xl">
                    <i class="fas fa-user-plus text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Librarian</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add a new staff member to the library system</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="<?= BASE_URL ?>/admin/librarian/create" method="POST" class="p-6 space-y-5">

            <!-- Error Messages -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div
                    class="bg-rose-50 dark:bg-rose-900/30 border-l-4 border-rose-500 text-rose-700 dark:text-rose-300 p-4 rounded-r-xl shadow-sm flex items-center gap-3 animate-slide-in">
                    <i class="fas fa-exclamation-circle text-rose-500 text-xl"></i>
                    <span><?= htmlspecialchars($_SESSION['error_message']);
                    unset($_SESSION['error_message']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Name -->
            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="fas fa-user mr-2 text-blue-500 dark:text-blue-400"></i>Full Name
                </label>
                <div class="relative">
                    <i
                        class="fas fa-user-circle absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <input type="text" name="name" id="name" required value="<?= htmlspecialchars($oldName) ?>"
                        placeholder="e.g. Jane Doe"
                        class="w-full pl-10 pr-4 py-2.5 bg-white/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 outline-none transition-all duration-200 hover:border-blue-400 dark:hover:border-blue-500 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                </div>
            </div>

            <!-- Email -->
            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="fas fa-envelope mr-2 text-blue-500 dark:text-blue-400"></i>Email Address
                </label>
                <div class="relative">
                    <i
                        class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <input type="email" name="email" id="email" required value="<?= htmlspecialchars($oldEmail) ?>"
                        placeholder="librarian@library.com"
                        class="w-full pl-10 pr-4 py-2.5 bg-white/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 outline-none transition-all duration-200 hover:border-blue-400 dark:hover:border-blue-500 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                </div>
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="fas fa-lock mr-2 text-blue-500 dark:text-blue-400"></i>Password
                </label>
                <div class="relative">
                    <i class="fas fa-key absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <input type="password" name="password" id="password" required minlength="6"
                        placeholder="Minimum 6 characters"
                        class="w-full pl-10 pr-10 py-2.5 bg-white/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 outline-none transition-all duration-200 hover:border-blue-400 dark:hover:border-blue-500 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                    <button type="button" id="togglePassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <i class="fas fa-info-circle"></i>
                    <span>Use at least 6 characters with a mix of letters and numbers</span>
                </div>
            </div>

            <!-- Department – Fixed to Department 1, 2, 3 -->
            <div class="space-y-1.5">
                <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="fas fa-building mr-2 text-blue-500 dark:text-blue-400"></i>Department
                </label>
                <div class="relative">
                    <i
                        class="fas fa-building absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select name="department" id="department" required
                        class="w-full pl-10 pr-4 py-2.5 bg-white/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 outline-none transition-all duration-200 hover:border-blue-400 dark:hover:border-blue-500 text-gray-900 dark:text-white appearance-none">
                        <option value="">Select Department</option>
                        <option value="Department 1" <?= $oldDepartment === 'Department 1' ? 'selected' : '' ?>>Department 1</option>
                        <option value="Department 2" <?= $oldDepartment === 'Department 2' ? 'selected' : '' ?>>Department 2</option>
                        <option value="Department 3" <?= $oldDepartment === 'Department 3' ? 'selected' : '' ?>>Department 3</option>
                    </select>
                    <i
                        class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none"></i>
                </div>
            </div>

            <!-- Actions -->
            <div
                class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                <a href="<?= BASE_URL ?>/admin/librarian"
                    class="w-full sm:w-auto px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 text-center font-medium">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98]">
                    <i class="fas fa-user-plus mr-2"></i>Create Librarian
                </button>
            </div>

        </form>
    </div>
</div>

<!-- JavaScript for password visibility toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const icon = this.querySelector('i');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        }
    });
</script>

<?php
// Include layout footer
require_once BASE_PATH . '/view/layout/footer.php';
?>

<style>
    /* ─── Animations (same as index) ─── */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 0.4s ease-out forwards;
    }

    .animate-slide-in {
        animation: slideIn 0.3s ease-out forwards;
    }

    .backdrop-blur-sm {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    /* ─── Dark mode scrollbar (optional) ─── */
    .dark .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
        background: #1f2937;
    }

    .dark .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #4b5563;
        border-radius: 3px;
    }

    /* ─── Custom select arrow ─── */
    select {
        background-image: none;
    }

    /* ─── Input focus glow ─── */
    input:focus,
    select:focus {
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    }

    .dark input:focus,
    .dark select:focus {
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    }
</style>