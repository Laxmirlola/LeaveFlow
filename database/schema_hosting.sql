-- ============================================================
-- Employee Leave Management System - Hosting Import Schema
-- Import this into your existing hosting database.
-- Do not run this on a database that contains data you need to keep.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS leave_requests;
DROP TABLE IF EXISTS leave_balances;
DROP TABLE IF EXISTS leave_types;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('employee', 'manager') NOT NULL DEFAULT 'employee',
    department VARCHAR(100),
    avatar VARCHAR(10) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Leave types table
CREATE TABLE leave_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(20) DEFAULT '#6366f1'
);

-- Leave balances table
CREATE TABLE leave_balances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    total_days DECIMAL(5,1) NOT NULL DEFAULT 0,
    used_days DECIMAL(5,1) NOT NULL DEFAULT 0,
    year YEAR NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
    UNIQUE KEY unique_balance (user_id, leave_type_id, year)
);

-- Leave requests table
CREATE TABLE leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days DECIMAL(5,1) NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    manager_comment TEXT,
    reviewed_by INT DEFAULT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

-- Leave types
INSERT INTO leave_types (name, color) VALUES
('Vacation', '#6366f1'),
('Sick Leave', '#f43f5e'),
('Personal Leave', '#f59e0b'),
('Maternity/Paternity', '#10b981'),
('Unpaid Leave', '#64748b');

-- Default password for all users: "password123" (bcrypt hash)
INSERT INTO users (name, email, password, role, department, avatar) VALUES
('Sarah Johnson', 'manager@company.com', '$2y$10$zhIpjQPllmm9kNDuTgGUmOe4O25wl1El8iAlcCItPMCY7gk6wxXrK', 'manager', 'Human Resources', NULL),
('Alex Thompson', 'alex@company.com', '$2y$10$zhIpjQPllmm9kNDuTgGUmOe4O25wl1El8iAlcCItPMCY7gk6wxXrK', 'employee', 'Engineering', NULL),
('Priya Patel', 'priya@company.com', '$2y$10$zhIpjQPllmm9kNDuTgGUmOe4O25wl1El8iAlcCItPMCY7gk6wxXrK', 'employee', 'Design', NULL),
('Marcus Chen', 'marcus@company.com', '$2y$10$zhIpjQPllmm9kNDuTgGUmOe4O25wl1El8iAlcCItPMCY7gk6wxXrK', 'employee', 'Marketing', NULL),
('Emily Davis', 'emily@company.com', '$2y$10$zhIpjQPllmm9kNDuTgGUmOe4O25wl1El8iAlcCItPMCY7gk6wxXrK', 'employee', 'Finance', NULL);

-- Leave balances for current year (2026)
INSERT INTO leave_balances (user_id, leave_type_id, total_days, used_days, year) VALUES
(2, 1, 15, 3, 2026),
(2, 2, 10, 1, 2026),
(2, 3, 5, 0, 2026),
(3, 1, 15, 5, 2026),
(3, 2, 10, 2, 2026),
(3, 3, 5, 1, 2026),
(4, 1, 15, 0, 2026),
(4, 2, 10, 3, 2026),
(4, 3, 5, 0, 2026),
(5, 1, 15, 7, 2026),
(5, 2, 10, 0, 2026),
(5, 3, 5, 2, 2026);

-- Sample leave requests
INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, total_days, reason, status, manager_comment, reviewed_by, reviewed_at) VALUES
(2, 1, '2026-06-10', '2026-06-12', 3, 'Family vacation to Goa', 'approved', 'Enjoy your vacation!', 1, '2026-05-20 09:00:00'),
(3, 2, '2026-05-15', '2026-05-15', 1, 'Feeling unwell', 'approved', 'Get well soon!', 1, '2026-05-14 16:00:00'),
(3, 1, '2026-07-01', '2026-07-07', 5, 'Annual family trip', 'pending', NULL, NULL, NULL),
(4, 2, '2026-05-20', '2026-05-22', 3, 'Medical appointment and recovery', 'rejected', 'Insufficient notice period, please resubmit.', 1, '2026-05-19 11:00:00'),
(5, 1, '2026-08-03', '2026-08-11', 7, 'Summer holiday', 'pending', NULL, NULL, NULL);
