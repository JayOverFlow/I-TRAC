<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Nunito", sans-serif;
            font-optical-sizing: auto;
            font-weight: 500;
            font-style: normal;
            line-height: 1.6;
            color: #020202;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #630418 0%, #8C0404 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .code-container {
            background: white;
            border: 2px dashed #8C0404;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .verification-code {
            font-size: 32px;
            font-weight: bold;
            color: #630418;
            letter-spacing: 5px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('img/itrac-header-logo.png') }}" alt="I-TRAC">
        <h1>I-TRAC Email Verification</h1>
    </div>
    
    <div class="content">
        <h2>Hello!</h2>
        <p>To complete your registration, please use the verification code below:</p>
        
        <div class="code-container">
            <p><strong>Your verification code is:</strong></p>
            <div class="verification-code">{{ $code }}</div>
            <p><small>This code will expire in 15 minutes.</small></p>
        </div>
        
        <h3>Instructions:</h3>
        <ol>
            <li>Return to the registration page</li>
            <li>Enter the 6-digit code in the verification field</li>
            <li>Click "VERIFY" to complete your registration</li>
        </ol>
        
        <p><strong>Important:</strong></p>
        <ul>
            <li>Never share this code with anyone</li>
            <li>This code can only be used once</li>
            <li>If you didn't request this code, please ignore this email</li>
        </ul>
    </div>
    
    <div class="footer">
        <p><strong>I-TRAC System</strong></p>
        <p>A Digital System for Item Status Tracking and QR-Code Enabled Material Requisition Control</p>
        <p><small>© 2026 Technological University of the Philippines. All rights reserved.</small></p>
    </div>
</body>
</html>