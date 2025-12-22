<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
        <h2 style="color: #2c3e50;">Hi {{ $name }},</h2>

        <p>Thank you for registering with us! Please use the following <strong>OTP</strong> to verify your email address:</p>

        <p style="font-size: 24px; font-weight: bold; color: #e74c3c; text-align: center; margin: 20px 0;">
            {{ $otp }}
        </p>

        <p>This OTP will expire in <strong>5 minutes</strong>.</p>

        <p>If you did not create an account, please ignore this email.</p>

        <br>
        <p>Regards,<br><strong>HCBA</strong></p>
    </div>
</body>
</html>
