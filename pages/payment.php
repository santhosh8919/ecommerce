<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Fetch the latest cart total
$stmt = $conn->prepare("SELECT SUM(c.quantity * p.price) AS total_amount FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_amount = $row['total_amount'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay_now'])) {
    $upi_id = $_POST['upi_id']; // Get UPI ID from user input
    $transaction_id = uniqid('TXN'); // Generate unique transaction ID

    // Insert payment details into the database
    $stmt = $conn->prepare("INSERT INTO payments (user_id, transaction_id, upi_id, amount, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$user_id, $transaction_id, $upi_id, $total_amount]);

    // Simulate payment success (in real-world, integrate actual UPI gateway)
    $stmt = $conn->prepare("UPDATE payments SET status = 'completed' WHERE transaction_id = ?");
    $stmt->execute([$transaction_id]);

    // Clear the cart after successful payment
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
            padding: 50px;
        }
        .container {
            background: white;
            padding: 20px;
            max-width: 400px;
            margin: auto;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            margin-bottom: 20px;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: white;
            font-size: 1.2em;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Complete Your Payment</h2>
        <p>Total Amount: <strong>$<?= number_format($total_amount, 2); ?></strong></p>
        <form method="POST">
            <input type="text" name="upi_id" placeholder="Enter UPI ID" required>
            <button type="submit" name="pay_now">Pay Now</button>
        </form>
    </div>
</body>
</html>
