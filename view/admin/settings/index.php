<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_authenticated']) || $_SESSION['user_authenticated'] !== true) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
$pageTitle = 'Access Control';

$defaultPermissions = [
    'view_users', 'create_users', 'edit_users', 'delete_users',
    'view_books', 'create_books', 'edit_books', 'delete_books',
    'view_loans', 'create_loans', 'edit_loans', 'delete_loans',
    'borrow_books',
    'view_own_loans',
    'view_profile', 'edit_profile',
    'view_reports', 'export_reports', 'manage_settings',
    'view_notifications', 'create_notifications', 'edit_notifications',
    'view_payments', 'create_payments', 'edit_payments', 'delete_payments',
];

$roles = $roles ?? ['admin', 'librarian', 'user'];
$defaultRole = $defaultRole ?? 'user';
$roleCounts = $roleCounts ?? ['admin' => 1, 'librarian' => 3, 'user' => 15];
$permissions = $permissions ?? $defaultPermissions;

$rolePermissions = $rolePermissions ?? [];

foreach ($roles as $role) {
    if (!isset($rolePermissions[$role])) {
        $rolePermissions[$role] = [];
    }
    if ($role === 'admin') {
        $rolePermissions[$role] = $defaultPermissions;
    }
}

$permissionGroups = [
    '📚 Catalog' => ['view_books', 'create_books', 'edit_books', 'delete_books'],
    '🔄 Circulation' => [
        'view_loans', 'create_loans', 'edit_loans', 'delete_loans',
        'borrow_books',
        'view_own_loans'
    ],
    '👥 Users' => ['view_users', 'create_users', 'edit_users', 'delete_users'],
    '👤 Profile' => ['view_profile', 'edit_profile'],
    '📊 Reports' => ['view_reports', 'export_reports'],
    '⚙️ Settings' => ['manage_settings'],
    '💳 Payments' => [
        'view_payments',
        'create_payments',
        'edit_payments',
        'delete_payments',
    ],
    '🔔 Notifications' => [
        'view_notifications',
        'create_notifications',
        'edit_notifications',
    ],
];

foreach ($permissionGroups as $groupName => $groupPerms) {
    $filtered = array_intersect($groupPerms, $permissions);
    if (!empty($filtered)) {
        $permissionGroups[$groupName] = $filtered;
    }
}

