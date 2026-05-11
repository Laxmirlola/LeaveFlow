<?php
require_once '../config/db.php';
require_once '../config/auth.php';

startSession();
header('Content-Type: application/json');

if (!isLoggedIn()) { jsonResponse(['error' => 'Unauthorized'], 401); }

$user   = currentUser();
$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET: fetch leave requests ─────────────────────────────────
if ($method === 'GET') {
    if ($user['role'] === 'manager') {
        // Managers see all requests
        $stmt = $db->prepare("
            SELECT lr.*, u.name AS employee_name, u.department, u.avatar,
                   lt.name AS leave_type, lt.color,
                   m.name AS reviewed_by_name
            FROM leave_requests lr
            JOIN users u ON u.id = lr.user_id
            JOIN leave_types lt ON lt.id = lr.leave_type_id
            LEFT JOIN users m ON m.id = lr.reviewed_by
            ORDER BY lr.created_at DESC
        ");
        $stmt->execute();
    } else {
        // Employees see only their own
        $stmt = $db->prepare("
            SELECT lr.*, u.name AS employee_name, u.department, u.avatar,
                   lt.name AS leave_type, lt.color,
                   m.name AS reviewed_by_name
            FROM leave_requests lr
            JOIN users u ON u.id = lr.user_id
            JOIN leave_types lt ON lt.id = lr.leave_type_id
            LEFT JOIN users m ON m.id = lr.reviewed_by
            WHERE lr.user_id = ?
            ORDER BY lr.created_at DESC
        ");
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
    }

    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    jsonResponse(['requests' => $rows]);
}

// ── POST: submit a new leave request ─────────────────────────
if ($method === 'POST') {
    $input        = json_decode(file_get_contents('php://input'), true);
    $leave_type   = (int)($input['leave_type_id'] ?? 0);
    $start_date   = $input['start_date'] ?? '';
    $end_date     = $input['end_date']   ?? '';
    $reason       = trim($input['reason'] ?? '');

    // Validation
    if (!$leave_type || !$start_date || !$end_date) {
        jsonResponse(['error' => 'Leave type, start date and end date are required'], 400);
    }
    if (strtotime($start_date) > strtotime($end_date)) {
        jsonResponse(['error' => 'End date must be after start date'], 400);
    }
    if (strtotime($start_date) < strtotime('today')) {
        jsonResponse(['error' => 'Start date cannot be in the past'], 400);
    }

    // Calculate working days (Mon–Fri)
    $total_days = 0;
    $current = strtotime($start_date);
    $end     = strtotime($end_date);
    while ($current <= $end) {
        $dow = date('N', $current);
        if ($dow < 6) $total_days++;
        $current = strtotime('+1 day', $current);
    }
    if ($total_days === 0) {
        jsonResponse(['error' => 'Leave period contains no working days'], 400);
    }

    // Check balance
    $year = date('Y');
    $stmt = $db->prepare("
        SELECT (total_days - used_days) AS remaining
        FROM leave_balances
        WHERE user_id = ? AND leave_type_id = ? AND year = ?
    ");
    $stmt->bind_param('iii', $user['id'], $leave_type, $year);
    $stmt->execute();
    $bal = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$bal || $bal['remaining'] < $total_days) {
        jsonResponse(['error' => 'Insufficient leave balance for this request'], 400);
    }

    // Check for overlapping pending/approved requests
    $stmt = $db->prepare("
        SELECT COUNT(*) AS cnt FROM leave_requests
        WHERE user_id = ? AND status != 'rejected'
          AND NOT (end_date < ? OR start_date > ?)
    ");
    $stmt->bind_param('iss', $user['id'], $start_date, $end_date);
    $stmt->execute();
    $overlap = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($overlap['cnt'] > 0) {
        jsonResponse(['error' => 'You already have a leave request overlapping these dates'], 400);
    }

    // Insert
    $stmt = $db->prepare("
        INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, total_days, reason)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iissds', $user['id'], $leave_type, $start_date, $end_date, $total_days, $reason);
    $stmt->execute();
    $new_id = $stmt->insert_id;
    $stmt->close();

    jsonResponse(['success' => true, 'id' => $new_id, 'total_days' => $total_days]);
}

// ── PATCH: manager approves/rejects ──────────────────────────
if ($method === 'PATCH') {
    if ($user['role'] !== 'manager') {
        jsonResponse(['error' => 'Only managers can review requests'], 403);
    }

    $input      = json_decode(file_get_contents('php://input'), true);
    $request_id = (int)($input['id'] ?? 0);
    $action     = $input['action'] ?? '';  // 'approved' or 'rejected'
    $comment    = trim($input['comment'] ?? '');

    if (!$request_id || !in_array($action, ['approved', 'rejected'])) {
        jsonResponse(['error' => 'Invalid request'], 400);
    }

    // Fetch the request
    $stmt = $db->prepare("SELECT * FROM leave_requests WHERE id = ?");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$req) { jsonResponse(['error' => 'Request not found'], 404); }
    if ($req['status'] !== 'pending') {
        jsonResponse(['error' => 'Request has already been reviewed'], 400);
    }

    // Update request status
    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare("
        UPDATE leave_requests
        SET status = ?, manager_comment = ?, reviewed_by = ?, reviewed_at = ?
        WHERE id = ?
    ");
    $stmt->bind_param('ssisi', $action, $comment, $user['id'], $now, $request_id);
    $stmt->execute();
    $stmt->close();

    // Update leave balance if approved
    if ($action === 'approved') {
        $year = date('Y', strtotime($req['start_date']));
        $stmt = $db->prepare("
            UPDATE leave_balances
            SET used_days = used_days + ?
            WHERE user_id = ? AND leave_type_id = ? AND year = ?
        ");
        $stmt->bind_param('diii', $req['total_days'], $req['user_id'], $req['leave_type_id'], $year);
        $stmt->execute();
        $stmt->close();
    }

    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Method not allowed'], 405);
