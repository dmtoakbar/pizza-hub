<?php
require_once __DIR__ . '/../../config/constants/constants.php';
require_once __DIR__ . '/../../config/verify-each-request.php';

switch ($uri) {
    case 'auth/register':
        require_once __DIR__ . '/../features/auth/register/register.php';
        send_json(registerUser());
        break;

    case 'auth/login':
        require_once __DIR__ . '/../features/auth/login/login.php';
            send_json(loginUser());
        break;

    case 'auth/get-user':
        require_once __DIR__ . '/../features/auth/get-user/get_user_by_id.php';
        send_json(getUserById());
        break;

    case 'auth/send-reset-password-otp':
        require_once __DIR__ . '/../features/auth/forget-password/send_password_reset_otp.php';
        send_json(sendPasswordResetOtp());
        break;

    case 'auth/verify-reset-password-otp':
        require_once __DIR__ . '/../features/auth/forget-password/verify-opt-and-reset-password.php';
        send_json(verifyOtpAndResetPassword());
        break;

    case 'auth/update-user':
        require_once __DIR__ . '/../features/auth/update-user/update-user.php';
        send_json(updateUser());
        break;

    case 'auth/delete-user':
        require_once __DIR__ . '/../features/auth/delete/delete_user.php';
        send_json(deleteUser());
        break;

    default:
        send_json(['error' => 'Invalid route'], 404);
}
