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
           deenpoints_balance, profile_image, bio,
           gender, country, is_email_verified, is_active
    FROM users
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->execute([$userId]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) json_out(404, ['status' => 'error', 'message' => 'User not found']);

  $scholar = null;
  if (($user['user_type'] ?? '') === 'scholar') {
    $st2 = $pdo->prepare("
      SELECT id, user_id, display_name, phone, fields_of_knowledge, other_field,
             madhhab, institute, years_of_study, teachers,
             approval_status, approval_notes, reviewed_at,
             certificate_path, recommendation_path, verification_links,
             created_at, updated_at
      FROM scholars
      WHERE user_id = ?
      LIMIT 1
    ");
    $st2->execute([$userId]);
    $scholar = $st2->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  json_out(200, [
    'status' => 'success',
    'user' => [
      'id' => (int)$user['id'],
      'username' => $user['username'],
      'email' => $user['email'],
      'full_name' => $user['full_name'],
      'user_type' => $user['user_type'],
      'deenpoints_balance' => (int)$user['deenpoints_balance'],
      'profile_image' => $user['profile_image'],
      'bio' => $user['bio'],
      'gender' => $user['gender'],
      'country' => $user['country'],
    ],
    'scholar' => $scholar
  ]);

} catch (Throwable $e) {
  error_log("me.php error: " . $e->getMessage());
  json_out(500, ['status' => 'error', 'message' => 'Server error']);
}