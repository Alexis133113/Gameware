[file name]: verification_pending.php
<?php
session_start();
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Gameware</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verification-container {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 40px;
            border: 1px solid rgba(0, 255, 136, 0.1);
            backdrop-filter: blur(10px);
            max-width: 600px;
            text-align: center;
            box-shadow: 0 15px 30px rgba(0, 255, 136, 0.2);
        }

        .verification-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        h1 {
            color: #00ff88;
            font-size: 36px;
            margin-bottom: 20px;
        }

        p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .email-highlight {
            color: #00ff88;
            font-weight: bold;
            font-size: 20px;
            background: rgba(0, 255, 136, 0.1);
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
            margin: 10px 0;
        }

        .steps {
            text-align: left;
            margin: 25px 0;
            background: rgba(0, 255, 136, 0.05);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #00ff88;
        }

        .steps ol {
            padding-left: 20px;
        }

        .steps li {
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.8);
        }

        .important-note {
            background: rgba(255, 65, 108, 0.1);
            border: 1px solid rgba(255, 65, 108, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .important-note h3 {
            color: #ff416c;
            margin-bottom: 10px;
        }

        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            color: #0a0a0a;
        }

        .btn-secondary {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.4);
        }

        .btn-secondary:hover {
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
        }

        @media (max-width: 768px) {
            .verification-container {
                padding: 20px;
            }
            
            h1 {
                font-size: 28px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-icon">📧</div>
        <h1>Verify Your Email Address</h1>
        
        <p>A verification email has been sent to:</p>
        <div class="email-highlight"><?php echo $email; ?></div>
        
        <div class="steps">
            <h3>Follow these steps:</h3>
            <ol>
                <li>Check your email inbox for a message from Gameware</li>
                <li>Click the verification link in the email</li>
                <li>You'll be redirected to login automatically</li>
            </ol>
        </div>
        
        <div class="important-note">
            <h3>⚠️ Important:</h3>
            <p>You must verify your email before you can login to your account.</p>
            <p>The verification link expires in 24 hours.</p>
            <p>If you don't see the email, please check your spam folder.</p>
        </div>
        
        <div class="action-buttons">
            <a href="login.php" class="btn btn-primary">Go to Login</a>
            <a href="register.php" class="btn btn-secondary">Register Another Account</a>
        </div>
        
        <p style="margin-top: 30px; font-size: 14px; color: rgba(255, 255, 255, 0.6);">
            Didn't receive the email? Wait a few minutes and check your spam folder.
            <br>Still not working? <a href="resend_verification.php?email=<?php echo urlencode($email); ?>" style="color: #00ff88;">Resend verification email</a>
        </p>
    </div>
</body>
</html>