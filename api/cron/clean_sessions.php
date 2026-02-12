<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

try {
    $pdo = DB::conn();

    // Mark expired sessions inactive
    $stmt = $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE is_active = 1 AND expires_at <= NOW()");
    $stmt->execute();

    // Optional: hard delete really old sessions (example: older than 90 days)
    $stmt2 = $pdo->prepare("DELETE FROM user_sessions WHERE expires_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    $stmt2->execute();

    echo "OK. Deactivated: {$stmt->rowCount()}, Deleted: {$stmt2->rowCount()}";
} catch (Throwable $e) {
    error_log("cleanup_sessions error: " . $e->getMessage());
    http_response_code(500);
    echo "ERROR";
}