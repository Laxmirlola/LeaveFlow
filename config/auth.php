<?php
// ============================================================
// Auth Helper
// ============================================================
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

function requireManager() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'manager') {
        header('Location: dashboard.php');
        exit;
    }
}

function currentUser() {
    startSession();
    return [
        'id'   => $_SESSION['user_id']   ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'dept' => $_SESSION['user_dept'] ?? null,
        'avatar' => $_SESSION['user_avatar'] ?? '🧑',
    ];
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
