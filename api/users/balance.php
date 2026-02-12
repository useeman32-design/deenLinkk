<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

function json_out(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  json_out(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
  json_out(401, ['status' => 'error', 'message' => 'Not logged in']);
}

$userId = (int)$_SESSION['user_id'];

try {
  $pdo = DB::conn();
  $stmt = $pdo->prepare("SELECT deenpoints_balance FROM users WHERE id = ? AND is_active = 1 LIMIT 1");
  $stmt->execute([$userId]);
  $balance = $stmt->fetchColumn();

  if ($balance === false) {
    json_out(404, ['status' => 'error', 'message' => 'User not found']);
  }

  json_out(200, [
    'status' => 'success',
    'deenpoints_balance' => (int)$balance
  ]);

} catch (Throwable $e) {
  error_log("balance error: " . $e->getMessage());
  json_out(500, ['status' => 'error', 'message' => 'Server error']);
}