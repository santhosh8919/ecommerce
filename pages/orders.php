<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's completed orders
$stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? AND (status = 'completed' OR status = 'cancelled') ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
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
            max-width: 600px;
            margin: auto;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        .cancel-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .cancel-btn:disabled {
            background-color: gray;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Orders</h2>
        <?php if (empty($orders)) : ?>
            <p>No orders found.</p>
        <?php else : ?>
            <table>
                <tr>
                    <th>Transaction ID</th>
                    <th>UPI ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Cancel Orders</th>
                </tr>
                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td><?= htmlspecialchars($order['transaction_id']); ?></td>
                        <td><?= htmlspecialchars($order['upi_id']); ?></td>
                        <td>$<?= number_format($order['amount'], 2); ?></td>
                        <td style="color: <?= $order['status'] == 'cancelled' ? 'red' : 'green'; ?>;">
                            <?= htmlspecialchars($order['status']); ?>
                        </td>
                        <td>
                            <?php if ($order['status'] == 'completed') : ?>
                                <form method="POST" action="cancel_order.php">
                                    <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                    <button type="submit" class="cancel-btn" name="cancel_order">Cancel</button>
                                </form>
                            <?php else : ?>
                                <button class="cancel-btn" disabled>Canceled</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        <br>
        <a href="../index.php">Back to Shop</a>
    </div>
</body>
</html>
