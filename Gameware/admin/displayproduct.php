<?php
session_start();
include "../db.php";

// Initialize variables to avoid undefined warnings
$result1 = null;
$error = '';
$success = '';

if(isset($_SESSION['user_id']) && $_SESSION['user_role'] == "admin") {
    // This query should run regardless of POST or GET request
    $sql = "SELECT * FROM products";
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
    <title>Document</title>

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
        }

        th{
            border-top: 4px solid darkblue;
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr, th, td{
            padding: 10px;
            text-align: center;
            border-bottom: 2px solid blue;           
        }
        
        td{
            background-color: lightblue;
        }

        img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        .update{
            background-color: lightgreen;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            color: black;
            display: inline-block;
        }

        .delete{
            background-color: lightcoral;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            color: black;
            display: inline-block;
        }

        .update:hover {
            background-color: #4CAF50;
            color: white;
        }

        .delete:hover {
            background-color: #f44336;
            color: white;
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
        <h1>Products</h1>
        <table>
            <thead>
                <tr>
                    <th>Product Title</th>
                    <th>Product Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Image</th>
                    <th>Category Name</th>
                    <th>Action</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if($result1 && mysqli_num_rows($result1) > 0) {
                    while ($row = mysqli_fetch_assoc($result1)) {
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>$<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo $row['stock']; ?></td>
                    <td>
                        <?php if(!empty($row['image'])): ?>
                            <img src="../image/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><a class="update" href="updateproduct.php?product_id=<?php echo $row['id']; ?>">Update</a></td>
                    <td><a class="delete" href="deleteproduct.php?product_id=<?php echo $row['id']; ?>">Delete</a></td>
                </tr>
                <?php 
                    }
                } else {
                ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        No products found. <a href="addproduct.php">Add a product</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>