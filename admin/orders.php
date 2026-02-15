<?php
session_start();
$page_title = 'Order Management';

require_once '../includes/config.php';
require_once '../includes/check_admin_session.php';

// Get all orders
$orders_query = "SELECT * FROM orders ORDER BY order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $page_title; ?> - Artisan Pastry Shop
    </title>
    <link rel="icon"
        href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🥐</text></svg>">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <nav>
        <div class="container">
            <a href="../index.php" class="logo">Pâtisserie Admin</a>
            <ul class="nav-links">
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 style="font-family: 'Playfair Display', serif; color: var(--deep-brown);">Order Management</h1>
            <p style="color: var(--warm-brown);">Manage customer orders and update their status</p>
        </div>

        <!-- Orders Table -->
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($orders_result) > 0): ?>
                        <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                            <tr>
                                <td>#
                                    <?php echo $order['id']; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($order['customer_name']); ?>
                                </td>
                                <td>
                                    <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?>
                                </td>
                                <td>₱
                                    <?php echo number_format($order['total_amount'], 2); ?>
                                </td>
                                <td>
                                    <select class="filter-select"
                                        onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                                        <option value="Pending" <?php echo $order['status'] === 'Pending' ? 'selected' : ''; ?>>
                                            Pending</option>
                                        <option value="Processing" <?php echo $order['status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="Completed" <?php echo $order['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Cancelled" <?php echo $order['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="action-btn edit" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">View
                                        Details</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">No orders yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="font-family: 'Playfair Display', serif;">Order Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="orderDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>

    <script>
        function updateOrderStatus(orderId, newStatus) {
            fetch('order_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=update_status&order_id=' + orderId + '&status=' + encodeURIComponent(newStatus)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order status updated successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function viewOrderDetails(orderId) {
            fetch('order_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_details&order_id=' + orderId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div class="cart-container">';
                        html += '<h3>Order #' + data.order.id + '</h3>';
                        html += '<p><strong>Customer:</strong> ' + data.order.customer_name + '</p>';
                        html += '<p><strong>Date:</strong> ' + data.order.order_date + '</p>';
                        html += '<p><strong>Delivery Address:</strong> ' + (data.order.delivery_address || 'Not provided') + '</p>';
                        html += '<p><strong>Status:</strong> <span class="status-badge status-' + data.order.status.toLowerCase() + '">' + data.order.status + '</span></p>';
                        html += '<h4 style="margin-top: 1.5rem;">Items:</h4>';

                        data.items.forEach(item => {
                            html += '<div class="cart-item">';
                            html += '<div class="cart-item-details">';
                            html += '<h4>' + item.pastry_name + '</h4>';
                            html += '<p>Quantity: ' + item.quantity + ' × ₱' + parseFloat(item.price).toFixed(2) + '</p>';
                            html += '</div>';
                            html += '<div class="cart-item-price">₱' + parseFloat(item.subtotal).toFixed(2) + '</div>';
                            html += '</div>';
                        });

                        html += '<div class="cart-total">Total: ₱' + parseFloat(data.order.total_amount).toFixed(2) + '</div>';
                        html += '</div>';

                        document.getElementById('orderDetailsContent').innerHTML = html;
                        document.getElementById('orderDetailsModal').classList.add('active');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function closeModal() {
            document.getElementById('orderDetailsModal').classList.remove('active');
        }
    </script>
</body>

</html>