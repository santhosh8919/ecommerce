<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];

    // Update order status to "cancelled"
    $stmt = $conn->prepare("UPDATE payments SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'completed'");
    $stmt->execute([$order_id, $_SESSION['user_id']]);

    header("Location: orders.php"); // Redirect back to orders page
    exit();
}
?>
