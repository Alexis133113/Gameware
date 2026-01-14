<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

if(isset($_GET['submit'])) {
    if(isset($_GET['user_id'], $_GET['product_id'], $_GET['product_price'], $_GET['quantity'])) {
        
        $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
        $product_id = mysqli_real_escape_string($conn, $_GET['product_id']);
        $product_quantity = intval($_GET['quantity']);
        $product_price = floatval($_GET['product_price']);
        
        // Check if product has stock
        $sql_check_stock = "SELECT stock, price, name FROM products WHERE id = '$product_id'";
        $result_check_stock = mysqli_query($conn, $sql_check_stock);
        
        if($result_check_stock && mysqli_num_rows($result_check_stock) > 0) {
            $row_stock = mysqli_fetch_assoc($result_check_stock);
            $current_stock = $row_stock['stock'];
            $price = floatval($row_stock['price']);
            $product_name = $row_stock['name'];
            
            if($current_stock >= $product_quantity && $product_quantity > 0) {
                // Calculate total
                $calculated_total = $price * $product_quantity;
                
                // Start transaction
                mysqli_autocommit($conn, false);
                
                try {
                    // INSERT order into single_order table
                    $sql_order = "INSERT INTO single_order (user_id, product_id, product_quantity, total_amount) 
                                 VALUES ('$user_id', '$product_id', '$product_quantity', '$calculated_total')";
                    
                    $result_order = mysqli_query($conn, $sql_order);
                    
                    if(!$result_order) {
                        throw new Exception("Order Error: " . mysqli_error($conn));
                    }
                    
                    // Get the auto-incremented order ID (for internal use)
                    $order_id = mysqli_insert_id($conn);
                    
                    // UPDATE product stock
                    $new_stock = $current_stock - $product_quantity;
                    $sql_update_stock = "UPDATE products SET stock = '$new_stock' WHERE id = '$product_id'";
                    $result_update_stock = mysqli_query($conn, $sql_update_stock);
                    
                    if(!$result_update_stock) {
                        throw new Exception("Stock Update Error: " . mysqli_error($conn));
                    }
                    
                    // Commit transaction
                    mysqli_commit($conn);
                    
                    // Show success message (Order ID hidden from user)
                    echo "<div style='padding: 20px; text-align: center; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px;'>";
                    echo "<h2 style='color: #155724;'>✅ Order Successful!</h2>";
                    echo "<p>Your order has been placed successfully.</p>";
                    echo "<p><strong>Product:</strong> $product_name</p>";
                    echo "<p><strong>Quantity:</strong> $product_quantity</p>";
                    echo "<p><strong>Unit Price:</strong> $" . number_format($price, 2) . "</p>";
                    echo "<p><strong>Total Amount:</strong> $" . number_format($calculated_total, 2) . "</p>";
                    echo "<p><strong>Remaining Stock:</strong> $new_stock</p>";
                    echo "<p><strong>Payment Method:</strong> Cash on Delivery</p>";
                    echo "<p><strong>Status:</strong> Order confirmed and will be processed shortly</p>";
                    echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;'>Continue Shopping</a>";
                    echo "</div>";
                    
                } catch (Exception $e) {
                    // Rollback transaction on error
                    mysqli_rollback($conn);
                    echo "<div style='padding: 20px; text-align: center; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
                    echo "<h2 style='color: #721c24;'>❌ Error!</h2>";
                    echo "<p>" . $e->getMessage() . "</p>";
                    echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go Back</a>";
                    echo "</div>";
                }
                
                mysqli_autocommit($conn, true);
                
            } else {
                echo "<div style='padding: 20px; text-align: center; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; margin: 20px;'>";
                echo "<h2 style='color: #856404;'>⚠️ Out of Stock!</h2>";
                echo "<p>Not enough stock available! Available: " . $current_stock . "</p>";
                echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go Back</a>";
                echo "</div>";
            }
        } else {
            echo "<div style='padding: 20px; text-align: center; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
            echo "<h2 style='color: #721c24;'>❌ Product Not Found!</h2>";
            echo "<p>The product you're trying to order doesn't exist.</p>";
            echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go Back</a>";
            echo "</div>";
        }
    } else {
        echo "<div style='padding: 20px; text-align: center; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
        echo "<h2 style='color: #721c24;'>❌ Missing Parameters!</h2>";
        echo "<p>Required parameters are missing. Please go back and try again.</p>";
        echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go Back</a>";
        echo "</div>";
    }
} else {
    header("Location: index.php");
    exit();
}
?>