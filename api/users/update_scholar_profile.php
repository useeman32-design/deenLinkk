<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/csrf.php';

function json_out(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_out(405, ['status'=>'error','message'=>'Method not allowed']);
}

require_csrf();

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
  json_out(401, ['status'=>'error','message'=>'Not logged in']);
}

$userId = (int)$_SESSION['user_id'];

$input = file_get_contents('php://input') ?: '';
$data = json_decode($input, true);
if (!is_array($data)) json_out(400, ['status'=>'error','message'=>'Invalid JSON']);

$title = trim((string)($data['title'] ?? ''));
$education = trim((string)($data['education'] ?? ''));
$experience = trim((string)($data['experience'] ?? ''));
$publications = trim((string)($data['publications'] ?? ''));
$expertise_details = trim((string)($data['expertise_details'] ?? ''));

try {
  $pdo = DB::conn();

  // Ensure scholar account
  $st = $pdo->prepare("SELECT user_type FROM users WHERE id = ? LIMIT 1");
  $st->execute([$userId]);
  if ((string)$st->fetchColumn() !== 'scholar') {
    json_out(403, ['status'=>'error','message'=>'Forbidden']);
  }

  $st = $pdo->prepare("
    UPDATE scholars
    SET title = ?, education = ?, experience = ?, publications = ?, expertise_details = ?
    WHERE user_id = ?
    LIMIT 1
  ");
  $st->execute([
    $title ?: null,
    $education ?: null,
    $experience ?: null,
    $publications ?: null,
    $expertise_details ?: null,
    $userId
  ]);

  json_out(200, ['status'=>'success','message'=>'Scholar profile updated']);

} catch (Throwable $e) {
  error_log("update_scholar_profile error: " . $e->getMessage());
  json_out(500, ['status'=>'error','message'=>'Server error']);
}