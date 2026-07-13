
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    status VARCHAR(20) DEFAULT 'pending',
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_email_verification_token (email_verification_token),
    INDEX idx_phone_verification_code (phone_verification_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Create categories table first
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Then create books table with foreign key
CREATE TABLE books (
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
    status ENUM('active', 'returned', 'overdue') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_book (book_id)
);


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`, `permission`),
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



INSERT INTO `role_permissions` (`role_id`, `permission`)
SELECT id, 'view_users' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'create_users' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'edit_users' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'delete_users' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'view_books' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'create_books' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'edit_books' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'delete_books' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'view_loans' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'create_loans' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'edit_loans' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'delete_loans' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'view_reports' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'export_reports' FROM `roles` WHERE `name` = 'admin' UNION ALL
SELECT id, 'manage_settings' FROM `roles` WHERE `name` = 'admin';


INSERT INTO `role_permissions` (`role_id`, `permission`)
SELECT id, 'view_users' FROM `roles` WHERE `name` = 'librarian' UNION ALL
SELECT id, 'view_books' FROM `roles` WHERE `name` = 'librarian' UNION ALL
SELECT id, 'create_books' FROM `roles` WHERE `name` = 'librarian' UNION ALL
SELECT id, 'edit_books' FROM `roles` WHERE `name` = 'librarian' UNION ALL
SELECT id, 'delete_books' FROM `roles` WHERE `name` = 'librarian' UNION ALL
SELECT id, 'view_loans' FROM `roles` WHERE `name` = 'librarian' UNION ALL
SELECT id, 'create_loans' FROM `roles` WHERE `name` = 'librarian' UNION ALL
SELECT id, 'edit_loans' FROM `roles` WHERE `name` = 'librarian' UNION ALL
SELECT id, 'delete_loans' FROM `roles` WHERE `name` = 'librarian' UNION ALL
SELECT id, 'view_reports' FROM `roles` WHERE `name` = 'librarian' UNION ALL
SELECT id, 'export_reports' FROM `roles` WHERE `name` = 'librarian';

INSERT INTO `role_permissions` (`role_id`, `permission`)
SELECT id, 'view_books' FROM `roles` WHERE `name` = 'user' UNION ALL
SELECT id, 'view_loans' FROM `roles` WHERE `name` = 'user';


INSERT IGNORE INTO `roles` (`name`, `display_name`, `description`) VALUES
('admin', 'Admin', 'Primary administrator with full system management privileges'),
('librarian', 'Librarian', 'Authorized to manage books and borrowing records'),
('user', 'User', 'Standard user with view-only permissions');

INSERT INTO `roles` (`name`, `display_name`, `description`) VALUES
('admin', 'Admin', 'Primary administrator with full system management privileges'),
('librarian', 'Librarian', 'Authorized to manage books and borrowing records'),
('user', 'User', 'Standard user with view-only permissions')
ON DUPLICATE KEY UPDATE
`display_name` = VALUES(`display_name`),
`description` = VALUES(`description`); 



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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_loan (loan_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
);



CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL,
    setting_type ENUM('string', 'int', 'float', 'boolean') DEFAULT 'string',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('fine_per_day', '500', 'int', 'Fine amount per day for overdue books (MMK)'),
('borrowing_fee', '0', 'int', 'Fee per book borrowed (MMK)'),
('max_borrow_days', '14', 'int', 'Maximum days a book can be borrowed'),
('max_borrow_limit', '5', 'int', 'Maximum number of books a user can borrow at once'),
('grace_period_days', '3', 'int', 'Days allowed before fine applies'),
('membership_fee', '0', 'int', 'Annual membership fee (MMK)'),
('late_return_fee', '0', 'int', 'Fixed fee for late returns (MMK)');


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




INSERT INTO categories (name, description, created_at, updated_at) VALUES
('Programming', 'Books about programming languages, algorithms, and software development', NOW(), NOW()),
('Networking', 'Books about computer networks, protocols, and cybersecurity', NOW(), NOW()),
('History', 'Books about historical events, biographies, and ancient civilizations', NOW(), NOW()),
('Business', 'Books about entrepreneurship, management, and finance', NOW(), NOW()),
('Art', 'Books about visual arts, design, and photography', NOW(), NOW()),
('Travel', 'Books about travel guides, cultures, and destinations', NOW(), NOW()),
('Cooking', 'Books about recipes, culinary techniques, and food culture', NOW(), NOW()),
('Healthy', 'Books about health, wellness, and fitness', NOW(), NOW()),
('Science', 'Books about scientific topics, technology, and natural sciences', NOW(), NOW()),
('Others', 'Miscellaneous books that do not fit into other categories', NOW(), NOW());