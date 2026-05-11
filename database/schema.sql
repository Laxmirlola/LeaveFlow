-- ============================================================
-- Employee Leave Management System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS leave_management;
USE leave_management;

-- Users table
CREATE TABLE IF NOT EXISTS users (
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
CREATE TABLE IF NOT EXISTS leave_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(20) DEFAULT '#6366f1'
);

-- Leave balances table
CREATE TABLE IF NOT EXISTS leave_balances (
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
CREATE TABLE IF NOT EXISTS leave_requests (
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

-- ============================================================
-- SEED DATA
-- ============================================================

-- Leave types
INSERT INTO leave_types (name, color) VALUES
('Vacation', '#6366f1'),
('Sick Leave', '#f43f5e'),
('Personal Leave', '#f59e0b'),
('Maternity/Paternity', '#10b981'),
('Unpaid Leave', '#64748b');

-- Default password for all users: "password123" (bcrypt hash)
-- Using MD5 here for simplicity - in production use bcrypt via PHP password_hash()
INSERT INTO users (name, email, password, role, department, avatar) VALUES
('Sarah Johnson', 'manager@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'Human Resources', '👩'),
('Alex Thompson', 'alex@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'Engineering', '👨'),
('Priya Patel', 'priya@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'Design', '👩'),
('Marcus Chen', 'marcus@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'Marketing', '🧑'),
('Emily Davis', 'emily@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'Finance', '👩');

-- Leave balances for current year (2025)
INSERT INTO leave_balances (user_id, leave_type_id, total_days, used_days, year) VALUES
-- Alex Thompson
(2, 1, 15, 3, 2025),
(2, 2, 10, 1, 2025),
(2, 3, 5, 0, 2025),
-- Priya Patel
(3, 1, 15, 5, 2025),
(3, 2, 10, 2, 2025),
(3, 3, 5, 1, 2025),
-- Marcus Chen
(4, 1, 15, 0, 2025),
(4, 2, 10, 3, 2025),
(4, 3, 5, 0, 2025),
-- Emily Davis
(5, 1, 15, 7, 2025),
(5, 2, 10, 0, 2025),
(5, 3, 5, 2, 2025);

-- Sample leave requests
INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, total_days, reason, status, manager_comment, reviewed_by, reviewed_at) VALUES
(2, 1, '2025-06-10', '2025-06-12', 3, 'Family vacation to Goa', 'approved', 'Enjoy your vacation!', 1, '2025-05-20 09:00:00'),
(3, 2, '2025-05-15', '2025-05-15', 1, 'Feeling unwell', 'approved', 'Get well soon!', 1, '2025-05-14 16:00:00'),
(3, 1, '2025-07-01', '2025-07-05', 5, 'Annual family trip', 'pending', NULL, NULL, NULL),
(4, 2, '2025-05-20', '2025-05-22', 3, 'Medical appointment and recovery', 'rejected', 'Insufficient notice period, please resubmit.', 1, '2025-05-19 11:00:00'),
(5, 1, '2025-08-04', '2025-08-11', 7, 'Summer holiday', 'pending', NULL, NULL, NULL);
