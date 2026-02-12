<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

function json_out(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    json_out(200, ['status' => 'success', 'logged_in' => false, 'checked_in_today' => false]);
}

$userId = (int)$_SESSION['user_id'];

$pdo = DB::conn();
$stmt = $pdo->prepare("SELECT 1 FROM user_daily_checkins WHERE user_id = ? AND checkin_date = CURDATE() LIMIT 1");
$stmt->execute([$userId]);
$checked = (bool)$stmt->fetchColumn();

json_out(200, ['status' => 'success', 'logged_in' => true, 'checked_in_today' => $checked]);