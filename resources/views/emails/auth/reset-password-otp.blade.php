<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width:600px;margin:auto;padding:20px;border:1px solid #ddd;border-radius:8px;background:#f9f9f9">

        <h2 style="color:#2c3e50;">Hi {{ $name }},</h2>

        <p>
            We received a request to reset your account password.
            Use the OTP below to continue:
        </p>

        <p style="
            font-size:28px;
            font-weight:bold;
            color:#e74c3c;
            text-align:center;
            letter-spacing:5px;
            margin:20px 0;
        ">
            {{ $otp }}
        </p>

        <p>
            This OTP will expire in <strong>5 minutes</strong>.
        </p>

        <p>
            If you did not request a password reset, please ignore this email.
        </p>

        <br>
        <p>
            Regards,<br>
            <strong>HCBA</strong>
        </p>
    </div>
</body>
</html>