$roleMeta = [
    'admin' => [
        'color' => 'indigo',
        'gradient' => 'from-indigo-500 to-indigo-600',
        'light' => 'indigo-50',
        'icon' => 'fa-user-shield',
        'label' => 'Administrator',
        'desc' => 'Full system access – permissions are fixed',
        'badge' => 'MASTER',
    ],
    'librarian' => [
        'color' => 'emerald',
        'gradient' => 'from-emerald-500 to-emerald-600',
        'light' => 'emerald-50',
        'icon' => 'fa-user-graduate',
        'label' => 'Librarian',
        'desc' => 'Manage catalog, circulation, and reports',
        'badge' => 'PRO',
    ],
    'user' => [
        'color' => 'blue',
        'gradient' => 'from-blue-500 to-blue-600',
        'light' => 'blue-50',
        'icon' => 'fa-user',
        'label' => 'User',
        'desc' => 'Browse books, borrow, view profile and own loans',
        'badge' => 'BASIC',
    ],
];
$defaultRoleColor = 'gray';
$defaultRoleIcon = 'fa-user-cog';
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/30 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <!-- ─── HEADER ─────────────────────────────────────────────────── -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="p-2.5 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-400 shadow-lg shadow-indigo-500/20">
                            <i class="fas fa-lock text-white text-lg"></i>
                        </span>
                        Access Control
                    </h1>
                    <p class="text-sm text-gray-700 dark:text-gray-400 mt-1 flex items-center gap-2">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        Define roles and permissions for your library team
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button onclick="openRoleModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white text-sm font-medium rounded-xl shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-user-tag"></i> New Role
                    </button>
                    <span class="text-xs text-gray-700 dark:text-gray-500 bg-white/50 dark:bg-gray-700/50 px-3 py-1.5 rounded-full border border-gray-200/50 dark:border-gray-600/50">
                        <i class="far fa-clock mr-1"></i> <?= date('M d, Y H:i') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- ─── MESSAGES ───────────────────────────────────────────────── -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300 px-5 py-3.5 rounded-xl mb-6 flex items-center justify-between backdrop-blur-sm">
                <span><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?></span>
                <button onclick="this.parentElement.style.display='none'" class="text-emerald-700 dark:text-emerald-300 hover:text-emerald-900"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 px-5 py-3.5 rounded-xl mb-6 flex items-center justify-between backdrop-blur-sm">
                <span><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?></span>
                <button onclick="this.parentElement.style.display='none'" class="text-rose-700 dark:text-rose-300 hover:text-rose-900"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- ─── ROLE CARDS ──────────────────────────────────────────── -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <?php foreach ($roles as $role): 
                $meta = $roleMeta[$role] ?? [
                    'color' => 'gray', 
                    'gradient' => 'from-gray-500 to-gray-600', 
                    'light' => 'gray-50', 
                    'icon' => 'fa-user', 
                    'label' => ucfirst($role), 
                    'desc' => '', 
                    'badge' => ''
                ];
                $color = $meta['color'];
                $icon = $meta['icon'];
                $count = $roleCounts[$role] ?? 0;
                $permCount = count($rolePermissions[$role] ?? []);
                $badge = $meta['badge'] ?? '';
            ?>
                <div class="group bg-white dark:bg-gray-800/90 rounded-2xl shadow-sm hover:shadow-2xl transition-all duration-300 border border-gray-200/60 dark:border-gray-700/60 overflow-hidden hover:-translate-y-2 flex flex-col">
                    <div class="h-1.5 bg-gradient-to-r <?= $meta['gradient'] ?>"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start gap-3">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br <?= $meta['gradient'] ?> shadow-md flex items-center justify-center text-white text-xl flex-shrink-0">
                                <i class="fas <?= $icon ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">
                                        <?= $meta['label'] ?>
                                    </h3>
                                    <?php if ($badge): ?>
                                        <span class="inline-block px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-<?= $color ?>-100 dark:bg-<?= $color ?>-900/30 text-<?= $color ?>-700 dark:text-<?= $color ?>-300 rounded-full border border-<?= $color ?>-200/50 dark:border-<?= $color ?>-800/50 whitespace-nowrap">
                                            <?= $badge ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= $color ?>-50 dark:bg-<?= $color ?>-900/20 text-<?= $color ?>-700 dark:text-<?= $color ?>-300 border border-<?= $color ?>-200/50 dark:border-<?= $color ?>-800/50 whitespace-nowrap">
                                        <i class="fas fa-key text-[10px]"></i>
                                        <?= $permCount ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-400 mt-2 leading-relaxed"><?= $meta['desc'] ?></p>
                            </div>
                        </div>
                        <div class="mt-auto pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                                    <i class="fas fa-users text-gray-500 dark:text-gray-500 text-xs"></i>
                                    <span class="font-medium"><?= $count ?></span>
                                    <span class="text-gray-500 dark:text-gray-500"><?= $count === 1 ? 'user' : 'users' ?></span>
                                </span>
                                <span class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="w-2 h-2 rounded-full bg-<?= $color ?>-500"></span>
                                    <span class="text-gray-600 dark:text-gray-500 capitalize"><?= $role ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ─── PERMISSION SETTINGS (TABS) ──────────────────────────── -->
        <div class="bg-white dark:bg-gray-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
            <form action="<?= BASE_URL ?>/admin/settings/update" method="POST">
                <div class="p-6 lg:p-8">
                    <!-- Default Role -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 pb-6 border-b border-gray-200/70 dark:border-gray-700/70">
                        <div>
                            <label class="block text-sm font-medium text-gray-800 dark:text-gray-300">
                                <i class="fas fa-user-plus mr-1 text-gray-600"></i> Default Role for New Users
                            </label>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Assign a default role when a new user registers</p>
                        </div>
                        <div class="w-full sm:w-64">
                            <select name="default_role" class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition shadow-sm text-sm text-gray-900 dark:text-white">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role ?>" <?= $role === $defaultRole ? 'selected' : '' ?>>
                                        <?= ucfirst($role) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div>
                        <div class="flex flex-wrap border-b border-gray-200 dark:border-gray-700 mb-6">
                            <?php $tabIndex = 0; ?>
                            <?php foreach ($roles as $role): 
                                $meta = $roleMeta[$role] ?? ['color' => 'gray', 'label' => ucfirst($role)];
                                $color = $meta['color'];
                                $isFirst = $tabIndex === 0;
                                $tabId = 'tab-' . $role;
                            ?>
                                <button type="button" 
                                        class="role-tab px-5 py-2.5 text-sm font-medium border-b-2 transition-colors duration-200 focus:outline-none
                                               <?= $isFirst ? 'border-' . $color . '-500 text-' . $color . '-700 dark:text-' . $color . '-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' ?>"
                                        data-tab="<?= $tabId ?>"
                                        onclick="switchTab('<?= $tabId ?>')">
                                    <i class="fas <?= $meta['icon'] ?? 'fa-user' ?> mr-1"></i>
                                    <?= $meta['label'] ?>
                                </button>
                            <?php $tabIndex++; endforeach; ?>
                        </div>

                        <!-- Tab Content -->
                        <?php foreach ($roles as $role): 
                            $meta = $roleMeta[$role] ?? ['color' => 'gray'];
                            $color = $meta['color'];
                            $tabId = 'tab-' . $role;
                            $isActive = $tabId === 'tab-admin';
                            $disabled = $role === 'admin';
                        ?>
                            <div id="<?= $tabId ?>" class="tab-content <?= $isActive ? '' : 'hidden' ?>">
                                <div class="mb-4 flex items-center gap-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-<?= $color ?>-100 text-<?= $color ?>-700 dark:bg-<?= $color ?>-900/30 dark:text-<?= $color ?>-300">
                                        <i class="fas <?= $meta['icon'] ?? 'fa-user' ?> mr-1"></i>
                                        <?= $meta['label'] ?>
                                    </span>
                                    <?php if ($disabled): ?>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">(permissions are fixed)</span>
                                    <?php endif; ?>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($permissionGroups as $groupName => $groupPermissions): ?>
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-gray-50/30 dark:bg-gray-900/20">
                                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-300 mb-3"><?= $groupName ?></h4>
                                            <div class="space-y-2">
                                                <?php foreach ($groupPermissions as $permission): 
                                                    $checked = in_array($permission, $rolePermissions[$role] ?? []);
                                                    $bgColor = $color === 'indigo' ? 'bg-indigo-500' : ($color === 'emerald' ? 'bg-emerald-500' : 'bg-blue-500');
                                                ?>
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm text-gray-700 dark:text-gray-400"><?= str_replace('_', ' ', ucfirst($permission)) ?></span>
                                                        <div class="flex items-center">
                                                            <!-- Hidden input for unchecked state -->
                                                            <input type="hidden" 
                                                                   name="permissions[<?= $role ?>][<?= $permission ?>]" 
                                                                   value="0">
                                                            <label class="relative inline-flex items-center cursor-pointer <?= $disabled ? 'opacity-60 cursor-not-allowed' : '' ?>">
                                                                <input type="checkbox"
                                                                       name="permissions[<?= $role ?>][<?= $permission ?>]"
                                                                       value="1"
                                                                       <?= $checked ? 'checked' : '' ?>
                                                                       <?= $disabled ? 'disabled' : '' ?>
                                                                       class="sr-only peer">
                                                                <div class="w-9 h-5 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:<?= $bgColor ?> peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-focus:ring-2 peer-focus:ring-<?= $color ?>-300 dark:peer-focus:ring-<?= $color ?>-800 shadow-inner"></div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ─── SAVE ──────────────────────────────────────────────── -->
                <div class="p-6 lg:p-8 border-t border-gray-200/70 dark:border-gray-700/70 bg-gray-50/70 dark:bg-gray-900/30">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="p-2.5 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl">
                                <i class="fas fa-server text-emerald-600 dark:text-emerald-400"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">System Status</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Current operational status</p>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full md:w-auto">
                            <select name="system_status" class="w-full sm:w-48 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm shadow-sm text-gray-900 dark:text-white">
                                <option value="active">🟢 Active</option>
                                <option value="maintenance">🟡 Maintenance</option>
                                <option value="offline">🔴 Offline</option>
                            </select>
                            <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white px-6 py-2.5 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 shadow-md shadow-indigo-500/20 hover:shadow-lg text-sm font-medium">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <a href="<?= BASE_URL ?>/admin/dashboard" class="w-full sm:w-auto text-center text-gray-700 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 text-sm px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ─── ROLE MODAL ────────────────────────────────────────────────────── -->
