<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../core/mail/send_mail.php';
require_once __DIR__ . '/../../../../config/constants/otp-email-templates.php';

use Ramsey\Uuid\Uuid;

function sendPasswordResetOtp()
{
    global $conn;

    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $email = trim($data['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email'];
    }

    // Find user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }

    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $otpId = Uuid::uuid4()->toString();

    // Expire old OTPs
    $stmt = $conn->prepare("
        UPDATE otp_verifications
        SET status = 'expired', verified = 0
        WHERE user_id = ?
          AND purpose = 'password_reset'
          AND status = 'pending'
    ");
    $stmt->bind_param("s", $user['id']);
    $stmt->execute();
    $stmt->close();

    // Insert OTP
    $stmt = $conn->prepare("
        INSERT INTO otp_verifications
        (id, user_id, otp, purpose, expires_at, status)
        VALUES (?, ?, ?, 'password_reset', ?, 'pending')
    ");
    $stmt->bind_param("ssss", $otpId, $user['id'], $otp, $expiresAt);
    $stmt->execute();
    $stmt->close();

    // Send email
    $body = str_replace('{{OTP}}', $otp, OTP_EMAIL_TEMPLATE);
    $mailResult = sendEmail($email, 'Reset your Pizza Hub password', $body);

    if (!$mailResult['success']) {
        return ['success' => false, 'message' => 'Failed to send OTP'];
    }

    return ['success' => true, 'message' => 'OTP sent to your email'];
}

?>
