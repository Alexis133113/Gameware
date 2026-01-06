<?php
session_start();
include "db.php";

$error = "";
$success = "";

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

if(isset($_POST['submit'])){
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = cleanInput($_POST['phone']);
    $address = cleanInput($_POST['address']);
    $role = "user";
    
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
            
            // Insert user
            $sql = "INSERT INTO users (name, email, password, phone, address, role) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $hashed_password, $phone, $address, $role);
            
            if(mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($conn);
                $success = "Registration Successful! Redirecting to login...";
                
                // Log the registration
                $log_message = "New user registered: $email (ID: $user_id)";
                error_log(date('Y-m-d H:i:s') . " - " . $log_message . PHP_EOL, 3, __DIR__ . '/logs/actions.log');
                
                $_SESSION['success'] = "Registration successful! Please login.";
                
                header("refresh:2; url=login.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
                
                // Log the error
                $error_message = "Registration failed for email: $email - " . mysqli_error($conn);
                error_log(date('Y-m-d H:i:s') . " - " . $error_message . PHP_EOL, 3, __DIR__ . '/logs/error.log');
            }
        }
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
        /* Copy all the CSS from your login.php */
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

        .footer {
            background: rgba(0, 0, 0, 0.9);
            padding: 30px 40px;
            text-align: center;
            border-top: 2px solid #00ff88;
            margin-top: 60px;
        }

        @media (max-width: 768px) {
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
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
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