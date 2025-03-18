<?php
session_start();
include '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the 'id' parameter is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid product ID.";
    exit();
}

$product_id = intval($_GET['id']);

try {
    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    
    // Execute the query
    if ($stmt->execute()) {
        echo "<script>alert('Product deleted successfully!'); window.location.href='manage_products.php';</script>";
    } else {
        echo "Error deleting product.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
