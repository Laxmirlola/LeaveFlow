<?php
// ============================================================
// Database Configuration
// ============================================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');       // Default XAMPP username
define('DB_PASS', getenv('DB_PASS') ?: '');           // Default XAMPP password (empty)
define('DB_NAME', getenv('DB_NAME') ?: 'leave_management');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
