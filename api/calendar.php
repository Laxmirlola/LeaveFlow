<?php
require_once '../config/db.php';
require_once '../config/auth.php';

startSession();
header('Content-Type: application/json');

if (!isLoggedIn()) { jsonResponse(['error' => 'Unauthorized'], 401); }

$db = getDB();

$month = (int)($_GET['month'] ?? date('m'));
$year  = (int)($_GET['year']  ?? date('Y'));

$start = sprintf('%04d-%02d-01', $year, $month);
$end   = date('Y-m-t', strtotime($start));

$stmt = $db->prepare("
    SELECT lr.start_date, lr.end_date, lr.status,
           u.name AS employee_name, u.avatar,
           lt.name AS leave_type, lt.color
    FROM leave_requests lr
    JOIN users u ON u.id = lr.user_id
    JOIN leave_types lt ON lt.id = lr.leave_type_id
    WHERE lr.status = 'approved'
      AND NOT (lr.end_date < ? OR lr.start_date > ?)
    ORDER BY lr.start_date
");
$stmt->bind_param('ss', $start, $end);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

jsonResponse(['events' => $rows, 'month' => $month, 'year' => $year]);
