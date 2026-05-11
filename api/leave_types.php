<?php
require_once '../config/db.php';
require_once '../config/auth.php';

startSession();
header('Content-Type: application/json');

if (!isLoggedIn()) { jsonResponse(['error' => 'Unauthorized'], 401); }

$db   = getDB();
$rows = $db->query("SELECT * FROM leave_types ORDER BY name")->fetch_all(MYSQLI_ASSOC);

jsonResponse(['types' => $rows]);
