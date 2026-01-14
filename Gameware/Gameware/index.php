<?php
session_start();
include "db.php";

$result_product_category = null;
$result = null; // Initialize $result here

if(isset($_GET['category_name'])){
     $category_name = $_GET['category_name'];
     $sql_product_category = "SELECT * FROM products WHERE category_name = '$category_name' AND stock > 0";
     $result_product_category = mysqli_query($conn, $sql_product_category);
}

// Get categories for the header
$sql_categories_query = "SELECT * FROM categories";
$result_categories = mysqli_query($conn, $sql_categories_query);

// Get search functionality
$search_results = null;
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = cleanInput($_GET['search']);
    $search_sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ? AND stock > 0";
    $search_stmt = mysqli_prepare($conn, $search_sql);
    $search_param = "%$search_term%";
    mysqli_stmt_bind_param($search_stmt, "ss", $search_param, $search_param);
    mysqli_stmt_execute($search_stmt);
    $search_results = mysqli_stmt_get_result($search_stmt);
}

if(!isset($_GET['category_name']) && !isset($_GET['search'])) {
    // Show only products with stock > 0
    $sql = "SELECT * FROM products WHERE stock > 0";
    $result = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gameware - The Ultimate Game Hub</title>
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

        /* Header Styles */
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

        .logo span {
            color: #ffffff;
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

        /* User Actions */
        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .welcome-user {
            color: #00ff88;
            font-weight: 600;
            font-size: 14px;
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
            border: none;
            cursor: pointer;
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

        /* Search Bar */
        .search-container {
            flex: 1;
            max-width: 500px;
            margin: 0 40px;
        }

        .search-bar {
            width: 100%;
            padding: 12px 20px;
            border-radius: 25px;
            border: 2px solid #00ff88;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-bar:focus {
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
            background: rgba(255, 255, 255, 0.15);
        }

        .search-bar::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        /* Hero Section */
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

        /* Products Grid */
        .products-container {
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
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

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        /* Product Card */
        .product-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 255, 136, 0.1);
            backdrop-filter: blur(10px);
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 255, 136, 0.2);
            border-color: #00ff88;
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 2px solid #00ff88;
        }

        .product-info {
            padding: 20px;
        }

        .product-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #ffffff;
        }

        .product-description {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
            height: 60px;
            overflow: hidden;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .product-price {
            font-size: 24px;
            font-weight: 900;
            color: #00ff88;
        }

        .product-stock {
            background: rgba(0, 255, 136, 0.2);
            color: #00ff88;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Buy Button */
        .buy-form {
            margin-top: 15px;
        }

        .quantity-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .quantity-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        .quantity-input {
            width: 70px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #00ff88;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-align: center;
        }

        .buy-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            color: #0a0a0a;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .buy-button:hover {
            background: linear-gradient(45deg, #00cc6a, #00ff88);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.4);
        }

        .register-prompt {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            display: block;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .register-prompt:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
        }

        /* Footer */
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

        /* Responsive Design */
        @media (max-width: 1024px) {
            .navbar {
                padding: 15px 20px;
                flex-wrap: wrap;
            }
            
            .search-container {
                order: 3;
                max-width: 100%;
                margin: 15px 0 0 0;
            }
            
            .hero h1 {
                font-size: 36px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero {
                padding: 40px 20px;
            }
            
            .hero h1 {
                font-size: 28px;
            }
            
            .hero p {
                font-size: 16px;
            }
            
            .products-container {
                padding: 20px;
            }
        }

        /* No Products Message */
        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 18px;
            grid-column: 1 / -1;
        }

        /* Category Badge */
        .category-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.8);
            color: #00ff88;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">GAMEWARE</div>
        
        <ul class="nav-links">
            <?php 
            if($result_categories) {
                mysqli_data_seek($result_categories, 0);
                while($row_category = mysqli_fetch_assoc($result_categories)) { 
            ?>
            <li><a href="index.php?category_name=<?php echo urlencode($row_category['name']); ?>">
                <?php echo htmlspecialchars($row_category['name']); ?>
            </a></li>
            <?php 
                }
            }
            ?>
        </ul>
        
        <div class="search-container">
            <form method="GET" action="index.php">
                <input type="text" name="search" class="search-bar" placeholder="Search Games..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </form>
        </div>
        
        <div class="user-actions">
            <?php if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                <div class="welcome-user">
                    Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
                </div>
                <?php if($_SESSION['user_role'] == 'admin'): ?>
                    <a href="admin/dashboard.php" class="login-btn">Dashboard</a>
                <?php else: ?>
                    <a href="dashboard.php" class="login-btn">Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
                <a href="register.php" class="logout-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1>THE ULTIMATE GAME HUB</h1>
        <p>Discover the latest and greatest games. From action-packed adventures to mind-bending puzzles, find your next gaming obsession here.</p>
    </section>

    <!-- Products Section -->
    <section class="products-container">
        <h2 class="section-title">
            <?php 
            if(isset($_GET['search']) && !empty($_GET['search'])) {
                echo "Search Results for: " . htmlspecialchars($_GET['search']);
            } elseif(isset($_GET['category_name'])) {
                echo htmlspecialchars($_GET['category_name']) . " Games";
            } else {
                echo "Featured Games";
            }
            ?>
        </h2>
        
        <div class="products-grid">
            <?php 
            // Show search results
            if(isset($_GET['search']) && $search_results && mysqli_num_rows($search_results) > 0) {
                while($row = mysqli_fetch_assoc($search_results)) {
                    displayProductCard($row);
                }
            }
            // Show category products
            elseif(isset($_GET['category_name']) && $result_product_category && mysqli_num_rows($result_product_category) > 0) {
                while($row = mysqli_fetch_assoc($result_product_category)) {
                    displayProductCard($row);
                }
            }
            // Show all products
            elseif(!isset($_GET['category_name']) && !isset($_GET['search']) && $result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    displayProductCard($row);
                }
            } else {
                echo '<div class="no-products">No games found. Try a different search or category.</div>';
            }
            
            // Function to display product card
            function displayProductCard($row) {
            ?>
            <div class="product-card">
                <?php if(isset($row['category_name'])): ?>
                    <div class="category-badge"><?php echo htmlspecialchars($row['category_name']); ?></div>
                <?php endif; ?>
                
                <img src="image/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image">
                
                <div class="product-info">
                    <h3 class="product-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars($row['description']); ?></p>
                    
                    <div class="product-meta">
                        <span class="product-price">$<?php echo number_format($row['price'], 2); ?></span>
                        <span class="product-stock">Stock: <?php echo htmlspecialchars($row['stock']); ?></span>
                    </div>
                    
                    <?php if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                    <form action="singleorder.php" method="get" class="buy-form">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                        
                        <div class="quantity-container">
                            <span class="quantity-label">Quantity:</span>
                            <input type="number" name="quantity" class="quantity-input" 
                                   min="1" max="<?php echo $row['stock']; ?>" value="1" required>
                        </div>
                        
                        <button type="submit" name="submit" value="1" class="buy-button">
                            🎮 Buy Now
                        </button>
                    </form>
                    <?php else: ?>
                    <a href="register.php" class="register-prompt">
                        🔒 Register to Purchase
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Gameware. All rights reserved. | The Ultimate Game Hub</p>
        <p style="margin-top: 10px; font-size: 12px;">All game titles are trademarks of their respective owners.</p>
    </footer>

    <script>
        // Search focus effect
        const searchBar = document.querySelector('.search-bar');
        if(searchBar) {
            searchBar.addEventListener('focus', function() {
                this.style.transform = 'scale(1.02)';
            });
            
            searchBar.addEventListener('blur', function() {
                this.style.transform = 'scale(1)';
            });
        }

        // Product card animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all product cards
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });

        // Auto-submit search on Enter
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.querySelector('form[method="GET"]');
            if(searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const searchInput = this.querySelector('input[name="search"]');
                    if(searchInput && searchInput.value.trim() === '') {
                        e.preventDefault();
                        window.location.href = 'index.php';
                    }
                });
            }
        });
    </script>
</body>
</html>