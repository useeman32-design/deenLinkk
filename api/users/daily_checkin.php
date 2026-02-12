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
    json_out(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

require_csrf();

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    json_out(401, ['status' => 'error', 'message' => 'Not logged in']);
}

$userId = (int)$_SESSION['user_id'];
$points = 5;

try {
    $pdo = DB::conn();
    $pdo->beginTransaction();

    // Insert check-in for today (unique constraint prevents duplicates)
    $stmt = $pdo->prepare("
        INSERT INTO user_daily_checkins (user_id, checkin_date, points_awarded, created_at)
        VALUES (?, CURDATE(), ?, NOW())
    ");
    $stmt->execute([$userId, $points]);

    // Award points
    $stmt = $pdo->prepare("
        UPDATE users
        SET deenpoints_balance = deenpoints_balance + ?
        WHERE id = ?
    ");
    $stmt->execute([$points, $userId]);

    // Return new balance
    $stmt = $pdo->prepare("SELECT deenpoints_balance FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $newBalance = (int)$stmt->fetchColumn();

    $pdo->commit();

    json_out(200, [
        'status' => 'success',
        'message' => "Daily check-in successful! {$points} DeenPoints added.",
        'points_awarded' => $points,
        'new_balance' => $newBalance,
        'checked_in_today' => true
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    // Duplicate check-in (already checked in today)
    if ((int)$e->errorInfo[1] === 1062) {
        // Already checked in today
        $stmt = DB::conn()->prepare("SELECT deenpoints_balance FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $balance = (int)$stmt->fetchColumn();

        json_out(200, [
            'status' => 'success',
            'message' => 'You already checked in today.',
            'points_awarded' => 0,
            'new_balance' => $balance,
            'checked_in_today' => true
        ]);
    }

    error_log("daily_checkin db error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("daily_checkin error: " . $e->getMessage());
    json_out(500, ['status' => 'error', 'message' => 'Server error']);
}