<?php
require_once '../config/db.php';
require_once '../config/auth.php';

startSession();
header('Content-Type: application/json');

if (!isLoggedIn()) { jsonResponse(['error' => 'Unauthorized'], 401); }

$user = currentUser();
$db   = getDB();
$year = date('Y');

$stmt = $db->prepare("
    SELECT lb.*, lt.name AS leave_type, lt.color,
           (lb.total_days - lb.used_days) AS remaining_days
    FROM leave_balances lb
    JOIN leave_types lt ON lt.id = lb.leave_type_id
    WHERE lb.user_id = ? AND lb.year = ?
");
$stmt->bind_param('ii', $user['id'], $year);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

jsonResponse(['balances' => $rows]);
