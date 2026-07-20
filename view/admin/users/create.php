<?php
// This is a partial view – it will be rendered inside the admin-dashboard layout
// No header/footer here.
?>

<style>
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.4rem;
    }
    .dark .form-label {
        color: #d1d5db;
    }
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
    .dark .form-control {
        background: #1e293b;
        border-color: #374151;
        color: #e5e7eb;
    }
    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .form-hint {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.2rem;
    }
    .dark .form-hint {
        color: #9ca3af;
    }
    .form-required {
        color: #ef4444;
        font-weight: 700;
    }
    .form-optional {
        color: #6b7280;
        font-size: 0.75rem;
        font-weight: 400;
    }
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
    .btn-primary:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }
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
    .dark .btn-secondary {
        background: #374151;
        color: #d1d5db;
    }
    .btn-secondary:hover {
        background: #d1d5db;
    }
    .dark .btn-secondary:hover {
        background: #4b5563;
    }

    /* ===== Tab Bar ===== */
    .tab-bar {
        display: flex;
        gap: 0.25rem;
        background: #f1f5f9;
        padding: 0.25rem;
        border-radius: 0.75rem;
        margin-bottom: 0.5rem;
    }
    .dark .tab-bar {
        background: #1e293b;
    }
    .tab-btn {
        flex: 1;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
        background: transparent;
        color: #475569;
    }
    .dark .tab-btn {
        color: #94a3b8;
    }
    .tab-btn:hover {
        background: rgba(255,255,255,0.5);
    }
    .tab-btn.active {
        background: #ffffff;
        color: #1e293b;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .dark .tab-btn.active {
        background: #334155;
        color: #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .tab-btn i {
        margin-right: 0.4rem;
    }

    /* ===== Tab Content ===== */
    .tab-content {
        display: none;
        animation: fadeIn 0.25s ease;
    }
    .tab-content.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .requirement-hint {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        color: #166534;
        margin-bottom: 1rem;
    }
    .dark .requirement-hint {
        background: #052e16;
        border-color: #166534;
        color: #86efac;
    }

    .invalid-feedback {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: none;
    }
    .invalid-feedback.show {
        display: block;
    }
    .form-control.is-invalid {
        border-color: #ef4444;
    }
    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }
</style>

