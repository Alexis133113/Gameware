<?php
session_start();
include "db.php";

$error = "";
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['success']);

// Process login form
if(isset($_POST['submit'])){
    // Security: Clean user input to prevent XSS
    $email = cleanInput($_POST['email']);
    $password = $_POST['password']; // Don't clean password before verification
    
    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
        logFailedLogin($email, "Empty fields");
    } elseif (!validateEmail($email)) {
        $error = "Invalid email format";
        logFailedLogin($email, "Invalid email format");
    } else {
        // Security: Prepared statement prevents SQL injection
        $sql = "SELECT * FROM users WHERE email = ?";
        $result = safeQuery($conn, $sql, [$email]);
        
        if($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            // FIXED: Check if password is bcrypt hashed or plain text
            $password_matched = false;
            
            // First try: Check if it's a bcrypt hash (60 characters starting with $2y$)
            if (strlen($row['password']) == 60 && strpos($row['password'], '$2y$') === 0) {
                // It's a bcrypt hash - verify with password_verify
                $password_matched = password_verify($password, $row['password']);
                
                // If password verified and needs re-hashing (if using old algorithm)
                if ($password_matched && password_needs_rehash($row['password'], PASSWORD_BCRYPT)) {
                    $new_hash = password_hash($password, PASSWORD_BCRYPT);
                    $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                    safeQuery($conn, $update_sql, [$new_hash, $row['id']]);
                }
            } 
            // Second try: Check if it's plain text password
            elseif ($row['password'] === $password) {
                $password_matched = true;
                
                // Convert plain text to bcrypt hash for security
                $new_hash = password_hash($password, PASSWORD_BCRYPT);
                $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                safeQuery($conn, $update_sql, [$new_hash, $row['id']]);
            }
            
            if($password_matched) {
                // Login successful
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = sanitizeOutput($row['name']);
                $_SESSION['user_role'] = $row['role'];
                $_SESSION['user_email'] = sanitizeOutput($row['email']);
                
                // Log successful login
                logAction($row['id'], 'login_success', "IP: " . $_SERVER['REMOTE_ADDR']);
                
                // Redirect based on role
                if($row['role'] == "admin"){
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Invalid email or password";
                logFailedLogin($email, "Wrong password");
            }
        } else {
            $error = "Invalid email or password";
            logFailedLogin($email, "Email not found");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gameware</title>
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

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: #00ff88;
            background: rgba(0, 255, 136, 0.1);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: #00ff88;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 80%;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logout-btn {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
        }

        .login-btn {
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            color: #0a0a0a;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.4);
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

        .footer p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            
            .nav-links {
                display: none;
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
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo"><a href="index.php">GAMEWARE</a></div>
        
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php?category_name=action-adventure">Action-Adventure</a></li>
            <li><a href="index.php?category_name=horror">Horror</a></li>
        </ul>
        
        <div class="user-actions">
            <?php if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                <div class="welcome-user">
                    Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
                <a href="register.php" class="logout-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1>WELCOME BACK GAMER</h1>
        <p>Login to access your game library, track orders, and continue your gaming journey with Gameware.</p>
    </section>

    <!-- Login Form -->
    <section class="auth-container">
        <div class="auth-card">
            <h2 class="section-title">Login to Your Account</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="post" id="loginForm">
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
                               placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)">
                            👁️
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="submit" class="auth-button">
                    🎮 Login to Gameware
                </button>
                
                <div class="auth-links">
                    Don't have an account? <a href="register.php">Create one now</a><br>
                    <a href="index.php">← Back to Store</a>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Gameware. All rights reserved. | The Ultimate Game Hub</p>
        <p style="margin-top: 10px; font-size: 12px;">All game titles are trademarks of their respective owners.</p>
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

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email.includes('@') || !email.includes('.')) {
                alert('Please enter a valid email address');
                e.preventDefault();
                return false;
            }
            
            if (password.length < 6) {
                alert('Password must be at least 6 characters long');
                e.preventDefault();
                return false;
            }
            
            return true;
        });

        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', function() {
                const errorDiv = document.querySelector('.error-message');
                if (errorDiv) errorDiv.style.display = 'none';
            });
        });
    </script>
</body>
</html>