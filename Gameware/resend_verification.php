[file name]: resend_verification.php
<?php
session_start();
include "db.php";

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = "";
$success = "";

if(isset($_GET['email'])){
    $email = cleanInput($_GET['email']);
    
    // Check if user exists and is not verified
    $sql = "SELECT * FROM users WHERE email = ? AND is_verified = 0";
    $result = safeQuery($conn, $sql, [$email]);
    
    if($result && mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        
        // Generate new verification code
        $new_verification_code = bin2hex(random_bytes(32));
        
        // Update verification code
        $update_sql = "UPDATE users SET verification_code = ? WHERE id = ?";
        if(safeQuery($conn, $update_sql, [$new_verification_code, $user['id']])){
            // Send new verification email
            if(sendVerificationEmail($email, $user['name'], $new_verification_code)){
                $success = "Verification email resent successfully!";
                $_SESSION['success'] = $success;
                header("Location: verification_pending.php?email=" . urlencode($email));
                exit();
            } else {
                $error = "Failed to send verification email. Please try again.";
            }
        } else {
            $error = "Failed to update verification code.";
        }
    } else {
        $error = "User not found or already verified.";
    }
}

/**
 * Send verification email (same function as in register.php)
 */
function sendVerificationEmail($to_email, $to_name, $verification_code){
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'alexisdefeo01331@gmail.com';
        $mail->Password   = 'jbdsdkzmdsczwqbg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('alexisdefeo01331@gmail.com', 'Gameware');
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Gameware Account';
        
        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/register.php?verify=" . $verification_code;
        
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(45deg, #00ff88, #00cc6a); padding: 20px; text-align: center; }
                .content { background: #f4f4f4; padding: 30px; }
                .button { background: linear-gradient(45deg, #00ff88, #00cc6a); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='color: white; margin: 0;'>🎮 Gameware</h1>
                </div>
                <div class='content'>
                    <h2>Verify Your Gameware Account</h2>
                    <p>You requested a new verification email. To complete your registration, please verify your email address by clicking the button below:</p>
                    
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='$verification_link' class='button' style='color: white; text-decoration: none; font-weight: bold;'>
                            VERIFY MY EMAIL
                        </a>
                    </p>
                    
                    <p>Or copy and paste this link in your browser:</p>
                    <p style='background: #eee; padding: 10px; border-radius: 5px; word-break: break-all;'>
                        $verification_link
                    </p>
                    
                    <p>This verification link will expire in 24 hours.</p>
                    <p>If you didn't request this email, please ignore it.</p>
                    
                    <p>Happy Gaming,<br>The Gameware Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Gameware. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Verify your Gameware account by visiting this link: $verification_link\n\nThis link expires in 24 hours.\n\nIf you didn't request this, please ignore this email.\n\nGameware Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification - Gameware</title>
    <style>
        /* Simple styling for error/success messages */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            text-align: center;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <?php if($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if($success): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>
    <p><a href="verification_pending.php">Go Back</a></p>
</body>
</html>