<div class="flex flex-col">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-user-plus text-blue-600 dark:text-blue-400 mr-2"></i>Create New User
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add a new user to the library system</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/users" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to Users
        </a>
    </div>

    <!-- Hint -->
    <div class="requirement-hint">
        <i class="fas fa-info-circle mr-1"></i>
        Choose <strong>Email</strong> or <strong>Phone</strong> as the primary login method. The selected field will be required.
        You may fill both if you wish.
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <form action="<?= BASE_URL ?>/admin/users/create" method="POST" class="space-y-4" id="userForm" novalidate>

            <!-- Name -->
            <div class="form-group">
                <label for="name" class="form-label">
                    <i class="fas fa-user text-blue-500 mr-1"></i> Full Name <span class="form-required">*</span>
                </label>
                <input type="text" name="name" id="name" required
                       placeholder="Enter full name"
                       class="form-control"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <!-- ===== Tab Bar ===== -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-sign-in-alt text-blue-500 mr-1"></i> Login Method <span class="form-required">*</span>
                </label>
                <div class="tab-bar" role="tablist">
                    <button type="button" class="tab-btn active" data-tab="email" role="tab">
                        <i class="fas fa-envelope"></i> Email
                    </button>
                    <button type="button" class="tab-btn" data-tab="phone" role="tab">
                        <i class="fas fa-phone"></i> Phone
                    </button>
                </div>

                <!-- Email Tab -->
                <div id="tab-email" class="tab-content active" role="tabpanel">
                    <label for="email" class="form-label">
                        Email Address <span class="form-required">*</span>
                    </label>
                    <input type="email" name="email" id="email"
                           placeholder="user@example.com"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <div class="invalid-feedback" id="emailFeedback">Email is required when selected.</div>
                </div>

                <!-- Phone Tab -->
                <div id="tab-phone" class="tab-content" role="tabpanel">
                    <label for="phone" class="form-label">
                        Phone Number <span class="form-required">*</span>
                    </label>
                    <input type="text" name="phone" id="phone"
                           placeholder="09xxxxxxxxx"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    <div class="invalid-feedback" id="phoneFeedback">Phone is required when selected.</div>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock text-blue-500 mr-1"></i> Password <span class="form-required">*</span>
                </label>
                <input type="password" name="password" id="password" required
                       placeholder="Enter password (min 6 chars)"
                       minlength="6"
                       class="form-control">
                <p class="form-hint">Minimum 6 characters</p>
            </div>

            <!-- Role -->
            <div class="form-group">
                <label for="role" class="form-label">
                    <i class="fas fa-user-tag text-blue-500 mr-1"></i> Role <span class="form-required">*</span>
                </label>
                <select name="role" id="role" required class="form-control">
                    <option value="user" selected>User</option>
                </select>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label for="status" class="form-label">
                    <i class="fas fa-circle text-blue-500 mr-1"></i> Status
                </label>
                <select name="status" id="status" class="form-control">
                    <option value="active" <?= (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="pending" <?= (isset($_POST['status']) && $_POST['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="btn-primary" id="submitBtn">
                    <i class="fas fa-save mr-1"></i> Create User
                </button>
                <a href="<?= BASE_URL ?>/admin/users" class="btn-secondary">
                    <i class="fas fa-times mr-1"></i> Cancel
                </a>
            </div>

            <!-- Hidden field to store selected login method -->
            <input type="hidden" name="login_method" id="loginMethod" value="email">
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userForm');
    const tabs = document.querySelectorAll('.tab-btn');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const emailFeedback = document.getElementById('emailFeedback');
    const phoneFeedback = document.getElementById('phoneFeedback');
    const loginMethodInput = document.getElementById('loginMethod');

    // ---- Tab Switching ----
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Show selected tab content
            const tabId = this.dataset.tab;
            document.getElementById('tab-' + tabId).classList.add('active');

            // Update hidden login_method
            loginMethodInput.value = tabId;

            // Clear validation states
            emailInput.classList.remove('is-invalid');
            phoneInput.classList.remove('is-invalid');
            emailFeedback.classList.remove('show');
            phoneFeedback.classList.remove('show');
        });
    });

    // ---- Real-time validation ----
    function validateTab() {
        const activeTab = document.querySelector('.tab-btn.active');
        const tabId = activeTab ? activeTab.dataset.tab : 'email';
        const isEmail = tabId === 'email';
        const value = isEmail ? emailInput.value.trim() : phoneInput.value.trim();

        if (value === '') {
            if (isEmail) {
                emailInput.classList.add('is-invalid');
                emailFeedback.classList.add('show');
            } else {
                phoneInput.classList.add('is-invalid');
                phoneFeedback.classList.add('show');
            }
            return false;
        } else {
            if (isEmail) {
                emailInput.classList.remove('is-invalid');
                emailFeedback.classList.remove('show');
            } else {
                phoneInput.classList.remove('is-invalid');
                phoneFeedback.classList.remove('show');
            }
            return true;
        }
    }

    emailInput.addEventListener('input', validateTab);
    phoneInput.addEventListener('input', validateTab);

    // ---- Form Submit ----
    form.addEventListener('submit', function(e) {
        if (!validateTab()) {
            e.preventDefault();
            const activeTab = document.querySelector('.tab-btn.active');
            const label = activeTab ? activeTab.dataset.tab : 'email';
            alert('Please enter a valid ' + (label === 'email' ? 'email address' : 'phone number') + '.');
        }
    });

    // Initial validation state (email tab active by default)
    validateTab();
});
</script>