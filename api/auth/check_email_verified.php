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
$email = trim((string)($data['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(400, ['status' => 'error', 'message' => 'Valid email is required']);
}

try {
    $pdo = DB::conn();
    $stmt = $pdo->prepare("SELECT is_email_verified FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $verified = $stmt->fetchColumn();

    // Do not reveal existence; treat missing as not verified
    if ($verified === false) {
        json_out(200, ['status' => 'success', 'verified' => false]);
    }

    json_out(200, ['status' => 'success', 'verified' => ((int)$verified === 1)]);
} catch (Throwable $e) {
    error_log("check_email_verified error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}