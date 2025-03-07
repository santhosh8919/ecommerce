<?php
// Start session and check if the user is logged in
session_start();

// Logout Logic
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: pages/login.php");
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login.php");
    exit();
}

// Database Connection
include 'includes/db.php';

// Fetch Categories
$categoryStmt = $conn->query("SELECT DISTINCT category FROM products");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize filtering options
$selectedCategory = $_GET['category'] ?? "";
$searchQuery = $_GET['search'] ?? "";

// Fetch products with filtering
$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($selectedCategory)) {
    $query .= " AND category = ?";
    $params[] = $selectedCategory;
}

if (!empty($searchQuery)) {
    $query .= " AND name LIKE ?";
    $params[] = "%" . $searchQuery . "%";
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f4f4f4;
            border-radius: 5px;
        }
        select, input[type="text"], button {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .product {
            width: 250px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: #fff;
            text-align: center;
        }
        .product img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .logout-button {
            background-color: red;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>E-Commerce Store</h1>
            <nav>
                <a href="pages/login.php">Login</a>
                <a href="pages/register.php">Register</a>
                <a href="pages/cart.php" class="cart-link">
                    <img src="images/cart-icon.png" alt="Cart" class="cart-icon">
                    Cart
                </a>
                <a href="pages/order.php">Order</a>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="logout" class="logout-button">Logout</button>
                </form>
            </nav>
        </div>
    </header>
    <div class="main-container">
        <main>
            <h2>Products</h2>

            <!-- Filter & Search Bar -->
            <div class="filter-bar">
                <form method="GET" style="display: flex;">
                    <!-- Category Filter -->
                    <select name="category" onchange="this.form.submit()">
                        <option value="" >All Categories</option>
                        <?php foreach ($categories as $cat) : ?>
                            <option value="<?= htmlspecialchars($cat['category']); ?>" <?= ($selectedCategory === $cat['category']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Search by Product Name -->
                    <input type="text" name="search" placeholder="Search by product name..." value="<?= htmlspecialchars($searchQuery); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>

            <div class="product-list">
                <?php if (empty($products)) : ?>
                    <p>No products available.</p>
                <?php else : ?>
                    <?php foreach ($products as $product) : ?>
                        <div class="product">
                            <h3><?= htmlspecialchars($product['name']); ?></h3>
                            <p>Price: $<?= number_format($product['price'], 2); ?></p>
                            <p><?= htmlspecialchars($product['description']); ?></p>
                            <?php if (!empty($product['image'])) : ?>
                                <img src="images/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                            <?php endif; ?>
                            <form method="POST" action="pages/cart.php">
                                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                <button type="submit" name="add_to_cart" class="add-to-cart-button">Add to Cart</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <footer>
        <p>&copy; <?= date('Y'); ?> Online Store. All rights reserved.</p>
    </footer>
</body>
</html>
