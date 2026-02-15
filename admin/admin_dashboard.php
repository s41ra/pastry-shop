<?php
session_start();
$page_title = 'Admin Dashboard';

require_once '../includes/config.php';
require_once '../includes/check_admin_session.php';

$admin_username = $_SESSION['admin_username'];

// Get statistics
$total_orders_query = "SELECT COUNT(*) as total FROM orders";
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, $total_orders_query))['total'];

$total_revenue_query = "SELECT SUM(total_amount) as revenue FROM orders WHERE status != 'Cancelled'";
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, $total_revenue_query))['revenue'] ?? 0;

$total_products_query = "SELECT COUNT(*) as total FROM pastries";
$total_products = mysqli_fetch_assoc(mysqli_query($conn, $total_products_query))['total'];

$pending_orders_query = "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'";
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, $pending_orders_query))['total'];

// Get recent orders
$recent_orders_query = "SELECT * FROM orders ORDER BY order_date DESC LIMIT 5";
$recent_orders = mysqli_query($conn, $recent_orders_query);
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
            <h1 style="font-family: 'Playfair Display', serif; color: var(--deep-brown);">
                Admin Dashboard
            </h1>
            <p style="color: var(--warm-brown);">Welcome back,
                <?php echo htmlspecialchars($admin_username); ?>!
            </p>
        </div>

        <!-- Statistics -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>
                    <?php echo $total_orders; ?>
                </h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3>₱
                    <?php echo number_format($total_revenue, 2); ?>
                </h3>
                <p>Total Revenue</p>
            </div>
            <div class="stat-card">
                <h3>
                    <?php echo $total_products; ?>
                </h3>
                <p>Products</p>
            </div>
            <div class="stat-card">
                <h3>
                    <?php echo $pending_orders; ?>
                </h3>
                <p>Pending Orders</p>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="cart-container">
            <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 1.5rem;">Recent Orders</h2>
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Delivery Address</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                            <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                                <tr>
                                    <td>#
                                        <?php echo $order['id']; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($order['customer_name']); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $address = $order['delivery_address'] ?? 'Not provided';
                                        echo htmlspecialchars(strlen($address) > 50 ? substr($address, 0, 50) . '...' : $address);
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
                                    </td>
                                    <td>₱
                                        <?php echo number_format($order['total_amount'], 2); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
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
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="orders.php" class="btn">View All Orders</a>
            </div>
        </div>
    </div>
</body>

</html>