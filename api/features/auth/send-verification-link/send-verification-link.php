<?php
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../core/mail/send_mail.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

function createVerificationLink($userId, $email)
{
   
    global $conn;
    // Generate UID and Token using UUID v4
    $uid = Uuid::uuid4()->toString();
    $token = Uuid::uuid4()->toString();

    // Set expiry (1 hour from now)
    $expiresAt = date('Y-m-d H:i:s', time() + (60 * 60));

    // Delete old unverified tokens for this user
    $conn->query("DELETE FROM email_verifications WHERE user_id='$userId' AND verified=0");

    // Insert new verification record
    $stmt = $conn->prepare("
        INSERT INTO email_verifications (user_id, uid, token, expires_at)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $userId, $uid, $token, $expiresAt);
    $stmt->execute();

    if ($stmt->affected_rows <= 0) {
        return ['success' => false, 'error' => $stmt->error];
    }

    // Build verification link
    $verifyUrl = "http://localhost/api-structure/api/features/auth/verification/verify_link.php?uid=$uid&token=$token";

    // Email content template
    $subject = "Verify Your Email - StudyLearn";
    $body = "
        <html>
        <body style='font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px;'>
            <div style='max-width: 500px; margin: auto; background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #333;'>Email Verification</h2>
                <p>Hello,</p>
                <p>Thank you for registering with <b>StudyLearn</b>. Please click the button below to verify your email address:</p>
                <p style='text-align: center;'>
                    <a href='$verifyUrl' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;'>Verify Email</a>
                </p>
                <p style='color: #666; font-size: 14px;'>This link will expire in 1 hour.</p>
                <hr style='border: none; border-top: 1px solid #eee;' />
                <p style='font-size: 13px; color: #999;'>If you did not request this email, you can safely ignore it.</p>
                <p style='font-size: 14px;'>Best regards,<br><b>StudyLearn Team</b></p>
            </div>
        </body>
        </html>
    ";

    // Send verification email
    $mailResult = sendEmail($email, $subject, $body);

    if ($mailResult['success']) {
        return ['success' => true, 'message' => 'Verification link sent successfully'];
    } else {
        return ['success' => false, 'error' => $mailResult['error']];
    }
}
