<?php
declare(strict_types=1);

/**
 * send_email()
 * - Dev: logs emails (MAIL_MODE=log or unset)
 * - Prod: you’ll switch to MAIL_MODE=smtp later (we’ll add PHPMailer then)
 */
function send_email(string $to, string $subject, string $html): bool {
    $mode = getenv('MAIL_MODE') ?: 'log'; // log|smtp

    if ($mode === 'log') {
        error_log("=== DEV EMAIL ===");
        error_log("TO: " . $to);
        error_log("SUBJECT: " . $subject);
        error_log("BODY: " . $html);
        error_log("=== /DEV EMAIL ===");
        return true;
    }

    // SMTP not implemented yet (we’ll add PHPMailer when you’re ready)
    error_log("MAIL_MODE=smtp set but SMTP not implemented yet.");
    return false;
}