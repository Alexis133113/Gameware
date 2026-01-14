<?php
session_start();
include "../db.php";

// Initialize variables to avoid undefined warnings
$result1 = null;
$error = '';
$success = '';
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
$row = array(); // Initialize $row array

if(isset($_SESSION['user_id'])) {
    // This query should run regardless of POST or GET request
    $sql1 = "SELECT * FROM categories";
    $result1 = mysqli_query($conn, $sql1);
    
    if(isset($_GET['product_id'])){
        $product_id = $_GET['product_id']; // Added semicolon
        $sql2 = "SELECT * FROM products WHERE id ='$product_id'"; // Fixed typo
        $result2 = mysqli_query($conn,$sql2);
        $row = mysqli_fetch_assoc($result2); // Changed to $row
    }

    if(!$result1) {
        $error = "Error loading categories: " . mysqli_error($conn);
    }

    if($_SESSION['user_role'] == "admin"){
        if(isset($_POST['submit'])) {
            // Collect form data
            $product_id = $_GET['product_id'];
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $price = mysqli_real_escape_string($conn, $_POST['price']);
            $stock = mysqli_real_escape_string($conn, $_POST['stock']);
            $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
            
            // Handle file upload
            $image = $_FILES['image']['name'];
            
            // Check if new image is uploaded
            if(!empty($image)) {
                $temp_location = $_FILES['image']['tmp_name'];
                $upload_location = "../image/";
                $target_file = $upload_location . basename($image);
                
                $sql4 = "UPDATE products SET name = '$name',
                         description = '$description',
                         price = '$price', stock = '$stock', 
                         image = '$image', category_name = '$category_name' 
                         WHERE id ='$product_id'";

                $result4 = mysqli_query($conn, $sql4);
                if($result4){
                    // Move uploaded file
                    move_uploaded_file($temp_location, $target_file);
                    header("Location: displayproduct.php");
                    exit();
                } else {
                    echo "Error!: " . mysqli_error($conn);
                }
            } else {
                // Update without changing image
                $sql3 = "UPDATE products SET name = '$name', 
                         description = '$description', price = '$price',
                         stock = '$stock', category_name = '$category_name' 
                         WHERE id = '$product_id'";

                $result3 = mysqli_query($conn, $sql3);
                if($result3){
                    header("Location: displayproduct.php");
                    exit();
                } else {
                    echo "Error!: " . mysqli_error($conn);
                }
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
    <title>Update Product - Admin</title>
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
        
        img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin: 10px;
            border: 1px solid #ccc;
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
        <h1>Update Product</h1>
        
        <?php if($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($row)): ?>
        <form action="updateproduct.php?product_id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
            <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>">
            <textarea name="description"><?php echo htmlspecialchars($row['description']); ?></textarea>
            <input type="number" name="price" value="<?php echo htmlspecialchars($row['price']); ?>">
            <input type="number" name="stock" value="<?php echo htmlspecialchars($row['stock']); ?>">
            
            <?php if(!empty($row['image'])): ?>
                <img src="../image/<?php echo htmlspecialchars($row['image']); ?>" alt="Current Image">
            <?php endif; ?>
            
            <input type="file" name="image">
            
            <h3>Current Category: <?php echo htmlspecialchars($row['category_name']); ?></h3>
            
            <?php if($result1 && mysqli_num_rows($result1) > 0): ?>
                <select name="category_name" required>
                    <option value="<?php echo htmlspecialchars($row['category_name']); ?>">
                        <?php echo htmlspecialchars($row['category_name']); ?> (Current)
                    </option>
                    <?php 
                    mysqli_data_seek($result1, 0); // Reset pointer
                    while($category = mysqli_fetch_assoc($result1)): 
                        if($category['name'] != $row['category_name']):
                    ?>
                        <option value="<?php echo htmlspecialchars($category['name']); ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php 
                        endif;
                    endwhile; 
                    ?>
                </select>
            <?php else: ?>
                <p>No categories available.</p>
            <?php endif; ?>
            
            <input class="button" type="submit" name="submit" value="Update Product">
        </form>
        <?php else: ?>
            <p>No product found to update.</p>
        <?php endif; ?>
    </div>
</body>
</html>