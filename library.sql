-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2026 at 05:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `available_quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `category_id`, `description`, `cover_image`, `quantity`, `available_quantity`, `created_at`, `updated_at`) VALUES
(1, 'Python Crash Course, 3rd Edition', 'Eric Matthes', '1718502702', 1, 'Eric Matthes was a high school science, math, and programming teacher, now full-time author, living in Alaska. He has been writing programs since he was five years old and is the author of the Python Flash Cards, also from No Starch Press.', '/uploads/books/6a57034c1dd7b.jpg', 5, 3, '2026-07-15 03:49:32', '2026-07-15 11:13:19');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Programming', 'Books about programming languages, algorithms, and software development', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(2, 'Networking', 'Books about computer networks, protocols, and cybersecurity', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(3, 'History', 'Books about historical events, biographies, and ancient civilizations', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(4, 'Business', 'Books about entrepreneurship, management, and finance', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(5, 'Art', 'Books about visual arts, design, and photography', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(6, 'Travel', 'Books about travel guides, cultures, and destinations', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(7, 'Cooking', 'Books about recipes, culinary techniques, and food culture', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(8, 'Health', 'Books about health, wellness, and fitness', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(9, 'Science', 'Books about scientific topics, technology, and natural sciences', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(10, 'Others', 'Miscellaneous books that do not fit into other categories', '2026-07-14 05:01:34', '2026-07-14 05:01:34');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'MMK',
  `payment_method` varchar(50) NOT NULL,
  `transaction_reference` varchar(255) NOT NULL,
  `borrowed_at` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `issued_at` datetime DEFAULT current_timestamp(),
  `status` enum('issued','cancelled') DEFAULT 'issued',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `payment_id`, `loan_id`, `user_id`, `book_id`, `amount`, `currency`, `payment_method`, `transaction_reference`, `borrowed_at`, `due_date`, `issued_at`, `status`, `created_at`, `updated_at`) VALUES
(1, 'INV-2026-000001', 1, 1, 3, 1, 2000.00, 'MMK', 'kpay', '7297qw9er', '2026-07-15 13:13:19', '2026-07-29 13:13:19', '2026-07-15 13:13:19', 'issued', '2026-07-15 11:13:19', '2026-07-15 11:13:19');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrowed_at` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `returned_at` datetime DEFAULT NULL,
  `status` enum('pending','awaiting_payment','active','returned','overdue','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `user_id`, `book_id`, `borrowed_at`, `due_date`, `returned_at`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '2026-07-15 13:13:19', '2026-07-29 13:13:19', NULL, 'active', '2026-07-15 11:11:47', '2026-07-15 11:13:19');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','librarian','user') NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'MMK',
  `status` enum('pending_approval','approved','rejected') NOT NULL DEFAULT 'pending_approval',
  `payment_method` enum('kpay','wavepay') NOT NULL,
  `transaction_reference` varchar(255) NOT NULL,
  `screenshot_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `idempotency_key` varchar(100) DEFAULT NULL,
  `refund_status` enum('none','pending','completed') DEFAULT 'none',
  `refunded_at` datetime DEFAULT NULL,
  `refund_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `loan_id`, `user_id`, `amount`, `currency`, `status`, `payment_method`, `transaction_reference`, `screenshot_path`, `submitted_at`, `approved_at`, `rejected_at`, `idempotency_key`, `refund_status`, `refunded_at`, `refund_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 2000.00, 'MMK', 'approved', 'kpay', '7297qw9er', 'storage/payment_screenshots/6a576b1bd4d23.png', '2026-07-15 06:42:27', '2026-07-15 06:43:19', NULL, '9300b350-aab7-ca95-2187-afe89609f957', 'none', NULL, NULL, '2026-07-15 11:12:27', '2026-07-15 11:13:19');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Admin', 'Primary administrator with full system management privileges', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(2, 'librarian', 'Librarian', 'Authorized to manage books and borrowing records', '2026-07-14 05:01:34', '2026-07-14 05:01:34'),
(3, 'user', 'User', 'Standard user with view-only permissions', '2026-07-14 05:01:34', '2026-07-14 05:01:34');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission`) VALUES
(1171, 2, 'borrow_books'),
(1164, 2, 'create_books'),
(1168, 2, 'create_loans'),
(1187, 2, 'create_notifications'),
(1183, 2, 'create_payments'),
(1174, 2, 'create_users'),
(1166, 2, 'delete_books'),
(1170, 2, 'delete_loans'),
(1185, 2, 'delete_payments'),
(1176, 2, 'delete_users'),
(1165, 2, 'edit_books'),
(1169, 2, 'edit_loans'),
(1188, 2, 'edit_notifications'),
(1184, 2, 'edit_payments'),
(1178, 2, 'edit_profile'),
(1175, 2, 'edit_users'),
(1180, 2, 'export_reports'),
(1181, 2, 'manage_settings'),
(1202, 2, 'refund_payments'),
(1163, 2, 'view_books'),
(1167, 2, 'view_loans'),
(1186, 2, 'view_notifications'),
(1172, 2, 'view_own_loans'),
(1182, 2, 'view_payments'),
(1177, 2, 'view_profile'),
(1179, 2, 'view_reports'),
(1173, 2, 'view_users'),
(1194, 3, 'borrow_books'),
(1190, 3, 'create_books'),
(1200, 3, 'create_payments'),
(1192, 3, 'delete_books'),
(1191, 3, 'edit_books'),
(1197, 3, 'edit_profile'),
(1198, 3, 'manage_settings'),
(1189, 3, 'view_books'),
(1193, 3, 'view_loans'),
(1201, 3, 'view_notifications'),
(1195, 3, 'view_own_loans'),
(1199, 3, 'view_payments'),
(1196, 3, 'view_profile');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `setting_type` enum('string','int','float','boolean') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'fine_per_day', '500', 'int', 'Fine amount per day for overdue books (MMK)', '2026-07-14 05:01:35', '2026-07-14 05:01:35'),
(2, 'borrowing_fee', '2000', 'int', 'Fee per book borrowed (MMK)', '2026-07-14 05:01:35', '2026-07-15 10:35:25'),
(3, 'max_borrow_days', '7', 'int', 'Maximum days a book can be borrowed', '2026-07-14 05:01:35', '2026-07-15 04:09:31'),
(4, 'max_borrow_limit', '5', 'int', 'Maximum number of books a user can borrow at once', '2026-07-14 05:01:35', '2026-07-14 05:01:35'),
(5, 'grace_period_days', '3', 'int', 'Days allowed before fine applies', '2026-07-14 05:01:35', '2026-07-14 05:01:35'),
(6, 'membership_fee', '0', 'int', 'Annual membership fee (MMK)', '2026-07-14 05:01:35', '2026-07-14 05:01:35'),
(7, 'late_return_fee', '500', 'int', 'Fixed fee for late returns (MMK)', '2026-07-14 05:01:35', '2026-07-15 04:09:41'),
(50, 'default_role', 'user', 'string', NULL, '2026-07-15 08:55:27', '2026-07-15 08:55:27'),
(51, 'enable_refunds', '1', 'string', NULL, '2026-07-15 08:55:27', '2026-07-15 10:13:51'),
(52, 'system_status', 'active', 'string', NULL, '2026-07-15 08:55:27', '2026-07-15 08:55:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('pending','active','inactive') DEFAULT 'pending',
  `department` varchar(50) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `phone_verified` tinyint(1) DEFAULT 0,
  `login_method` varchar(20) DEFAULT 'email',
  `remember_token` varchar(100) DEFAULT NULL,
  `email_verification_token` varchar(100) DEFAULT NULL,
  `phone_verification_code` varchar(10) DEFAULT NULL,
  `verification_code_expires_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `role`, `name`, `email`, `phone`, `password_hash`, `profile_image`, `status`, `department`, `email_verified`, `phone_verified`, `login_method`, `remember_token`, `email_verification_token`, `phone_verification_code`, `verification_code_expires_at`, `email_verified_at`, `phone_verified_at`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', 'Admin', 'admin@library.com', NULL, '$2y$10$j2o0hTJzhOnF.i4tm/5AsuPJd.s0BRTryX/mEkkYUGt9tFdaycCcq', NULL, 'active', NULL, 1, 1, 'email', NULL, NULL, NULL, NULL, '2026-07-14 09:04:43', '2026-07-14 09:04:43', NULL, '2026-07-14 09:04:43', '2026-07-15 11:01:13'),
(2, 2, 'librarian', 'Librarian', 'librarian@library.com', NULL, '$2y$10$HEYsxQ9CQYAH6c5mzSvAC.yNzTfrtMdL9gdZUwlD3h8pES5aiOFyq', NULL, 'active', 'Fiction', 1, 0, 'email', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-14 09:05:37', '2026-07-15 10:18:18'),
(3, 3, 'user', 'Test', 'tzaw0991@gmail.com', NULL, '$2y$10$RuwzvfZZHqEo3ejiJ3qhlOSxA3J/xqoHPPngxxTVEtQbq9/1qFQSS', NULL, 'active', NULL, 1, 0, 'email', NULL, NULL, NULL, NULL, '2026-07-14 05:11:31', NULL, NULL, '2026-07-14 05:10:23', '2026-07-14 09:43:09');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `created_at`) VALUES
(1, 1, 1, '2026-07-14 09:05:11'),
(2, 2, 2, '2026-07-14 09:05:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_payment` (`payment_id`),
  ADD KEY `idx_loan` (`loan_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_book` (`book_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_book` (`book_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_role` (`user_id`,`role`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idempotency_key` (`idempotency_key`),
  ADD KEY `idx_loan` (`loan_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_idempotency` (`idempotency_key`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_role` (`role_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1203;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_4` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
