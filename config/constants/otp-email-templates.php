<?php
define('OTP_EMAIL_TEMPLATE', '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OTP Verification</title>
    <style>
        body {
            font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 480px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            padding: 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 1px;
        }
        .content {
            padding: 25px 30px;
            color: #374151;
            text-align: center;
        }
        .otp-box {
            display: inline-block;
            background: #f3f4f6;
            border-radius: 8px;
            padding: 15px 25px;
            margin: 20px 0;
            font-size: 28px;
            letter-spacing: 6px;
            font-weight: bold;
            color: #111827;
            border: 1px solid #e5e7eb;
        }
        .note {
            font-size: 14px;
            color: #6b7280;
            margin-top: 10px;
        }
        .footer {
            background: #f9fafb;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>StudyLearn</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>Your One-Time Password (OTP) for verification is:</p>
            <div class="otp-box">{{OTP}}</div>
            <p>This OTP is valid for <strong>5 minutes</strong>.</p>
            <p class="note">If you didn’t request this code, please ignore this email.</p>
        </div>
        <div class="footer">
            &copy; ' . date('Y') . ' StudyLearn. All rights reserved.
        </div>
    </div>
</body>
</html>
');
