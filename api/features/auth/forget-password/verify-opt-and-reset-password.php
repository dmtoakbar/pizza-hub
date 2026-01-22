<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

function verifyOtpAndResetPassword()
{
    global $conn;

    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $email = trim($data['email'] ?? '');
    $otp = preg_replace('/\D/', '', $data['otp'] ?? '');
    $newPassword = $data['new_password'] ?? '';

    if ($email === '' || $otp === '' || $newPassword === '') {
        return ['success' => false, 'message' => 'All fields are required'];
    }

    if (strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
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

    // Start transaction
    $conn->begin_transaction();

    try {
        // Validate OTP
        $stmt = $conn->prepare("
            SELECT id
            FROM otp_verifications
            WHERE user_id = ?
              AND otp = ?
              AND purpose = 'password_reset'
              AND status = 'pending'
              AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->bind_param("ss", $user['id'], $otp);
        $stmt->execute();
        $otpRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$otpRow) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Invalid or expired OTP'];
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("ss", $hashedPassword, $user['id']);
        $stmt->execute();
        $stmt->close();

        // Expire ALL reset OTPs
        $stmt = $conn->prepare("
            UPDATE otp_verifications
            SET status = 'verified', verified = 1
            WHERE user_id = ?
              AND purpose = 'password_reset'
        ");
        $stmt->bind_param("s", $user['id']);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        return [
            'success' => true,
            'message' => 'Password reset successfully'
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Something went wrong'];
    }
}


?>