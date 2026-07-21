<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_authenticated']) || $_SESSION['user_authenticated'] !== true) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$pageTitle = 'My Profile';

// Get user data from session (updated by controller after DB save)
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';
$userPhone = $_SESSION['user_phone'] ?? '';
$userStatus = $_SESSION['user_status'] ?? 'Active';
$memberSince = $_SESSION['member_since'] ?? date('Y-m-d');
$profileImage = $_SESSION['user_profile_image'] ?? null;

// Build profile image URL
$profileImageUrl = $profileImage 
    ? BASE_URL . '/' . $profileImage 
    : BASE_URL . '/public/images/default-avatar.png';

// Include header using BASE_PATH (defined in bootstrap)
include BASE_PATH . '/view/layout/header.php';
?>

<style>
    /* ─── 1. Main container ─── */
    .profile-card {
        transition: all 0.2s ease;
    }
    .profile-card:hover {
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }

    /* ─── 2. Avatar ─── */
    .avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid #fff;
        box-shadow: 0 8px 20px rgba(59,130,246,0.3);
        background-size: cover;
        background-position: center;
        background-color: #3b82f6;
        overflow: hidden;
    }
    .dark .avatar-large {
        border-color: #1f2937;
    }
    .avatar-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .avatar-large i {
        font-size: 3rem;
        color: #fff;
    }

    /* ─── 3. Status Badge ─── */
    .status-badge {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .status-badge.active {
        background: #d1fae5;
        color: #065f46;
    }
    .dark .status-badge.active {
        background: #065f46;
        color: #d1fae5;
    }
    .status-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    .dark .status-badge.inactive {
        background: #7f1d1d;
        color: #fca5a5;
    }

    /* ─── 4. Profile Fields (one‑line label + value) ─── */
    .profile-field {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f3f4f6;
        gap: 1rem;
    }
    .dark .profile-field {
        border-bottom-color: #374151;
    }
    .profile-field-label {
        font-weight: 500;
        color: #6b7280;
        white-space: nowrap;
        flex-shrink: 0;
        min-width: 120px;
    }
    .dark .profile-field-label {
        color: #9ca3af;
    }
    .profile-field-value {
        font-weight: 600;
        color: #1f2937;
        text-align: right;
        word-break: break-word;
        flex: 1;
    }
    .dark .profile-field-value {
        color: #f3f4f6;
    }

    /* ─── 5. Buttons ─── */
    .btn-primary {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: #fff;
        padding: 0.7rem 1.5rem;
        border: none;
        border-radius: 0.8rem;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(59,130,246,0.4);
    }
    .btn-secondary {
        background: #e2e8f0;
        color: #1e293b;
        padding: 0.7rem 1.5rem;
        border: none;
        border-radius: 0.8rem;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .dark .btn-secondary {
        background: #374151;
        color: #e5e7eb;
    }
    .btn-secondary:hover {
        background: #cbd5e1;
        transform: translateY(-2px);
    }
    .dark .btn-secondary:hover {
        background: #4b5563;
    }

    /* ─── 6. Form Inputs & Labels ─── */
    .form-input {
        width: 100%;
        padding: 0.6rem 0.8rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.8rem;
        background: #f8fafc;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
        background: #fff;
    }
    .dark .form-input {
        background: #1f2937;
        border-color: #374151;
        color: #f3f4f6;
    }
    .dark .form-input:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 4px rgba(96,165,250,0.12);
        background: #111827;
    }
    .form-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    .dark .form-label {
        color: #e5e7eb;
    }

    /* ─── 7. File Input Styling ─── */
    .file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
        width: 100%;
    }
    .file-input-wrapper input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    .file-input-label {
        display: block;
        padding: 0.6rem 0.8rem;
        border: 1.5px dashed #e2e8f0;
        border-radius: 0.8rem;
        background: #f8fafc;
        text-align: center;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
    }
    .file-input-label:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .dark .file-input-label {
        background: #1f2937;
        border-color: #374151;
        color: #9ca3af;
    }
    .dark .file-input-label:hover {
        border-color: #60a5fa;
        background: #1e293b;
    }

    /* ─── 8. Edit Form Toggle ─── */
    #edit-form-container {
        transition: max-height 0.3s ease, opacity 0.25s ease;
        overflow: hidden;
        max-height: 0;
        opacity: 0;
    }
    #edit-form-container:not(.hidden) {
        max-height: 1200px;
        opacity: 1;
    }

    /* ─── 9. Responsive tweaks ─── */
    @media (max-width: 640px) {
        .profile-field {
            flex-wrap: wrap;
            gap: 0.25rem;
        }
        .profile-field-label {
            min-width: 100%;
            text-align: left;
        }
        .profile-field-value {
            text-align: left;
            width: 100%;
        }
        .btn-primary,
        .btn-secondary {
            width: 100%;
            justify-content: center;
        }
        .flex-wrap.gap-3 {
            flex-direction: column;
        }
        .edit-form-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<!-- ─── FULL PAGE WRAPPER WITH DARK MODE BACKGROUND ─── -->
<main class="min-h-screen w-full bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-25 py-16 max-w-xl">

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between shadow-sm">
                <div>
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                </div>
                <button onclick="this.parentElement.style.display='none'" class="text-green-700 dark:text-green-300 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between shadow-sm">
                <div>
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                </div>
                <button onclick="this.parentElement.style.display='none'" class="text-red-700 dark:text-red-300 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Profile Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 md:p-8 profile-card">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="avatar-large" style="background-image: url('<?= $profileImageUrl ?>');">
                        <?php if (!$profileImage): ?>
                            <i class="fas fa-user-graduate"></i>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- User Info (Read-only view) -->
                <div class="flex-1 w-full">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($userName); ?>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <?php echo htmlspecialchars($userEmail); ?>
                    </p>
                    <div class="mt-2 flex flex-wrap gap-3">
                        <span class="status-badge <?php echo $userStatus === 'active' ? 'active' : 'inactive'; ?>">
                            <?php echo ucfirst($userStatus); ?>
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-calendar-alt mr-1"></i> Member since <?php echo date('M d, Y', strtotime($memberSince)); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- ✅ Profile Details – ONE COLUMN -->
            <div class="mt-6 grid grid-cols-1 gap-4">
                <div class="profile-field">
                    <span class="profile-field-label"><i class="fas fa-user mr-2"></i>Full Name</span>
                    <span class="profile-field-value"><?php echo htmlspecialchars($userName); ?></span>
                </div>
                <div class="profile-field">
                    <span class="profile-field-label"><i class="fas fa-envelope mr-2"></i>Email</span>
                    <span class="profile-field-value break-all"><?php echo htmlspecialchars($userEmail); ?></span>
                </div>
                <div class="profile-field">
                    <span class="profile-field-label"><i class="fas fa-phone mr-2"></i>Phone</span>
                    <span class="profile-field-value">
                        <?php 
                        $phoneDisplay = trim($userPhone ?? '');
                        if (empty($phoneDisplay)) {
                            echo '+95 -- -----';
                        } else {
                            echo htmlspecialchars($phoneDisplay);
                        }
                        ?>
                    </span>
                </div>
                <div class="profile-field">
                    <span class="profile-field-label"><i class="fas fa-id-card mr-2"></i>Status</span>
                    <span class="profile-field-value">
                        <span class="status-badge <?php echo $userStatus === 'active' ? 'active' : 'inactive'; ?>">
                            <?php echo ucfirst($userStatus); ?>
                        </span>
                    </span>
                </div>
            </div>

            <!-- Edit Form (toggle visibility) -->
            <div class="mt-6">
                <button id="toggle-edit-btn" class="btn-primary inline-flex items-center gap-2">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                <a href="<?php echo BASE_URL; ?>/change-password" class="btn-secondary inline-flex items-center gap-2 ml-3">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>

            <!-- ✅ Edit Form with Profile Image Upload -->
            <div id="edit-form-container" class="mt-6 hidden">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Edit Profile</h2>
                
                <form action="<?= BASE_URL ?>/profile/update" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 edit-form-grid">
                        <div>
                            <label for="name" class="form-label">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>" class="form-input" required>
                        </div>
                        <div>
                            <label for="email" class="form-label">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" class="form-input" required>
                        </div>
                        <div>
                            <label for="phone" class="form-label">Phone<span class="text-red-500">*</span></label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userPhone); ?>" class="form-input">
                        </div>
                    </div>

                    <!-- ✅ Profile Image Upload -->
                    <div>
                        <label class="form-label">Profile Image</label>
                        <div class="file-input-wrapper">
                            <span class="file-input-label">
                                <i class="fas fa-cloud-upload-alt mr-2"></i> Choose Image (JPG, PNG, GIF, WEBP)
                            </span>
                            <input type="file" name="profile_image" accept="image/*" id="profile_image">
                        </div>
                        <?php if ($profileImage): ?>
                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                <i class="fas fa-check-circle"></i> Current image: <?php echo basename($profileImage); ?>
                            </p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <i class="fas fa-info-circle"></i> Max size: 2MB. Allowed: JPG, PNG, GIF, WEBP
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" name="update_profile" class="btn-primary inline-flex items-center gap-2">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <button type="button" id="cancel-edit-btn" class="btn-secondary inline-flex items-center gap-2">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick links back to dashboard -->
        <div class="mt-6 text-center">
            <a href="<?php echo BASE_URL; ?>/user-dashboard" class="text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

    </div>
