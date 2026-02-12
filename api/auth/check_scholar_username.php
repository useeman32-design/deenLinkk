<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';

function json_out(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

$input = file_get_contents('php://input') ?: '';
$data = json_decode($input, true);
$username = trim((string)($data['username'] ?? ''));

if ($username === '') {
    json_out(400, ['available' => false, 'message' => 'Username is required']);
}

if (mb_strlen($username) < 3) json_out(200, ['available' => false, 'message' => 'Username must be at least 3 characters']);
if (mb_strlen($username) > 20) json_out(200, ['available' => false, 'message' => 'Username must be maximum 20 characters']);
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) json_out(200, ['available' => false, 'message' => 'Username can only contain letters, numbers, and underscores']);

$reserved = ['admin','root','system','test','user','moderator','staff','support'];
if (in_array(strtolower($username), $reserved, true)) {
    json_out(200, ['available' => false, 'message' => 'This username is not available']);
}

// Additional reserved names for scholars
$scholarReserved = ['sheikh', 'imam', 'mufti', 'scholar', 'ustadh', 'ustaz', 'shaykh', 'alim', 'aalim'];
if (in_array(strtolower($username), $scholarReserved, true)) {
    json_out(200, ['available' => false, 'message' => 'This username is reserved']);
}

try {
    $pdo = DB::conn();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        json_out(200, ['available' => false, 'message' => 'Username is already taken']);
    }
    json_out(200, ['available' => true, 'message' => 'Username is available']);

} catch (Throwable $e) {
    error_log("Username check error: " . $e->getMessage());
    json_out(500, ['available' => false, 'message' => 'Unable to check username. Please try again.']);
}