<div id="roleModal" class="fixed inset-0 z-50 hidden overflow-y-auto flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 transform transition-all border border-gray-200/50 dark:border-gray-700/50">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="roleModalTitle">Add New Role</h3>
            <button onclick="closeRoleModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="roleForm" action="<?= BASE_URL ?>/admin/roles/save" method="POST">
            <input type="hidden" name="action" value="add" id="roleFormAction">
            <input type="hidden" name="original_name" id="originalRoleName">
            <div class="mb-4">
                <label for="roleName" class="block text-sm font-medium text-gray-800 dark:text-gray-300 mb-1.5">Role Name</label>
                <input type="text" name="name" id="roleName" required
                       class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition shadow-sm text-sm text-gray-900 dark:text-white"
                       placeholder="e.g., manager">
            </div>
            <div class="mb-5">
                <label for="roleDescription" class="block text-sm font-medium text-gray-800 dark:text-gray-300 mb-1.5">Description (optional)</label>
                <textarea name="description" id="roleDescription" rows="2"
                          class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition shadow-sm text-sm text-gray-900 dark:text-white"
                          placeholder="Brief description of this role"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeRoleModal()" class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white rounded-xl transition flex items-center gap-2 shadow-md shadow-indigo-500/20 text-sm font-medium">
                    <i class="fas fa-save"></i> Save Role
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.getElementById(tabId).classList.remove('hidden');

    document.querySelectorAll('.role-tab').forEach(btn => {
        btn.classList.remove('border-indigo-500', 'text-indigo-700', 'dark:text-indigo-400',
                              'border-emerald-500', 'text-emerald-700', 'dark:text-emerald-400',
                              'border-blue-500', 'text-blue-700', 'dark:text-blue-400');
        btn.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
    });
    const activeBtn = document.querySelector(`.role-tab[data-tab="${tabId}"]`);
    if (activeBtn) {
        const color = tabId.replace('tab-', '');
        activeBtn.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
        activeBtn.classList.add(`border-${color}-500`, `text-${color}-700`, `dark:text-${color}-400`);
    }
}

