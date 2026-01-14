<?php
session_start();
include "db.php";

$error = "";
$success = "";

// Include PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Handle email verification
if(isset($_GET['verify'])){
    $verification_code = cleanInput($_GET['verify']);
    
    // Check if verification code exists
    $sql = "SELECT * FROM users WHERE verification_code = ? AND is_verified = 0";
    $result = safeQuery($conn, $sql, [$verification_code]);
    
    if($result && mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        
        // Update user to verified
        $update_sql = "UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?";
        safeQuery($conn, $update_sql, [$user['id']]);
        
        $_SESSION['success'] = "Email verified successfully! You can now login.";
        header("Location: login.php");
        exit();
    } else {
        $error = "Invalid or expired verification link.";
    }
}

if(isset($_POST['submit'])){
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = cleanInput($_POST['phone']);
    $address = cleanInput($_POST['address']);
    $role = "user";
    $verification_code = bin2hex(random_bytes(32)); // Generate verification code
    
    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Email already registered. Please use another email.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user with verification code
            $sql = "INSERT INTO users (name, email, password, phone, address, role, verification_code, is_verified) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssss", $name, $email, $hashed_password, $phone, $address, $role, $verification_code);
            
            if(mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($conn);
                
                // Send verification email
                $email_result = sendVerificationEmail($email, $name, $verification_code);
                if($email_result === true){
                    $success = "Registration Successful! Please check your email to verify your account.";
                    
                    // Log the registration
                    $log_message = "New user registered: $email (ID: $user_id) - Verification email sent";
                    error_log(date('Y-m-d H:i:s') . " - " . $log_message . PHP_EOL, 3, __DIR__ . '/logs/actions.log');
                    
                    // Redirect to verification pending page
                    header("refresh:3; url=verification_pending.php?email=" . urlencode($email));
                    exit();
                } else {
                    // If email fails, still save user but show error
                    $error = "Registration complete, but verification email failed to send. Please contact support.";
                    $error .= "<br><small>Error: " . htmlspecialchars($email_result) . "</small>";
                    
                    // Log the email error
                    $log_message = "Verification email failed for: $email - Error: " . $email_result;
                    error_log(date('Y-m-d H:i:s') . " - " . $log_message . PHP_EOL, 3, __DIR__ . '/logs/error.log');
                }
            } else {
                $error = "Registration failed. Please try again.";
                
                // Log the error
                $error_message = "Registration failed for email: $email - " . mysqli_error($conn);
                error_log(date('Y-m-d H:i:s') . " - " . $error_message . PHP_EOL, 3, __DIR__ . '/logs/error.log');
            }
        }
    }
}

/**
 * Send verification email using PHPMailer - F
 */
