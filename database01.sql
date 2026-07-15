
USE library;


CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.2. users (role_id foreign key)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,  -- 1=admin, 2=librarian, 3=user
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) NULL,
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    department VARCHAR(50) NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    login_method VARCHAR(20) DEFAULT 'email',
    remember_token VARCHAR(100) NULL,
    email_verification_token VARCHAR(100) NULL,
    phone_verification_code VARCHAR(10) NULL,
    verification_code_expires_at TIMESTAMP NULL,
    email_verified_at TIMESTAMP NULL,
    phone_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_role (role_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.3. categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.4. books
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) NULL,
    category_id INT NOT NULL,
    description TEXT NULL,
    cover_image VARCHAR(255) NULL,
    quantity INT NOT NULL DEFAULT 1,
    available_quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrowed_at DATETIME NOT NULL,
    due_date DATETIME NOT NULL,
    returned_at DATETIME DEFAULT NULL,
    status ENUM('pending', 'awaiting_payment', 'active', 'returned', 'overdue', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_book (book_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.6. payments (refund fields + idempotency)
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'MMK',
    status ENUM('pending_approval', 'approved', 'rejected') NOT NULL DEFAULT 'pending_approval',
    payment_method ENUM('kpay', 'wavepay') NOT NULL,
    transaction_reference VARCHAR(255) NOT NULL,
    screenshot_path VARCHAR(255) DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    rejected_at TIMESTAMP NULL DEFAULT NULL,
    idempotency_key VARCHAR(100) NULL UNIQUE,                  -- ✅
    refund_status ENUM('none', 'pending', 'completed') DEFAULT 'none',  -- ✅
    refunded_at DATETIME NULL,                                 -- ✅
    refund_reason TEXT NULL,                                   -- ✅
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_loan (loan_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_idempotency (idempotency_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.7. invoices
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    payment_id INT NOT NULL,
    loan_id INT NOT NULL,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'MMK',
    payment_method VARCHAR(50) NOT NULL,
    transaction_reference VARCHAR(255) NOT NULL,
    borrowed_at DATETIME NOT NULL,
    due_date DATETIME NOT NULL,
    issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('issued', 'cancelled') DEFAULT 'issued',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    INDEX idx_payment (payment_id),
    INDEX idx_loan (loan_id),
    INDEX idx_user (user_id),
    INDEX idx_book (book_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.8. notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('admin', 'librarian', 'user') NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_role (user_id, role, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.9. settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL,
    setting_type ENUM('string', 'int', 'float', 'boolean') DEFAULT 'string',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.10. role_permissions (permission list by role)
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission VARCHAR(100) NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT IGNORE INTO roles (name, display_name, description) VALUES
('admin', 'Admin', 'Primary administrator with full system management privileges'),
('librarian', 'Librarian', 'Authorized to manage books and borrowing records'),
('user', 'User', 'Standard user with view-only permissions');
;

-- 2.5. Categories
INSERT IGNORE INTO categories (name, description) VALUES
('Programming', 'Books about programming languages, algorithms, and software development'),
('Networking', 'Books about computer networks, protocols, and cybersecurity'),
('History', 'Books about historical events, biographies, and ancient civilizations'),
('Business', 'Books about entrepreneurship, management, and finance'),
('Art', 'Books about visual arts, design, and photography'),
('Travel', 'Books about travel guides, cultures, and destinations'),
('Cooking', 'Books about recipes, culinary techniques, and food culture'),
('Health', 'Books about health, wellness, and fitness'),
('Science', 'Books about scientific topics, technology, and natural sciences'),
('Others', 'Miscellaneous books that do not fit into other categories');

-- 2.6. Sample Books
INSERT IGNORE INTO books (title, author, isbn, category_id, quantity, available_quantity) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 1, 5, 5),
('To Kill a Mockingbird', 'Harper Lee', '9780061120084', 1, 3, 3),
('A Brief History of Time', 'Stephen Hawking', '9780553380163', 9, 2, 2),
('The Art of War', 'Sun Tzu', '9781590302259', 3, 4, 4);

-- 2.7. Settings
INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, description) VALUES
('fine_per_day', '500', 'int', 'Fine amount per day for overdue books (MMK)'),
('borrowing_fee', '0', 'int', 'Fee per book borrowed (MMK)'),
('max_borrow_days', '14', 'int', 'Maximum days a book can be borrowed'),
('max_borrow_limit', '5', 'int', 'Maximum number of books a user can borrow at once'),
('grace_period_days', '3', 'int', 'Days allowed before fine applies'),
('membership_fee', '0', 'int', 'Annual membership fee (MMK)'),
('late_return_fee', '0', 'int', 'Fixed fee for late returns (MMK)');

-- 2.8. Permissions for Admin (role_id=1)
INSERT IGNORE INTO role_permissions (role_id, permission) VALUES
(1, 'view_users'), (1, 'create_users'), (1, 'edit_users'), (1, 'delete_users'),
(1, 'view_books'), (1, 'create_books'), (1, 'edit_books'), (1, 'delete_books'),
(1, 'view_loans'), (1, 'create_loans'), (1, 'edit_loans'), (1, 'delete_loans'),
(1, 'view_reports'), (1, 'export_reports'), (1, 'manage_settings'),
(1, 'view_payments'), (1, 'create_payments'), (1, 'edit_payments'),
(1, 'view_profile'), (1, 'edit_profile'),
(1, 'view_notifications'), (1, 'edit_notifications'),
(1, 'borrow_books'), (1, 'view_own_loans'),
(1, 'view_categories'), (1, 'create_categories'), (1, 'edit_categories'), (1, 'delete_categories'),
(1, 'refund_payments');   -- ✅

-- 2.9. Permissions for Librarian (role_id=2)
INSERT IGNORE INTO role_permissions (role_id, permission) VALUES
(2, 'view_users'),
(2, 'view_books'), (2, 'create_books'), (2, 'edit_books'), (2, 'delete_books'),
(2, 'view_loans'), (2, 'create_loans'), (2, 'edit_loans'), (2, 'delete_loans'),
(2, 'view_reports'), (2, 'export_reports'), (2, 'manage_settings'),
(2, 'view_payments'), (2, 'create_payments'), (2, 'edit_payments'),
(2, 'view_profile'), (2, 'edit_profile'),
(2, 'view_notifications'), (2, 'edit_notifications'),
(2, 'borrow_books'), (2, 'view_own_loans'),
(2, 'view_categories'), (2, 'create_categories'), (2, 'edit_categories'), (2, 'delete_categories'),
(2, 'refund_payments');   -- ✅

-- 2.10. Permissions for User (role_id=3)
INSERT IGNORE INTO role_permissions (role_id, permission) VALUES
(3, 'view_books'),
(3, 'borrow_books'),
(3, 'view_own_loans'),
(3, 'view_payments'),
(3, 'create_payments'),
(3, 'view_profile'),
(3, 'edit_profile'),
(3, 'view_notifications');


SELECT 'Database and tables created successfully with all required columns and permissions!' AS Message;