function openRoleModal(roleName = null) {
    const modal = document.getElementById('roleModal');
    const title = document.getElementById('roleModalTitle');
    const form = document.getElementById('roleForm');
    const actionInput = document.getElementById('roleFormAction');
    const nameInput = document.getElementById('roleName');
    const originalNameInput = document.getElementById('originalRoleName');
    const descInput = document.getElementById('roleDescription');

    if (roleName) {
        title.innerText = 'Edit Role';
        actionInput.value = 'edit';
        nameInput.value = roleName;
        originalNameInput.value = roleName;
        descInput.value = '';
    } else {
        title.innerText = 'Add New Role';
        actionInput.value = 'add';
        nameInput.value = '';
        originalNameInput.value = '';
        descInput.value = '';
    }
    modal.classList.remove('hidden');
}

function closeRoleModal() {
    document.getElementById('roleModal').classList.add('hidden');
}

function deleteRole(role) {
    if (confirm('Are you sure you want to delete the role "' + role + '"? This action cannot be undone.')) {
        fetch('<?= BASE_URL ?>/admin/roles/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role: role })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => alert('An error occurred while deleting the role.'));
    }
}

document.addEventListener('click', function(e) {
    const modal = document.getElementById('roleModal');
    if (!modal.classList.contains('hidden') && e.target === modal) {
        closeRoleModal();
    }
});
</script>

<style>
/* -------------------------------------------------------
   Modern Glassmorphism Design
------------------------------------------------------- */

body {
    background: #f4f7fb;
}

.dark body {
    background: #0f172a;
}

/* Cards */
.rounded-2xl {
    transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
}

.rounded-2xl:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
}

/* Main Container */
.bg-white {
    backdrop-filter: blur(14px);
}

/* Permission Groups */
.border {
    transition: 0.3s;
}

.border:hover {
    border-color: #6366f1;
}

/* Tabs */
.role-tab {
    position: relative;
    transition: 0.25s;
}

.role-tab:hover {
    color: #4f46e5;
}

.role-tab::after {
    content: "";
    position: absolute;
    left: 50%;
    bottom: -2px;
    width: 0;
    height: 3px;
    border-radius: 10px;
    background: #6366f1;
    transition: 0.25s;
    transform: translateX(-50%);
}

.role-tab:hover::after {
    width: 70%;
}

/* Switch */
.peer + div {
    transition: 0.3s;
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.15);
}

.peer + div::after {
    transition: 0.3s;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
}

.peer:checked + div {
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
}

/* Buttons */
button {
    transition: 0.25s;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(79, 70, 229, 0.25);
}

/* Select */
select {
    transition: 0.25s;
}

select:hover {
    border-color: #6366f1;
}

select:focus {
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
}

/* Inputs */
input,
textarea {
    transition: 0.25s;
}

input:focus,
textarea:focus {
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
    border-color: #6366f1;
}

/* Modal */
#roleModal {
    animation: fade 0.25s;
}

#roleModal > div {
    animation: popup 0.3s;
}

@keyframes popup {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes fade {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Cards */
.shadow-lg,
.shadow-sm {
    transition: 0.3s;
}

.shadow-lg:hover,
.shadow-sm:hover {
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.12);
}

/* Status */
.bg-emerald-50,
.bg-rose-50 {
    border-width: 2px;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #eef2ff;
}

::-webkit-scrollbar-thumb {
    background: #6366f1;
    border-radius: 30px;
}

::-webkit-scrollbar-thumb:hover {
    background: #4f46e5;
}

/* Glass Effect */
.bg-white,
.dark .dark\:bg-gray-800 {
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(18px);
}

.dark .dark\:bg-gray-800 { 
    background: rgba(31, 41, 55, 0.75);
}
</style>