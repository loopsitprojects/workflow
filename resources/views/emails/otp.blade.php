<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 40px 20px;">
    <div style="max-w: 500px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="text-align: center; margin-bottom: 24px;">
            <h2 style="color: #111827; margin: 0 0 8px; font-size: 24px;">Password Reset Request</h2>
            <p style="color: #6b7280; font-size: 14px; margin: 0;">Use the Verification Code below to reset your Loops password.</p>
        </div>

        <div style="background-color: #f0f7ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 24px;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #1d4ed8;">{{ $otp }}</span>
        </div>

        <p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 16px;">
            This One-Time Password (OTP) is valid for <strong>15 minutes</strong>. If you did not request a password reset, please ignore this email or contact support.
        </p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

        <p style="color: #9ca3af; font-size: 12px; text-align: center; margin: 0;">
            &copy; {{ date('Y') }} Loops Creative Management. All rights reserved.
        </p>
    </div>
</body>
</html>
