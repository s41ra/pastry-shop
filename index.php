<?php
$page_title = 'Home';
require_once 'includes/config.php';

// Smart Redirects: Redirect logged-in users to their dashboards
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/admin_dashboard.php');
    exit;
}
if (isset($_SESSION['customer_id'])) {
    header('Location: customer_dashboard.php');
    exit;
}

require_once 'includes/header.php';

// Fetch featured pastries
$query = "SELECT * FROM pastries WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 6";
$result = mysqli_query($conn, $query);
?>

<section class="hero">
    <div class="container">
        <h1>Freshly Baked Daily</h1>
        <p>Artisan pastries crafted with passion, tradition, and the finest ingredients</p>
        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="customer_dashboard.php" class="btn">View Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
                <a href="signup.php" class="btn btn-secondary">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <h2>Featured Pastries</h2>
        <div class="pastry-grid">
            <?php while ($pastry = mysqli_fetch_assoc($result)): ?>
                <div class="pastry-card fade-in-up">
                    <img src="<?php echo htmlspecialchars($pastry['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($pastry['name']); ?>" class="pastry-image" loading="lazy">
                    <div class="pastry-info">
                        <h3><?php echo htmlspecialchars($pastry['name']); ?></h3>
                        <p><?php echo htmlspecialchars($pastry['description']); ?></p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="pastry-price">₱<?php echo number_format($pastry['price'], 2); ?></span>
                            <?php if (isset($_SESSION['customer_id'])): ?>
                                <button class="btn" style="padding: 0.6rem 1.5rem; font-size: 0.9rem;"
                                    onclick="window.location.href='customer_dashboard.php'">Order Now</button>
                            <?php else: ?>
                                <button class="btn" style="padding: 0.6rem 1.5rem; font-size: 0.9rem;"
                                    onclick="alert('Please login to place an order'); window.location.href='login.php'">Order
                                    Now</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section style="background: linear-gradient(135deg, var(--butter) 0%, var(--crust) 100%); margin-top: 4rem;">
    <div class="container" style="text-align: center;">
        <h2>Why Choose Our Pastries?</h2>
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 2rem;">
            <div style="padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        style="color: var(--warm-brown);">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 0.5rem;">Fresh
                    Ingredients</h3>
                <p>Only the finest, locally-sourced ingredients make it into our pastries</p>
            </div>
            <div style="padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        style="color: var(--warm-brown);">
                        <path
                            d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z" />
                        <line x1="6" y1="17" x2="18" y2="17" />
                    </svg>
                </div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 0.5rem;">Master
                    Bakers</h3>
                <p>Crafted by experienced artisans with decades of expertise</p>
            </div>
            <div style="padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor"
                        style="color: var(--raspberry);">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                </div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 0.5rem;">Made with
                    Love</h3>
                <p>Every pastry is baked with care and attention to detail</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>