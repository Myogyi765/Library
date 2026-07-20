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
    .form-control:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .form-hint {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.2rem;
    }
    .dark .form-hint {
        color: #9ca3af;
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

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <form action="<?= BASE_URL ?>/admin/users/create" method="POST" class="space-y-4">
            
            <!-- Name -->
            <div class="form-group">
                <label for="name" class="form-label">
                    <i class="fas fa-user text-blue-500 mr-1"></i> Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" required
                       placeholder="Enter full name"
                       class="form-control">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope text-blue-500 mr-1"></i> Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="email" required
                       placeholder="user@example.com"
                       class="form-control">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock text-blue-500 mr-1"></i> Password <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password" id="password" required
                       placeholder="Enter password (min 6 chars)"
                       minlength="6"
                       class="form-control">
                <p class="form-hint">Minimum 6 characters</p>
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label for="phone" class="form-label">
                    <i class="fas fa-phone text-blue-500 mr-1"></i> Phone Number
                </label>
                <input type="text" name="phone" id="phone"
                       placeholder="09xxxxxxxxx"
                       class="form-control">
                <p class="form-hint">Optional</p>
            </div>

            <!-- Role -->
            <div class="form-group">
                <label for="role" class="form-label">
                    <i class="fas fa-user-tag text-blue-500 mr-1"></i> Role <span class="text-red-500">*</span>
                </label>
                <select name="role" id="role" required class="form-control">
                    <option value="user">User</option>
                    <option value="librarian">Librarian</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label for="status" class="form-label">
                    <i class="fas fa-circle text-blue-500 mr-1"></i> Status
                </label>
                <select name="status" id="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-1"></i> Create User
                </button>
                <a href="<?= BASE_URL ?>/admin/users" class="btn-secondary">
                    <i class="fas fa-times mr-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>