function sendVerificationEmail($to_email, $to_name, $verification_code){
    $mail = new PHPMailer(true);
    
    try {
        // Enable debugging
        $mail->SMTPDebug = 3; // Shows detailed connection information
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer debug level $level: $str");
        };
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'alexisdefeo01331@gmail.com';
        $mail->Password   = 'jbdsdkzmdsczwqbg'; // Your app password
        
        // FIX: Disable SSL certificate verification to fix the SSL error
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Try STARTTLS first
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Increase timeout
        $mail->Timeout = 30;
        
        // Alternative: Try SSL on port 465 if STARTTLS fails
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        // $mail->Port       = 465;
        
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
                    <h2>Welcome to Gameware, $to_name!</h2>
                    <p>Thank you for registering with Gameware. To complete your registration and start gaming, please verify your email address by clicking the button below:</p>
                    
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
                    <p>If you didn't create an account with Gameware, please ignore this email.</p>
                    
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
        
        $mail->AltBody = "Welcome to Gameware, $to_name!\n\nPlease verify your email by visiting this link: $verification_link\n\nThis link expires in 24 hours.\n\nIf you didn't create an account, please ignore this email.\n\nGameware Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Return the actual error message for debugging
        $error_msg = "PHPMailer Error: " . $mail->ErrorInfo;
        error_log($error_msg); // Also log it
        return $error_msg;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Gameware</title>
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
        }

        .navbar {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #00ff88;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 28px;
            font-weight: 900;
            color: #00ff88;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .logo a {
            color: #00ff88;
            text-decoration: none;
        }

        .hero {
            margin-top: 100px;
            padding: 60px 40px;
            text-align: center;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect width="1200" height="600" fill="none"/><g fill="%2300ff88" opacity="0.1"><circle cx="100" cy="100" r="50"/><circle cx="300" cy="200" r="30"/><circle cx="500" cy="100" r="40"/><circle cx="700" cy="300" r="60"/><circle cx="900" cy="150" r="35"/><circle cx="1100" cy="250" r="45"/></g></svg>');
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 900;
            margin-bottom: 20px;
            text-transform: uppercase;
            background: linear-gradient(45deg, #00ff88, #00cc6a, #ff416c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.8);
            max-width: 800px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        .auth-container {
            padding: 40px;
            max-width: 500px;
            margin: 120px auto 60px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 40px;
            border: 1px solid rgba(0, 255, 136, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .auth-card:hover {
            border-color: #00ff88;
            box-shadow: 0 15px 30px rgba(0, 255, 136, 0.2);
        }

        .section-title {
            font-size: 32px;
            margin-bottom: 30px;
            color: #00ff88;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #00ff88;
            font-weight: 600;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 14px 20px;
            border-radius: 10px;
            border: 2px solid rgba(0, 255, 136, 0.3);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: #00ff88;
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #00ff88;
            cursor: pointer;
            font-size: 14px;
            padding: 5px;
        }

        .auth-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            color: #0a0a0a;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .auth-button:hover {
            background: linear-gradient(45deg, #00cc6a, #00ff88);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.4);
        }

        .auth-links {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.7);
        }

        .auth-links a {
            color: #00ff88;
            text-decoration: none;
            font-weight: 600;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .auth-links a:hover {
            text-decoration: underline;
            color: #ffffff;
        }

        .error-message {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
            animation: slideIn 0.3s ease;
        }

        .success-message {
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            color: #0a0a0a;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .footer {
            background: rgba(0, 0, 0, 0.9);
            padding: 30px 40px;
            text-align: center;
            border-top: 2px solid #00ff88;
            margin-top: 60px;
        }

        .verification-info {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }

        .verification-info h3 {
            color: #00ff88;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .verification-info p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .verification-info small {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            
            .auth-container {
                padding: 20px;
                margin: 100px auto 40px;
            }
            
            .auth-card {
                padding: 30px 20px;
            }
            
            .hero h1 {
                font-size: 28px;
            }
            
            .section-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo"><a href="index.php">GAMEWARE</a></div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1>JOIN THE GAMEWARE COMMUNITY</h1>
        <p>Create an account to buy games, track orders, and join thousands of gamers in the ultimate gaming hub.</p>
    </section>

    <!-- Registration Form -->
    <section class="auth-container">
        <div class="auth-card">
            <h2 class="section-title">Create Your Account</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if(isset($_GET['verify']) && $error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form action="register.php" method="post" id="registerForm">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="form-input" 
                           placeholder="Enter your full name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-input" 
                           placeholder="Enter your email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" class="form-input" 
                               placeholder="Enter your password (min. 8 characters)" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)">
                            👁️
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <div class="password-container">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-input" 
                               placeholder="Confirm your password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)">
                            👁️
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" name="phone" id="phone" class="form-input" 
                           placeholder="Enter your phone number" required 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="address">Address</label>
                    <textarea name="address" id="address" class="form-input" 
                              placeholder="Enter your complete address" required 
                              style="min-height: 100px; resize: vertical;"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="verification-info">
                    <h3>📧 Email Verification Required</h3>
                    <p>After registration, you'll receive a verification email to activate your account.</p>
                    <p><small>Check your spam folder if you don't see the email within 5 minutes.</small></p>
                </div>
                
                <button type="submit" name="submit" class="auth-button">
                    🎮 Create Account
                </button>
                
                <div class="auth-links">
                    Already have an account? <a href="login.php">Login here</a><br>
                    <a href="index.php">← Back to Store</a>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Gameware. All rights reserved.</p>
    </footer>

    <script>
        function togglePassword(fieldId, button) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
                button.textContent = '🙈';
            } else {
                field.type = 'password';
                button.textContent = '👁️';
            }
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password.length < 8) {
                alert('Password must be at least 8 characters long');
                e.preventDefault();
                return false;
            }
            
            if (password !== confirm) {
                alert('Passwords do not match');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>