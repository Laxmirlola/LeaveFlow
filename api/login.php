<?php
require_once '../config/db.php';
require_once '../config/auth.php';

startSession();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$email    = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if (!$email || !$password) {
    jsonResponse(['error' => 'Email and password are required'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['error' => 'Invalid email address'], 400);
}

$db = getDB();
$stmt = $db->prepare("SELECT id, name, email, password, role, department, avatar FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    jsonResponse(['error' => 'Invalid email or password'], 401);
}

// Set session
$_SESSION['user_id']     = $user['id'];
$_SESSION['user_name']   = $user['name'];
$_SESSION['user_role']   = $user['role'];
$_SESSION['user_dept']   = $user['department'];
$_SESSION['user_avatar'] = $user['avatar'] ?? '🧑';

jsonResponse([
    'success' => true,
    'user' => [
        'id'   => $user['id'],
        'name' => $user['name'],
        'role' => $user['role'],
        'dept' => $user['department'],
        'avatar' => $user['avatar'],
    ]
]);
