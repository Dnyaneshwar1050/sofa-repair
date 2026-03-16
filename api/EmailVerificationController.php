<?php
require_once __DIR__ . '/../includes/db.php';

// Mock PHPMailer for OTPs
function mockSendOtpEmail($to, $code) {
    // In a real environment, you would instantiate PHPMailer
    // $mail = new PHPMailer(true);
    // ... config SMTP
    // $mail->send();
    // For local dev, we will just log it to a text file for verification
    $logMsg = date('Y-m-d H:i:s') . " - OTP for $to: $code\n";
    file_put_contents(__DIR__ . '/../otp_mock_log.txt', $logMsg, FILE_APPEND);
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    // Action: Generate and Send OTP
    if ($action === 'send') {
        requireLogin();
        $userId = $_SESSION['user_id'];
        
        // Fetch user email
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$userId, CURRENT_TENANT_ID]);
        $user = $stmt->fetch();

        if (!$user) {
            sendJson(['error' => 'User not found'], 404);
        }

        // Generate 6 digit OTP
        $otpCode = sprintf("%06d", mt_rand(1, 999999));
        // Valid for 10 minutes
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Invalidate old OTPs
        $pdo->prepare("UPDATE otp_verifications SET is_used = TRUE WHERE user_id = ?")->execute([$userId]);

        // Insert new OTP
        $stmt = $pdo->prepare("INSERT INTO otp_verifications (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
        if ($stmt->execute([$userId, $otpCode, $expiresAt])) {
            mockSendOtpEmail($user->email, $otpCode);
            sendJson(['success' => true, 'message' => 'OTP sent successfully']);
        } else {
            sendJson(['error' => 'Failed to generate OTP'], 500);
        }
    } 
    // Action: Verify OTP
    elseif ($action === 'verify') {
        requireLogin();
        $userId = $_SESSION['user_id'];
        $code = $data['otp'] ?? '';

        if (empty($code)) {
            sendJson(['error' => 'OTP is required'], 400);
        }

        $stmt = $pdo->prepare("SELECT * FROM otp_verifications WHERE user_id = ? AND otp_code = ? AND is_used = FALSE AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId, $code]);
        $otpRecord = $stmt->fetch();

        if ($otpRecord) {
            // Mark as used
            $pdo->prepare("UPDATE otp_verifications SET is_used = TRUE WHERE id = ?")->execute([$otpRecord->id]);
            
            // Mark user email as verified
            $pdo->prepare("UPDATE users SET email_verified = TRUE WHERE id = ? AND tenant_id = ?")->execute([$userId, CURRENT_TENANT_ID]);
            
            $_SESSION['email_verified'] = true;
            
            sendJson(['success' => true, 'message' => 'Email verified successfully']);
        } else {
            sendJson(['error' => 'Invalid or expired OTP'], 400);
        }
    } else {
        sendJson(['error' => 'Invalid action'], 400);
    }
} else {
    sendJson(['error' => 'Method not allowed'], 405);
}
?>
