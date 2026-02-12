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

  $stmt = $pdo->prepare("
    SELECT id, username, email, full_name, user_type,
           profile_image, bio, gender, country, deenpoints_balance,
           is_email_verified, is_active
    FROM users
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->execute([$userId]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) json_out(404, ['status' => 'error', 'message' => 'User not found']);
  if (($user['user_type'] ?? '') !== 'scholar') {
    json_out(403, ['status' => 'error', 'message' => 'Not a scholar account']);
  }

  $stmt = $pdo->prepare("
  SELECT id, user_id, display_name, title, phone, fields_of_knowledge, other_field,
  madhhab, institute, years_of_study, teachers,
  education, experience, publications, expertise_details,
  approval_status, approval_notes, reviewed_at,
  certificate_path, recommendation_path, verification_links,
  created_at, updated_at
    FROM scholars
    WHERE user_id = ?
    LIMIT 1
  ");
  $stmt->execute([$userId]);
  $scholar = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$scholar) json_out(404, ['status' => 'error', 'message' => 'Scholar profile not found']);

  json_out(200, [
    'status' => 'success',
    'user' => $user,
    'scholar' => $scholar
  ]);

} catch (Throwable $e) {
  error_log("get_scholar_me error: " . $e->getMessage());
  json_out(500, ['status' => 'error', 'message' => 'Server error']);
}