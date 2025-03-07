# Step-by-Step Documentation for Orders Page (`orders.php`)

---

## Purpose
The **Orders Page** displays a list of all completed or canceled orders for a logged-in user. It allows users to view their order details and provides an option to cancel an order if it is still in the **"completed"** status.

---

## 1. PHP Logic for Fetching Orders
The PHP code at the top handles authentication and fetches user orders from the database.

```php
<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's completed or canceled orders
$stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? AND (status = 'completed' OR status = 'cancelled') ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
```

### Key Features:
- Ensures the user is logged in; otherwise, redirects them to the login page.
- Retrieves all **completed** and **canceled** orders from the **`payments`** table for the logged-in user.
- Orders are sorted by **creation date (latest first)**.

---

## 2. HTML Structure for Displaying Orders
This section creates a table displaying the user's order details.

```html
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
                    <th>Cancel</th>
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
```

### Features:
- Displays orders in a **table format** with columns for:
  - **Transaction ID**
  - **UPI ID**
  - **Amount**
  - **Status** (Green for completed, Red for canceled)
  - **Cancel Button** (Only available for completed orders)
- If no orders exist, it displays: *"No orders found."*
- Orders that are **canceled** show a disabled "Canceled" button.

---

## 3. Implementing the Cancel Order Functionality
Users can cancel orders by clicking the "Cancel" button. The request is handled by `cancel_order.php`.

### `cancel_order.php`
```php
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
```

### How It Works:
1. **Receives the order ID** from the form submission.
2. **Updates the database**: Changes the order status from **"completed"** to **"cancelled"**.
3. **Redirects back to `orders.php`**, refreshing the list.

---

## 4. Testing the Orders Page
✅ **Step 1**: Ensure the `payments` table contains **sample completed orders**.  
✅ **Step 2**: Open `orders.php` and check if orders are listed correctly.  
✅ **Step 3**: Click the **Cancel** button and verify that the order status changes to **"Cancelled"**.  
✅ **Step 4**: Confirm that the cancel button is **disabled** after canceling the order.  

---

## Next Step
The next page to implement is the **Admin Orders Page (`admin/orders.php`)**, which will allow the admin to **manage all user orders**. Let’s proceed! 🚀

