<?php
session_start();
include "../db.php";

// Initialize variables to avoid undefined warnings
$result1 = null;
$error = '';
$success = '';

if(isset($_SESSION['user_id']) && $_SESSION['user_role'] == "admin") {
    // Get all orders with product and user details
    $sql = "SELECT so.*, p.name as product_name, p.price as unit_price, p.image as product_image, u.name as user_name
            FROM single_order so 
            LEFT JOIN products p ON so.product_id = p.id 
            LEFT JOIN users u ON so.user_id = u.id 
            ORDER BY so.id DESC";
    $result1 = mysqli_query($conn, $sql);
    
    if(!$result1) {
        echo "Error!: " . mysqli_error($conn);
    }                    
} else {
    // If user is not logged in or not admin
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Orders</title>

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
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .customer-name {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .customer-id {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .status-completed {
            color: green;
            font-weight: bold;
        }
        
        .status-pending {
            color: orange;
            font-weight: bold;
        }
        
        .total-orders {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="dashboard_sidebar">
        <ul>
            <li><a href="addproduct.php">Add Product</a></li>
            <li><a href="displayproduct.php">View Products</a></li>
            <li><a href="vieworders.php">View Orders</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
  
    <div class="dashboard_main">
        <h1>Admin - All Orders</h1>
        
        <?php if($result1 && mysqli_num_rows($result1) > 0): ?>
        <div class="total-orders">
            Total Orders: <?php echo mysqli_num_rows($result1); ?>
        </div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
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
                        <span class="customer-name"><?php echo htmlspecialchars($row['user_name']); ?></span><br>
                        <span class="customer-id">User ID: <?php echo htmlspecialchars($row['user_id']); ?></span>
                    </td>
                    <td>
                        <?php if(!empty($row['product_image'])): ?>
                            <img src="../image/<?php echo htmlspecialchars($row['product_image']); ?>" 
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
                        <span class="status-completed">✓ Completed</span><br>
                        <small>Cash on Delivery</small>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                ?>
                <tr>
                    <td colspan="7" class="no-orders">
                        No orders found in the system.
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <?php 
        // Calculate total revenue if there are orders
        if($result1 && mysqli_num_rows($result1) > 0) {
            mysqli_data_seek($result1, 0); // Reset pointer to calculate total
            $total_revenue = 0;
            $total_quantity = 0;
            
            while ($row = mysqli_fetch_assoc($result1)) {
                $total_revenue += $row['total_amount'];
                $total_quantity += $row['product_quantity'];
            }
        ?>
        <div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 10px;">
            <h3>Order Summary</h3>
            <p><strong>Total Revenue:</strong> $<?php echo number_format($total_revenue, 2); ?></p>
            <p><strong>Total Items Sold:</strong> <?php echo $total_quantity; ?></p>
            <p><strong>Average Order Value:</strong> $<?php echo number_format($total_revenue / mysqli_num_rows($result1), 2); ?></p>
        </div>
        <?php } ?>
    </div>
</body>
</html>