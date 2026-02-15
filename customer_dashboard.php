<?php
$page_title = 'Customer Dashboard';

require_once 'includes/config.php';
require_once 'includes/check_customer_session.php';
require_once 'includes/header.php';

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

// Check for order success message
$order_success = isset($_GET['order_success']) ? true : false;

// Get cart count
$cart_count_query = "SELECT SUM(quantity) as total FROM cart WHERE customer_id = $customer_id";
$cart_count_result = mysqli_query($conn, $cart_count_query);
$cart_count = mysqli_fetch_assoc($cart_count_result)['total'] ?? 0;

// Get search and filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Build query for pastries
$pastries_query = "SELECT * FROM pastries WHERE 1=1";
if ($search) {
    $pastries_query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($category) {
    $pastries_query .= " AND category = '$category'";
}

// Apply sorting
if ($sort === 'price_asc') {
    $pastries_query .= " ORDER BY price ASC";
} elseif ($sort === 'price_desc') {
    $pastries_query .= " ORDER BY price DESC";
} else {
    $pastries_query .= " ORDER BY name ASC";
}

$pastries_result = mysqli_query($conn, $pastries_query);

// Get categories
$categories_query = "SELECT DISTINCT category FROM pastries ORDER BY category";
$categories_result = mysqli_query($conn, $categories_query);
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 style="font-family: 'Playfair Display', serif; color: var(--deep-brown);">
            Welcome,
            <?php echo htmlspecialchars($customer_name); ?>!
        </h1>
        <div style="display: flex; gap: 1rem;">
            <button class="btn" onclick="openCart()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="display: inline-block; vertical-align: middle; margin-right: 0.5rem;">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                </svg>
                Cart (<?php echo $cart_count; ?>)
            </button>
            <button class="btn btn-secondary" onclick="document.getElementById('ordersModal').classList.add('active')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="display: inline-block; vertical-align: middle; margin-right: 0.5rem;">
                    <path
                        d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                    <line x1="12" y1="22.08" x2="12" y2="12" />
                </svg>
                My Orders
            </button>
        </div>
    </div>

    <?php if ($order_success): ?>
        <div class="alert alert-success">Order placed successfully! Check "My Orders" to track your order.</div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="search-filter-bar">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search pastries..."
                value="<?php echo htmlspecialchars($search); ?>" onkeyup="if(event.key === 'Enter') filterPastries()">
            <p style="font-size: 0.8rem; color: var(--warm-brown); margin-top: 5px;">Press Enter to search</p>
        </div>
        <select class="filter-select" id="categoryFilter" onchange="filterPastries()">
            <option value="">All Categories</option>
            <?php
            $standard_categories = ['Croissants', 'Tarts', 'Muffins', 'Rolls', 'Danish', 'Macarons', 'Cupcakes', 'Eclairs', 'Donuts'];
            foreach ($standard_categories as $cat_name):
                ?>
                <option value="<?php echo $cat_name; ?>" <?php echo $category === $cat_name ? 'selected' : ''; ?>>
                    <?php echo $cat_name; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="filter-select" id="sortFilter" onchange="filterPastries()">
            <option value="">Default (Name)</option>
            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
        </select>
        <?php if ($search || $category || $sort): ?>
            <a href="customer_dashboard.php" class="btn-clear">
                <span>&times;</span> Clear Filters
            </a>
        <?php endif; ?>
    </div>

    <!-- Pastries Grid -->
    <div class="pastry-grid">
        <?php if (mysqli_num_rows($pastries_result) > 0): ?>
            <?php while ($pastry = mysqli_fetch_assoc($pastries_result)): ?>
                <div class="pastry-card">
                    <img src="<?php echo htmlspecialchars($pastry['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($pastry['name']); ?>" class="pastry-image" loading="lazy">
                    <div class="pastry-info">
                        <h3>
                            <?php echo htmlspecialchars($pastry['name']); ?>
                        </h3>
                        <p>
                            <?php echo htmlspecialchars($pastry['description']); ?>
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                            <span class="pastry-price">₱
                                <?php echo number_format($pastry['price'], 2); ?>
                            </span>
                            <button class="btn" onclick="addToCart(<?php echo $pastry['id']; ?>)">Add to Cart</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div
                style="grid-column: 1 / -1; text-align: center; padding: 4rem; background: white; border-radius: 15px; box-shadow: 0 5px 20px var(--shadow);">
                <span style="font-size: 4rem; display: block; margin-bottom: 1rem;">🥐</span>
                <h3 style="font-family: 'Playfair Display', serif; color: var(--deep-brown);">No pastries found</h3>
                <p style="color: var(--warm-brown);">Try adjusting your search or category filter.</p>
                <button class="btn" style="margin-top: 1.5rem;" onclick="window.location.href='customer_dashboard.php'">View
                    All Pastries</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cart Modal -->
<div id="cartModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 style="font-family: 'Playfair Display', serif;">Shopping Cart</h2>
            <button class="modal-close"
                onclick="document.getElementById('cartModal').classList.remove('active')">&times;</button>
        </div>
        <div id="cartContent">
            <!-- Cart items loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Orders Modal -->
<div id="ordersModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 style="font-family: 'Playfair Display', serif;">My Orders</h2>
            <button class="modal-close"
                onclick="document.getElementById('ordersModal').classList.remove('active')">&times;</button>
        </div>
        <div id="ordersContent">
            <?php
            $orders_query = "SELECT * FROM orders WHERE customer_id = $customer_id ORDER BY order_date DESC";
            $orders_result = mysqli_query($conn, $orders_query);

            if (mysqli_num_rows($orders_result) > 0):
                ?>
                <div class="cart-container">
                    <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                        <div class="cart-item">
                            <div class="cart-item-details">
                                <h4>Order #
                                    <?php echo $order['id']; ?>
                                </h4>
                                <p>Date:
                                    <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?>
                                </p>
                                <p>Total: ₱
                                    <?php echo number_format($order['total_amount'], 2); ?>
                                </p>
                            </div>
                            <div>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; padding: 2rem;">No orders yet. Start shopping!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function addToCart(pastryId) {
        fetch('cart_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=add&pastry_id=' + pastryId
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Added to cart!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }

    function loadCart() {
        fetch('cart_actions.php?action=get_cart')
            .then(response => response.json())
            .then(data => {
                let html = '<div class="cart-container">';
                let total = 0;

                if (data.items && data.items.length > 0) {
                    data.items.forEach(item => {
                        const subtotal = item.price * item.quantity;
                        total += subtotal;
                        html += `
                    <div class="cart-item">
                        <div class="cart-item-details">
                            <h4>${item.name}</h4>
                            <p>₱${parseFloat(item.price).toFixed(2)} each</p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div class="quantity-controls">
                                <button onclick="updateQuantity(${item.cart_id}, -1)">-</button>
                                <span>${item.quantity}</span>
                                <button onclick="updateQuantity(${item.cart_id}, 1)">+</button>
                            </div>
                            <div class="cart-item-price">₱${subtotal.toFixed(2)}</div>
                            <button class="action-btn delete" onclick="removeFromCart(${item.cart_id})">Remove</button>
                        </div>
                    </div>
                `;
                    });
                    html += `<div class="cart-total">Total: ₱${total.toFixed(2)}</div>`;
                    html += '<button class="btn" style="width: 100%; margin-top: 1rem;" onclick="window.location.href=\'checkout.php\'">Proceed to Checkout</button>';
                } else {
                    html += '<p style="text-align: center; padding: 2rem;">Your cart is empty</p>';
                }
                html += '</div>';
                document.getElementById('cartContent').innerHTML = html;
            });
    }

    function updateQuantity(cartId, change) {
        const currentQty = parseInt(event.target.parentElement.querySelector('span').textContent);
        const newQty = currentQty + change;

        if (newQty < 1) return;

        fetch('cart_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update&cart_id=${cartId}&quantity=${newQty}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCart();
                }
            });
    }

    function removeFromCart(cartId) {
        if (!confirm('Remove this item from cart?')) return;

        fetch('cart_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=remove&cart_id=' + cartId
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCart();
                    location.reload();
                }
            });
    }

    function filterPastries() {
        const search = document.getElementById('searchInput').value;
        const category = document.getElementById('categoryFilter').value;
        const sort = document.getElementById('sortFilter').value;
        window.location.href = '?search=' + encodeURIComponent(search) + '&category=' + encodeURIComponent(category) + '&sort=' + encodeURIComponent(sort);
    }

    // Function to open cart and load items immediately
    function openCart() {
        document.getElementById('cartModal').classList.add('active');
        loadCart();
    }

    // Load cart when cart modal is opened
    document.getElementById('cartModal').addEventListener('click', function (e) {
        if (e.target === this || e.target.classList.contains('btn')) {
            loadCart();
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>