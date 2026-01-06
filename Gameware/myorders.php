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
    <title>My Orders</title>

    <style>
        *{  
            margin: 0;
            padding: 0;
        }

        .dashboard_sidebar{  
            position: fixed;
            top: 0;
            background-color: darkcyan;
            width: 200px;
            height: 100%;
        }

        .dashboard_sidebar ul li{
            list-style:none;
            text-align: center;
            padding: 10px;
        }

        .dashboard_sidebar ul li a{
            display: block;
            text-decoration: none;
            color: white;
        }

        .dashboard_sidebar ul li a:hover {
            background-color: black;
        }

        .dashboard_main{
            padding: 30px;
            margin-left: 200px;
        }

        table{
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th{
            border-top: 4px solid darkblue;
            background-color: #f2f2f2;
            font-weight: bold;
            padding: 15px;
        }

        tr, th, td{
            padding: 12px;
            text-align: center;
            border-bottom: 2px solid blue;           
        }
        
        td{
            background-color: lightblue;
        }

        .no-orders {
            text-align: center;
            padding: 50px;
            font-size: 18px;
            color: #666;
            background-color: #f9f9f9;
            border-radius: 10px;
            margin: 20px;
        }
        
        .order-id {
            font-weight: bold;
            color: #333;
        }
        
        .order-date {
            color: #666;
            font-size: 0.9em;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .continue-shopping {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .continue-shopping:hover {
            background-color: #45a049;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="dashboard_sidebar">
        <ul>
            <li><a href="index.php">Shop</a></li>
            <li><a href="myorders.php">My Orders</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
  
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
                        <small>Product ID: <?php echo htmlspecialchars($row['product_id']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($row['product_quantity']); ?></td>
                    <td>$<?php echo number_format($row['unit_price'], 2); ?></td>
                    <td><strong>$<?php echo number_format($row['total_amount'], 2); ?></strong></td>
                    <td>
                        <span style="color: green; font-weight: bold;">✓ Completed</span><br>
                        <small>Cash on Delivery</small>
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
        <div style="margin-top: 20px; text-align: center;">
            <p>Total Orders: <?php echo mysqli_num_rows($result1); ?></p>
            <a href="index.php" class="continue-shopping">Continue Shopping</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>