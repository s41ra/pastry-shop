<?php
session_start();
$page_title = 'Inventory Management';

require_once '../includes/config.php';
require_once '../includes/check_admin_session.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Build query
$query = "SELECT * FROM pastries WHERE 1=1";
if ($search) {
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($category) {
    $query .= " AND category = '$category'";
}

// Apply sorting
if ($sort === 'price_asc') {
    $query .= " ORDER BY price ASC";
} elseif ($sort === 'price_desc') {
    $query .= " ORDER BY price DESC";
} else {
    $query .= " ORDER BY name ASC";
}

$pastries_result = mysqli_query($conn, $query);

// Get categories
$categories_query = "SELECT DISTINCT category FROM pastries ORDER BY category";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Artisan Pastry Shop</title>
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
            <h1 style="font-family: 'Playfair Display', serif; color: var(--deep-brown);">Inventory Management</h1>
            <button class="btn" onclick="showAddModal()">+ Add New Pastry</button>
        </div>

        <!-- Search and Filter -->
        <div class="search-filter-bar">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search pastries..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    onkeyup="if(event.key === 'Enter') filterInventory()">
                <p style="font-size: 0.8rem; color: var(--warm-brown); margin-top: 5px;">Press Enter to search</p>
            </div>
            <select class="filter-select" id="categoryFilter" onchange="filterInventory()">
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
        <select class="filter-select" id="sortFilter" onchange="filterInventory()">
            <option value="">Default (Name)</option>
            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
        </select>
        <?php if ($search || $category || $sort): ?>
            <a href="inventory.php" class="btn-clear">
                <span>&times;</span> Clear Filters
            </a>
        <?php endif; ?>
    </div>

        <!-- Inventory Table -->
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pastry = mysqli_fetch_assoc($pastries_result)): ?>
                        <tr>
                            <td><?php echo $pastry['id']; ?></td>
                            <td><?php echo htmlspecialchars($pastry['name']); ?></td>
                            <td><?php echo htmlspecialchars($pastry['category']); ?></td>
                            <td>₱<?php echo number_format($pastry['price'], 2); ?></td>
                            <td><?php echo $pastry['stock_quantity']; ?></td>
                            <td>
                                <button class="action-btn edit"
                                    onclick='editPastry(<?php echo json_encode($pastry); ?>)'>Edit</button>
                                <button class="action-btn delete"
                                    onclick="deletePastry(<?php echo $pastry['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="pastryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle" style="font-family: 'Playfair Display', serif;">Add New Pastry</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="pastryForm">
                <input type="hidden" id="pastryId" name="id">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" list="categoryList" required
                        placeholder="Type a category or select from suggestions"
                        style="width: 100%; padding: 0.8rem; border: 1px solid var(--crust); border-radius: 8px;">
                    <datalist id="categoryList">
                        <option value="Croissants">
                        <option value="Tarts">
                        <option value="Muffins">
                        <option value="Rolls">
                        <option value="Danish">
                        <option value="Macarons">
                        <option value="Cupcakes">
                        <option value="Eclairs">
                        <option value="Donuts">
                    </datalist>
                </div>
                <div class="form-group">
                    <label for="image">Pastry Image</label>
                    <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                    <input type="hidden" id="current_image" name="current_image">
                    <div id="imagePreview" style="margin-top: 10px; display: none;">
                        <img id="previewImg" src="" alt="Preview"
                            style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid var(--crust);">
                    </div>
                </div>
                <div class="form-group">
                    <label for="stock_quantity">Stock Quantity</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Save</button>
            </form>
        </div>
    </div>

    <script>
        let editMode = false;

        function showAddModal() {
            editMode = false;
            document.getElementById('modalTitle').textContent = 'Add New Pastry';
            document.getElementById('pastryForm').reset();
            document.getElementById('pastryId').value = '';
            document.getElementById('current_image').value = '';
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('image').required = true;
            document.getElementById('pastryModal').classList.add('active');
        }

        function editPastry(pastry) {
            editMode = true;
            document.getElementById('modalTitle').textContent = 'Edit Pastry';
            document.getElementById('pastryId').value = pastry.id;
            document.getElementById('name').value = pastry.name;
            document.getElementById('description').value = pastry.description;
            document.getElementById('price').value = pastry.price;
            document.getElementById('category').value = pastry.category;
            document.getElementById('current_image').value = pastry.image_url;
            document.getElementById('stock_quantity').value = pastry.stock_quantity;
            document.getElementById('image').required = false;

            // Show current image
            if (pastry.image_url) {
                document.getElementById('previewImg').src = '../' + pastry.image_url;
                document.getElementById('imagePreview').style.display = 'block';
            }

            document.getElementById('pastryModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('pastryModal').classList.remove('active');
        }

        function deletePastry(id) {
            if (!confirm('Are you sure you want to delete this pastry?')) return;

            fetch('inventory_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete&id=' + id
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pastry deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        document.getElementById('pastryForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', editMode ? 'update' : 'add');

            fetch('inventory_actions.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(editMode ? 'Pastry updated successfully!' : 'Pastry added successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });

        function filterInventory() {
            const search = document.getElementById('searchInput').value;
            const category = document.getElementById('categoryFilter').value;
            const sort = document.getElementById('sortFilter').value;
            window.location.href = '?search=' + encodeURIComponent(search) + '&category=' + encodeURIComponent(category) + '&sort=' + encodeURIComponent(sort);
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>

</html>