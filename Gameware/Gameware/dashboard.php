<?php
session_start();
if(isset($_SESSION['user_id']))  {
    if($_SESSION['user_role'] == "user"){
        // User is authorized
    }
    else{
      header("Location: admin/dashboard.php");
    }
}else{
     header("Location: ../index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Gameware</title>

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
        }

        /* Sidebar UI */
        .dashboard_sidebar {  
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            width: 240px;
            height: 100%;
            border-right: 2px solid #00ff88;
            padding-top: 30px;
            z-index: 1000;
        }

        .sidebar_logo {
            font-size: 24px;
            font-weight: 900;
            color: #00ff88;
            text-align: center;
            margin-bottom: 40px;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }

        .dashboard_sidebar ul {
            padding: 0 15px;
        }

        .dashboard_sidebar ul li {
            list-style: none;
            margin-bottom: 10px;
        }

        .dashboard_sidebar ul li a {
            display: block;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .dashboard_sidebar ul li a:hover {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
            transform: translateX(5px);
            box-shadow: inset 4px 0 0 #00ff88;
        }

        /* Main Content UI */
        .dashboard_main {
            margin-left: 240px;
            padding: 40px;
            width: 100%;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 255, 136, 0.1);
            border-radius: 15px;
            padding: 40px;
            backdrop-filter: blur(10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }

        .welcome-card h1 {
            font-size: 32px;
            color: #00ff88;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .welcome-card p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard_sidebar {
                width: 70px;
            }
            .sidebar_logo, .dashboard_sidebar ul li a span {
                display: none;
            }
            .dashboard_main {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body> 
    <div class="dashboard_sidebar">
        <div class="sidebar_logo">GAMEWARE</div>
        <ul>
            <li><a href="index.php">🎮 Shop</a></li>
            <li><a href="myorders.php">📦 My Orders</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
  
    <div class="dashboard_main">    
        <div class="welcome-card">
            <h1>Welcome Back!</h1>
            <p>Welcome to your <b>User Dashboard</b>. Here you can manage your gaming orders.</p>
        </div>
    </div> 
</body>
</html>