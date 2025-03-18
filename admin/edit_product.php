<?php
session_start();
include '../includes/db.php';

// Enable error debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<h2 style='color:red; text-align:center;'>Invalid product ID.</h2>");
}

$product_id = intval($_GET['id']);

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("<h2 style='color:red; text-align:center;'>Product not found.</h2>");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $price = floatval($_POST['price']);
    $description = htmlspecialchars($_POST['description']);
    $image = $product['image']; // Keep old image by default

    // Handle new image upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../images/";
        $image_name = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;

        // Validate image type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!in_array($image_file_type, $allowed_types)) {
            echo "<script>alert('Invalid image format! Allowed formats: JPG, JPEG, PNG, GIF');</script>";
        } else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $image_name;
            } else {
                echo "<script>alert('Image upload failed.');</script>";
            }
        }
    }

    // Update product details
    $update_stmt = $conn->prepare("UPDATE products SET name = :name, price = :price, description = :description, image = :image WHERE id = :id");
    $update_stmt->bindParam(':name', $name);
    $update_stmt->bindParam(':price', $price);
    $update_stmt->bindParam(':description', $description);
    $update_stmt->bindParam(':image', $image);
    $update_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        echo "<script>alert('Product updated successfully!'); window.location.href='manage_products.php';</script>";
    } else {
        echo "<script>alert('Failed to update product!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 450px;
            width: 100%;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h2 {
            color: #fff;
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-size: 14px;
            color: #f8f9fa;
            margin-bottom: 5px;
            text-align: left;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            margin-bottom: 10px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            outline: none;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        input::placeholder, textarea::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        img {
            width: 100px;
            border-radius: 8px;
            margin: 10px 0;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .file-input {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
        }

        .file-input input {
            display: none;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #ff7eb3;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #ff5588;
        }

        .back-btn {
            margin-top: 15px;
            text-align: center;
        }

        .back-btn a {
            color: #fff;
            font-size: 14px;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s;
        }

        .back-btn a:hover {
            border-bottom: 1px solid white;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Product</h2>

    <form action="" method="POST" enctype="multipart/form-data">
        <label>Product Name:</label>
        <input type="text" name="name" placeholder="Enter product name"
               value="<?= isset($product['name']) ? htmlspecialchars($product['name']) : ''; ?>" required>

        <label>Price ($):</label>
        <input type="number" name="price" step="0.01" placeholder="Enter price"
               value="<?= isset($product['price']) ? $product['price'] : ''; ?>" required>

        <label>Description:</label>
        <textarea name="description" placeholder="Enter description" required>
            <?= isset($product['description']) ? htmlspecialchars($product['description']) : ''; ?>
        </textarea>

        <label>Current Image:</label>
        <img src="<?= isset($product['image']) ? '../images/' . htmlspecialchars($product['image']) : 'default.jpg'; ?>" alt="Product Image">

        <label class="file-input">
            <span>Upload New Image</span>
            <input type="file" name="image">
        </label>

        <button type="submit" class="btn">Update Product</button>
    </form>

    <div class="back-btn">
        <a href="manage_products.php">← Back to Products</a>
    </div>
</div>

</body>
</html>