</main>

<?php
// Include footer
include BASE_PATH . '/view/layout/footer.php';
?>

<script>
    (function() {
        'use strict';

        const toggleBtn = document.getElementById('toggle-edit-btn');
        const cancelBtn = document.getElementById('cancel-edit-btn');
        const formContainer = document.getElementById('edit-form-container');

        if (toggleBtn && formContainer) {
            toggleBtn.addEventListener('click', function() {
                const isHidden = formContainer.classList.contains('hidden');
                if (isHidden) {
                    formContainer.classList.remove('hidden');
                    toggleBtn.innerHTML = '<i class="fas fa-times"></i> Close Edit';
                } else {
                    formContainer.classList.add('hidden');
                    toggleBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
                }
            });
        }

        if (cancelBtn && formContainer) {
            cancelBtn.addEventListener('click', function() {
                formContainer.classList.add('hidden');
                toggleBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
            });
        }

        // Ensure initial state
        if (formContainer) {
            formContainer.classList.add('hidden');
        }

        // ✅ Show selected filename in file input
        const fileInput = document.getElementById('profile_image');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const label = this.closest('.file-input-wrapper').querySelector('.file-input-label');
                if (label && this.files && this.files.length > 0) {
                    label.innerHTML = '<i class="fas fa-file-image mr-2"></i> ' + this.files[0].name;
                }
            });
        }
    })();
</script>