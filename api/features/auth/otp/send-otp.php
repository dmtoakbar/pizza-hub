<?php
require_once __DIR__ . '/../../../../core/mail/send_mail.php';
require_once __DIR__ . '/../../../../config/constants/otp-email-templates.php';

function sendOtp($to = 'amitit33@gmail.com', $subject = 'verify your email from Pizza Hub', $length = 6)
{

    // Generate OTP (numeric only)
    $otp = str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

    $body = str_replace('{{OTP}}', $otp, OTP_EMAIL_TEMPLATE);

    $result = sendEmail(
        $to,
        $subject,
        $body
    );
    return $result;
}
