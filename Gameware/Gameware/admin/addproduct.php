<?php
session_start();
include "../db.php";

// Initialize variables to avoid undefined warnings
$result1 = null;
$error = '';
$success = '';

if(isset($_SESSION['user_id'])) {
    // This query should run regardless of POST or GET request
    $sql1 = "SELECT * FROM categories";
    $result1 = mysqli_query($conn, $sql1);
    
    if(!$result1) {
        $error = "Error loading categories: " . mysqli_error($conn);
    }

    if($_SESSION['user_role'] == "admin"){
        if(isset($_POST['submit'])) {
            // Collect form data
            $name = mysqli_real_escape_string($conn, $_POST['name']); // Fixed: changed from 'product' to 'name'
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $price = mysqli_real_escape_string($conn, $_POST['price']);
            $stock = mysqli_real_escape_string($conn, $_POST['stock']);
            $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
            
            // Handle file upload
            $image = $_FILES['image']['name'];
            $temp_location = $_FILES['image']['tmp_name'];
            $upload_location = "../image/";
            
            // Check if file was uploaded
            if(!empty($image)) {
                $target_file = $upload_location . basename($image);
                
                // Insert product into database - REMOVED category_id from query
                $sql = "INSERT INTO products (name, description, price, stock, image, category_name) 
                        VALUES ('$name', '$description', '$price', '$stock', '$image', '$category_name')";
                
                $result = mysqli_query($conn, $sql); // Fixed: changed $sql1 to $sql
                
                if($result) {
                    // Move uploaded file
                    if(move_uploaded_file($temp_location, $target_file)) {
                        $success = "Product added successfully!";
                    } else {
                        $error = "Product added but failed to upload image!";
                    }
                } else {
                    $error = "Error adding product: " . mysqli_error($conn);
                }
            } else {
                $error = "Please select an image!";
            }
        }        
    } else {
        // If user is not admin
        header("Location: ../index.php");
        exit();
    }
} else {
    // If user is not logged in
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin</title>
    <style>
        *{  
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .dashboard_sidebar{  
            position: fixed;
            top: 0;
            background-color: darkcyan;
            width: 200px;
            height: 100%;
        }

        .dashboard_sidebar ul li{
            list-style: none;
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
            position: relative;
            padding: 30px;
            left: 45%;
            margin-top: 100px;
        }

        .dashboard_main input, 
        .dashboard_main textarea,
        .dashboard_main select {
            display: block;
            margin: 10px;
            padding: 15px;
            border-radius: 15px 50px;
            border-left: 2px solid lightcoral;
            border-right: 2px solid lightcoral;
            width: 300px;
            box-sizing: border-box;
        }

        .dashboard_main textarea {
            height: 100px;
            resize: vertical;
        }

        .button {
            width: 300px;
            background-color: green;
            color: white;
            border: none;
            cursor: pointer;
            padding: 15px;
            border-radius: 15px 50px;
            border-left: 2px solid lightcoral;
            border-right: 2px solid lightcoral;
        }

        .button:hover {
            background-color: darkgreen;
        }

        .message {
            padding: 10px;
            margin: 10px;
            border-radius: 5px;
            width: 300px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        h2 {
            margin: 10px;
            color: #333;
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
        <h1>Add New Product</h1>
        
        <?php if($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="addproduct.php" method="post" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Enter Product Name" required>
            <textarea name="description" placeholder="Enter Product Description" required></textarea>
            <input type="number" name="price" placeholder="Enter Price" step="0.01" required>
            <input type="number" name="stock" placeholder="Enter Stock Number" required>
            
            <h2>Upload Image</h2>
            <input type="file" name="image" accept="image/*" required>
            
            <h2>Select Category</h2>
            <?php if($result1 && mysqli_num_rows($result1) > 0): ?>
                <select name="category_name" required>
                    <option value="">Select Category</option>
                    <?php 
                    while($row = mysqli_fetch_assoc($result1)): 
                    ?>
                        <option value="<?php echo $row['name']; ?>">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            <?php else: ?>
                <p>No categories available. Please add categories first.</p>
            <?php endif; ?>
            
            <input class="button" type="submit" name="submit" value="Add Product">
        </form>
    </div>
</body>
</html>