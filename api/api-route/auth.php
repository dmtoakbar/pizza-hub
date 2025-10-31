<?php
require_once __DIR__ . '/../../config/constants/constants.php';
require_once __DIR__ . '/../../config/verify-each-request.php';

switch ($uri) {
    case 'auth/register':
        require_once __DIR__ . '/../features/auth/register/register.php';
        if ($method === 'POST') {
            send_json(registerUser());
        } else {
            send_json(['error' => 'Method'], 404);
        }
        break;

    case 'auth/otp':
        require_once __DIR__ . '/../features/auth/otp/send-otp.php';
        send_json(sendOtp());
        break;

    case 'products':
        require_once __DIR__ . '/../products.php';
        if ($method === 'GET') {
        }
        break;

    default:
        send_json(['error' => 'Invalid route'], 404);
}
