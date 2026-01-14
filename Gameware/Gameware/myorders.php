<?php
session_start();
include "db.php";

// Initialize variables to avoid undefined warnings
$result1 = null;
$error = '';
$success = '';

// Check if user is logged in
if(isset($_SESSION['user_id'])) {
    // Get the user_id from session
    $user_id = $_SESSION['user_id'];
    
    // Get orders for this specific user from single_order table
    $sql = "SELECT so.*, p.name as product_name, p.price as unit_price, p.image as product_image
            FROM single_order so 
            LEFT JOIN products p ON so.product_id = p.id 
            WHERE so.user_id = '$user_id' 
            ORDER BY so.id DESC";
    $result1 = mysqli_query($conn, $sql);
    
    if(!$result1) {
        echo "Error!: " . mysqli_error($conn);
    }                    
} else {
    // If user is not logged in
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Gameware</title>

    <style>
        *{  
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

        /* Sidebar with Gameware styling */
        .dashboard_sidebar{  
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            border-right: 2px solid #00ff88;
            width: 250px;
            height: 100%;
            z-index: 1000;
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.1);
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(0, 255, 136, 0.2);
            text-align: center;
        }

        .sidebar-logo {
            font-size: 24px;
            font-weight: 900;
            color: #00ff88;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .sidebar-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            letter-spacing: 1px;
        }

        .user-info {
            padding: 20px;
            border-bottom: 1px solid rgba(0, 255, 136, 0.2);
            text-align: center;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #0a0a0a;
        }

        .user-name {
            color: #00ff88;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .user-role {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .dashboard_sidebar ul {
            padding: 20px 0;
        }

        .dashboard_sidebar ul li{
            list-style: none;
            margin: 5px 15px;
        }

        .dashboard_sidebar ul li a{
            display: block;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 500;
        }

        .dashboard_sidebar ul li a:hover {
            background: linear-gradient(90deg, rgba(0, 255, 136, 0.1), transparent);
            color: #00ff88;
            transform: translateX(5px);
        }

        /* Main content area */
        .dashboard_main{
            padding: 30px;
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        /* Header */
        h1 {
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 10px;
            text-transform: uppercase;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dashboard_main p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        /* Table Styling */
        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(0, 255, 136, 0.1);
            backdrop-filter: blur(10px);
        }

        th{
            background: rgba(0, 255, 136, 0.2);
            color: #00ff88;
            font-weight: 600;
            padding: 18px 15px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
            border-bottom: 2px solid rgba(0, 255, 136, 0.3);
        }

        tr, td{
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid rgba(0, 255, 136, 0.1);
        }
        
        td{
            background-color: rgba(255, 255, 255, 0.02);
            color: rgba(255, 255, 255, 0.9);
        }

        tr:hover td {
            background: rgba(0, 255, 136, 0.05);
        }

        .no-orders {
            text-align: center;
            padding: 50px;
            font-size: 18px;
            background: rgba(0, 255, 136, 0.05);
            border-radius: 15px;
            border: 2px dashed rgba(0, 255, 136, 0.2);
            margin: 20px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .no-orders p {
            margin-bottom: 20px;
        }
        
        .order-id {
            font-weight: bold;
            color: #00ff88;
            font-family: 'Courier New', monospace;
            font-size: 16px;
        }
        
        .continue-shopping {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            color: #0a0a0a;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .continue-shopping:hover {
            background: linear-gradient(45deg, #00cc6a, #00ff88);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.4);
        }
        
        .product-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid rgba(0, 255, 136, 0.3);
            margin: 0 auto 10px;
            display: block;
        }

        .total-amount {
            color: #00ff88;
            font-weight: bold;
            font-size: 18px;
        }

        .status-completed {
            color: #00ff88;
            font-weight: bold;
        }

        .payment-method {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            margin-top: 5px;
        }

        /* Order Summary */
        .order-summary {
            background: rgba(0, 255, 136, 0.1);
            border-radius: 15px;
            padding: 20px 30px;
            border: 1px solid rgba(0, 255, 136, 0.3);
            margin-top: 30px;
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
        }

        .order-summary p {
            margin-bottom: 10px;
            font-size: 16px;
        }

        /* Footer */
        .dashboard-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 255, 136, 0.2);
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard_sidebar {
                width: 200px;
            }
            
            .dashboard_main {
                margin-left: 200px;
                width: calc(100% - 200px);
                padding: 20px;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 12px 8px;
            }
        }

        @media (max-width: 480px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="dashboard_sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">GAMEWARE</div>
            <div class="sidebar-subtitle">Player Dashboard</div>
        </div>
        
        <div class="user-info">
            <div class="user-avatar">
                <?php 
                    $initial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
                    echo $initial;
                ?>
            </div>
            <div class="user-name">
                <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?>
            </div>
            <div class="user-role">Gamer</div>
        </div>
        
        <ul>
            <li><a href="index.php">Shop</a></li>
            <li><a href="myorders.php">My Orders</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
  
    <!-- Main Content -->
    <div class="dashboard_main">
        <h1>My Orders</h1>
        
        <?php if(isset($_SESSION['user_name'])): ?>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Here are your orders:</p>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if($result1 && mysqli_num_rows($result1) > 0) {
                    while ($row = mysqli_fetch_assoc($result1)) {
                ?>
                <tr>
                    <td class="order-id"><?php echo htmlspecialchars($row['id']); ?></td>
                    <td>
                        <?php if(!empty($row['product_image'])): ?>
                            <img src="image/<?php echo htmlspecialchars($row['product_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                 class="product-image"><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($row['product_name']); ?><br>
                        
                    </td>
                    <td><?php echo htmlspecialchars($row['product_quantity']); ?></td>
                    <td>$<?php echo number_format($row['unit_price'], 2); ?></td>
                    <td class="total-amount">$<?php echo number_format($row['total_amount'], 2); ?></td>
                    <td>
                        <span class="status-completed">✓ Completed</span><br>
                        <small class="payment-method">Cash on Delivery</small>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                ?>
                <tr>
                    <td colspan="6" class="no-orders">
                        <p>You haven't placed any orders yet.</p>
                        <a href="index.php" class="continue-shopping">Start Shopping Now</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <?php if($result1 && mysqli_num_rows($result1) > 0): ?>
        <div class="order-summary">
            <p>Total Orders: <?php echo mysqli_num_rows($result1); ?></p>
            <a href="index.php" class="continue-shopping">Continue Shopping</a>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="dashboard-footer">
            &copy; <?php echo date('Y'); ?> Gameware. All rights reserved. | The Ultimate Game Hub
        </div>
    </div>
</body>
</html>