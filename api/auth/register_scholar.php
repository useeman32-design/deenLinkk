<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
require_once __DIR__ . '/../config/app.php';

function json_out(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

// Check if it's multipart/form-data for file uploads
$isMultipart = false;
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false) {
    $isMultipart = true;
}

if ($isMultipart) {
    // Handle multipart/form-data (file uploads)
    $full_name = trim($_POST['full_name'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $country = trim($_POST['country'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $fields_of_knowledge = $_POST['fields_of_knowledge'] ?? '[]';
    $other_field = trim($_POST['other_field'] ?? '');
    $madhhab = trim($_POST['madhhab'] ?? '');
    $institute = trim($_POST['institute'] ?? '');
    $years_of_study = (int)($_POST['years_of_study'] ?? 0);
    $teachers = trim($_POST['teachers'] ?? '');
    $verification_links = trim($_POST['verification_links'] ?? '');
    $agree_terms = isset($_POST['agree_terms']) && $_POST['agree_terms'] === '1';
    
    // Handle file uploads
    $certificate_file = $_FILES['certificate'] ?? null;
    $recommendation_file = $_FILES['recommendation'] ?? null;
    
    $fields_of_knowledge = json_decode($fields_of_knowledge, true);
    if (!is_array($fields_of_knowledge)) {
        $fields_of_knowledge = [];
    }
} else {
    // Handle JSON input (for testing)
    $input = file_get_contents('php://input') ?: '';
    $data = json_decode($input, true);
    if (!is_array($data)) {
        json_out(400, ['status' => 'error', 'message' => 'Invalid JSON data']);
    }
    
    $full_name = trim((string)($data['full_name'] ?? ''));
    $display_name = trim((string)($data['display_name'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $username = trim((string)($data['username'] ?? ''));
    $password = (string)($data['password'] ?? '');
    $confirm_password = (string)($data['confirm_password'] ?? '');
    $gender = (string)($data['gender'] ?? '');
    $country = trim((string)($data['country'] ?? ''));
    $phone = trim((string)($data['phone'] ?? ''));
    $fields_of_knowledge = (array)($data['fields_of_knowledge'] ?? []);
    $other_field = trim((string)($data['other_field'] ?? ''));
    $madhhab = trim((string)($data['madhhab'] ?? ''));
    $institute = trim((string)($data['institute'] ?? ''));
    $years_of_study = (int)($data['years_of_study'] ?? 0);
    $teachers = trim((string)($data['teachers'] ?? ''));
    $verification_links = trim((string)($data['verification_links'] ?? ''));
    $agree_terms = !empty($data['agree_terms']);
    
    $certificate_file = null;
    $recommendation_file = null;
}

// Validation
$errors = [];

// Basic info validation (same as regular registration)
if ($full_name === '' || mb_strlen($full_name) < 2 || mb_strlen($full_name) > 100) {
    $errors['full_name'] = 'Full name must be 2-100 characters';
}

if ($display_name === '' || mb_strlen($display_name) < 2 || mb_strlen($display_name) > 100) {
    $errors['display_name'] = 'Display name must be 2-100 characters';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
    $errors['email'] = 'Invalid email';
}

if ($username === '' || mb_strlen($username) < 3 || mb_strlen($username) > 20 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors['username'] = 'Username must be 3-20 chars and contain only letters, numbers, underscores';
}

$reserved = ['admin','root','system','test','user','moderator','staff','support'];
if (in_array(strtolower($username), $reserved, true)) {
    $errors['username'] = 'This username is not available';
}

if ($password === '' || strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
} else {
    $hasUpper = preg_match('/[A-Z]/', $password);
    $hasLower = preg_match('/[a-z]/', $password);
    $hasDigit = preg_match('/\d/', $password);
    $hasSpecial = preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password);
    if (!($hasUpper && $hasLower && $hasDigit && $hasSpecial)) {
        $errors['password'] = 'Password must contain uppercase, lowercase, number and special character';
    }
}

if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match';
}

if (!in_array($gender, ['male', 'female'], true)) {
    $errors['gender'] = 'Please select a valid gender';
}

if (!$agree_terms) {
    $errors['agree_terms'] = 'You must agree to the terms and conditions';
}

// Scholar-specific validation
if (empty($fields_of_knowledge)) {
    $errors['fields_of_knowledge'] = 'Please select at least one field of knowledge';
}

if ($other_field !== '' && in_array('other', $fields_of_knowledge, true)) {
    if (mb_strlen($other_field) > 100) {
        $errors['other_field'] = 'Other field must be less than 100 characters';
    }
}

if ($institute === '' || mb_strlen($institute) > 200) {
    $errors['institute'] = 'Institute must be provided and less than 200 characters';
}

if ($years_of_study < 1 || $years_of_study > 80) {
    $errors['years_of_study'] = 'Years of study must be between 1 and 80';
}

if ($phone !== '' && !preg_match('/^[+\d\s\-()]{10,20}$/', $phone)) {
    $errors['phone'] = 'Invalid phone number format';
}

// Validate at least one verification method
$hasCertificate = $isMultipart && $certificate_file && $certificate_file['error'] === UPLOAD_ERR_OK;
$hasRecommendation = $isMultipart && $recommendation_file && $recommendation_file['error'] === UPLOAD_ERR_OK;
$hasLinks = $verification_links !== '';

if (!$hasCertificate && !$hasRecommendation && !$hasLinks) {
    $errors['verification'] = 'Please provide at least one method of verification (certificate, recommendation, or links)';
}

// Validate certificate file if uploaded
if ($hasCertificate) {
    $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxFileSize = 3 * 1024 * 1024; // 3MB
    
    if (!in_array($certificate_file['type'], $allowedImageTypes)) {
        $errors['certificate'] = 'Certificate must be an image (JPG, PNG, GIF)';
    }
    
    if ($certificate_file['size'] > $maxFileSize) {
        $errors['certificate'] = 'Certificate must be less than 3MB';
    }
    
    if ($certificate_file['error'] !== UPLOAD_ERR_OK) {
        $errors['certificate'] = 'Error uploading certificate file';
    }
}

// Validate recommendation file if uploaded
if ($hasRecommendation) {
    $allowedDocTypes = [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        'application/msword', // .doc
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif' // Images
    ];
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    if (!in_array($recommendation_file['type'], $allowedDocTypes)) {
        $errors['recommendation'] = 'Recommendation must be DOCX, DOC, or image';
    }
    
    if ($recommendation_file['size'] > $maxFileSize) {
        $errors['recommendation'] = 'Recommendation must be less than 10MB';
    }
    
    if ($recommendation_file['error'] !== UPLOAD_ERR_OK) {
        $errors['recommendation'] = 'Error uploading recommendation file';
    }
}

if ($errors) {
    json_out(400, [
        'status' => 'error',
        'message' => 'Please fix the errors below',
        'errors' => $errors
    ]);
}

try {
    $pdo = DB::conn();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Check unique email/username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Email already registered');
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        throw new Exception('Username already taken');
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    if (!$password_hash) {
        throw new RuntimeException('Password hashing failed');
    }
    
    // Create user as UNVERIFIED + INACTIVE (scholar)
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, full_name, gender, country, phone, 
                          deenpoints_balance, is_email_verified, is_active, user_type, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 100, 0, 0, 'scholar', NOW())
    ");
    $stmt->execute([
        $username, 
        $email, 
        $password_hash, 
        $full_name, 
        $gender, 
        $country ?: null, 
        $phone ?: null
    ]);
    
    $user_id = (int)$pdo->lastInsertId();
    
    // Create verification token
    $rawVerifyToken  = bin2hex(random_bytes(32));
    $verifyTokenHash = hash('sha256', $rawVerifyToken);
    $verifyExpiresAt = date('Y-m-d H:i:s', time() + 24 * 60 * 60);
    
    $pdo->prepare("
        UPDATE users
        SET email_verification_token_hash = ?,
            email_verification_expires_at = ?
        WHERE id = ?
    ")->execute([$verifyTokenHash, $verifyExpiresAt, $user_id]);
    
    // Create scholar record
    $fields_json = json_encode($fields_of_knowledge);
    
    $stmt = $pdo->prepare("
        INSERT INTO scholars (user_id, display_name, phone, fields_of_knowledge, other_field, 
                            madhhab, institute, years_of_study, teachers, approval_status, 
                            verification_links, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
    ");
    $stmt->execute([
        $user_id,
        $display_name,
        $phone ?: null,
        $fields_json,
        $other_field ?: null,
        $madhhab ?: null,
        $institute,
        $years_of_study,
        $teachers ?: null,
        $verification_links ?: null
    ]);
    
    $scholar_id = (int)$pdo->lastInsertId();
    
    // Handle file uploads
    $upload_dir = __DIR__ . '/../../uploads/scholars/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Upload certificate if provided
    if ($hasCertificate) {
        $cert_ext = pathinfo($certificate_file['name'], PATHINFO_EXTENSION);
        $cert_filename = "certificate_{$scholar_id}_" . time() . "." . $cert_ext;
        $cert_path = $upload_dir . $cert_filename;
        
        if (move_uploaded_file($certificate_file['tmp_name'], $cert_path)) {
            // Store in scholar_documents table
            $stmt = $pdo->prepare("
                INSERT INTO scholar_documents (scholar_id, document_type, file_path, file_name, 
                                             file_size, mime_type, uploaded_at)
                VALUES (?, 'certificate', ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $scholar_id,
                $cert_filename,
                $certificate_file['name'],
                $certificate_file['size'],
                $certificate_file['type']
            ]);
            
            // Also store in scholars table for quick reference
            $pdo->prepare("UPDATE scholars SET certificate_path = ? WHERE id = ?")
                ->execute([$cert_filename, $scholar_id]);
        }
    }
    
    // Upload recommendation if provided
    if ($hasRecommendation) {
        $rec_ext = pathinfo($recommendation_file['name'], PATHINFO_EXTENSION);
        $rec_filename = "recommendation_{$scholar_id}_" . time() . "." . $rec_ext;
        $rec_path = $upload_dir . $rec_filename;
        
        if (move_uploaded_file($recommendation_file['tmp_name'], $rec_path)) {
            // Store in scholar_documents table
            $stmt = $pdo->prepare("
                INSERT INTO scholar_documents (scholar_id, document_type, file_path, file_name, 
                                             file_size, mime_type, uploaded_at)
                VALUES (?, 'recommendation', ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $scholar_id,
                $rec_filename,
                $recommendation_file['name'],
                $recommendation_file['size'],
                $recommendation_file['type']
            ]);
            
            // Also store in scholars table for quick reference
            $pdo->prepare("UPDATE scholars SET recommendation_path = ? WHERE id = ?")
                ->execute([$rec_filename, $scholar_id]);
        }
    }
    
    // Parse and store verification links if provided
    if ($hasLinks) {
        $links = array_filter(array_map('trim', explode("\n", $verification_links)));
        foreach ($links as $link) {
            if (!empty($link) && filter_var($link, FILTER_VALIDATE_URL)) {
                // Determine link type
                $link_type = 'other';
                if (strpos($link, 'youtube.com') !== false || strpos($link, 'youtu.be') !== false) {
                    $link_type = 'youtube';
                } elseif (strpos($link, 'facebook.com') !== false) {
                    $link_type = 'facebook';
                } elseif (strpos($link, 'instagram.com') !== false) {
                    $link_type = 'instagram';
                } elseif (strpos($link, 'twitter.com') !== false || strpos($link, 'x.com') !== false) {
                    $link_type = 'twitter';
                } elseif (strpos($link, 'tiktok.com') !== false) {
                    $link_type = 'tiktok';
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO scholar_verification_links (scholar_id, link, link_type, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$scholar_id, $link, $link_type]);
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Send verification email with scholar-specific message
    $verifyLink = app_url() . '/api/auth/verify_email.php?token=' . urlencode($rawVerifyToken);
    
    $emailBody = "Assalamu alaikum {$display_name},<br><br>
    Thank you for registering as a scholar on DeenLink!<br><br>
    
    <strong>Your scholar application is now pending for approval.</strong><br>
    Our team will review your application and get back to you soon. This process may take 2-3 business days.<br><br>
    
    <strong>Application Details:</strong><br>
    • Display Name: {$display_name}<br>
    • Username: @{$username}<br>
    • Institute: {$institute}<br>
    • Fields of Knowledge: " . implode(', ', $fields_of_knowledge) . "<br><br>
    
    <strong>Next Steps:</strong><br>
    1. Verify your email by clicking this link:<br>
    <a href='{$verifyLink}'>{$verifyLink}</a><br>
    This link expires in 24 hours.<br><br>
    
    2. Once verified, you can login to track your application status.<br>
    3. You will receive an email when your application is reviewed.<br><br>
    
    If you have any questions, please contact our support team.<br><br>
    
    JazakAllah Khair,<br>
    The DeenLink Team";
    
    send_email(
        $email,
        'Scholar Registration - Verify Your Email - DeenLink',
        $emailBody
    );
    
    // Send notification to admin about new scholar application
    $adminEmail = 'admin@deenlink.com'; // Change this to your admin email
    $adminSubject = 'New Scholar Application - ' . $display_name;
    $adminBody = "A new scholar application has been submitted:<br><br>
    
    <strong>Scholar Details:</strong><br>
    • Name: {$full_name} ({$display_name})<br>
    • Email: {$email}<br>
    • Username: @{$username}<br>
    • Institute: {$institute}<br>
    • Years of Study: {$years_of_study}<br>
    • Fields: " . implode(', ', $fields_of_knowledge) . "<br>
    • Madhhab: " . ($madhhab ?: 'Not specified') . "<br><br>
    
    <strong>Verification Provided:</strong><br>
    • Certificate: " . ($hasCertificate ? 'Yes' : 'No') . "<br>
    • Recommendation: " . ($hasRecommendation ? 'Yes' : 'No') . "<br>
    • Links: " . ($hasLinks ? 'Yes' : 'No') . "<br><br>
    
    Please review this application in the admin panel.";
    
    send_email($adminEmail, $adminSubject, $adminBody);
    
    json_out(201, [
        'status' => 'success',
        'needs_verification' => true,
        'message' => 'Scholar registration submitted successfully! Please check your email for verification.',
        'email' => $email,
        'scholar_id' => $scholar_id,
        'approval_status' => 'pending'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Scholar registration error: " . $e->getMessage());
    
    // Check for duplicate errors
    if (strpos($e->getMessage(), 'Email already registered') !== false) {
        json_out(400, [
            'status' => 'error',
            'message' => 'Email already registered',
            'errors' => ['email' => 'Email already registered']
        ]);
    }
    
    if (strpos($e->getMessage(), 'Username already taken') !== false) {
        json_out(400, [
            'status' => 'error',
            'message' => 'Username already taken',
            'errors' => ['username' => 'Username already taken']
        ]);
    }
    
    json_out(500, [
        'status' => 'error', 
        'message' => 'Registration failed. Please try again.'
    ]);
}
