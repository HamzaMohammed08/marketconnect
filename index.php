<?php
/**
 * MarketConnect - Main Home Page
 * This is the main landing page where users can see featured products
 * and get an overview of our awesome marketplace!
 * 
 * Features included:
 * - Cool hero section with background image
 * - Featured products display
 * - Responsive design that works on mobile too
 * - Categories and stats to impress visitors
 */

// Start session stuff - important for login/logout
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include our database and authentication files
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Function to handle database errors nicely
function handleDatabaseError($conn, $query) {
    echo '<div class="alert alert-danger" role="alert">';
    echo 'Oops! Something went wrong with the database: ' . mysqli_error($conn) . '<br>';
    echo 'Query that failed: ' . $query;
    echo '</div>';
}

// Initialize variables to prevent undefined variable errors
$products_result = false;
$categories_result = false;

try {
    // Get featured products to show on homepage
    // Only show approved and featured items
    $products_query = "SELECT p.id, p.title, p.price, 
                        SUBSTRING(p.description, 1, 100) as short_description,
                        u.name as seller_name, pi.image_path
                      FROM products p
                      JOIN users u ON p.seller_id = u.id
                      LEFT JOIN (
                          SELECT product_id, image_path
                          FROM product_images
                          WHERE display_order = 0
                      ) pi ON p.id = pi.product_id
                      WHERE p.status = 'approved' AND p.featured = 1
                      ORDER BY p.created_at DESC
                      LIMIT 4";

    $products_result = mysqli_query($conn, $products_query);

    if (!$products_result) {
        handleDatabaseError($conn, $products_query);
    }

    // Get main categories for navigation
    $categories_query = "SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY display_order ASC";
    $categories_result = mysqli_query($conn, $categories_query);

    if (!$categories_result) {
        handleDatabaseError($conn, $categories_query);
    }

} catch (Exception $e) {
    // Something went really wrong
    echo '<div class="alert alert-danger" role="alert">';
    echo 'Error: ' . $e->getMessage();
    echo '</div>';
}

// Include the header (navigation bar and HTML start)
include_once 'includes/header.php';
?>

<!-- Hero Banner Section -->
<section class="hero-section">
    <div class="hero-background">
        <div class="container-fluid">
            <div class="row align-items-center">
                <!-- Hero Content -->
                <div class="col-lg-6 hero-content">
                    <div class="hero-text">
                        <h1 class="hero-title">Welcome to MarketConnect!</h1>
                        <p class="hero-subtitle">The coolest place to buy and sell stuff online! Connect with people in your area and find amazing deals on everything you need.</p>
                        <div class="hero-buttons">
                            <?php if (!is_logged_in()): ?>
                                <a href="pages/register.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Join Us Today!
                                </a>
                                <a href="pages/browse.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-search me-2"></i>Check Out Items
                                </a>
                            <?php else: ?>
                                <a href="pages/browse.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-search me-2"></i>Find Cool Stuff
                                </a>
                                <a href="pages/sell.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-tag me-2"></i>Sell Your Stuff
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Statistics -->
                        <div class="hero-stats">
                            <div class="stat-item">
                                <div class="stat-number">250+</div>
                                <div class="stat-label">Users</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">500+</div>
                                <div class="stat-label">Items</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">Safe</div>
                                <div class="stat-label">Trading</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Image -->
                <div class="col-lg-6 hero-image-section">
                    <div class="hero-image-container">
                        <img src="assets/images/store-front.jpg" alt="Our Awesome Marketplace" class="hero-main-image">
                        
                        <!-- Floating Category Cards -->
                        <div class="floating-card card-1">
                            <div class="card-content">
                                <i class="fas fa-laptop"></i>
                                <span>Tech Stuff</span>
                            </div>
                        </div>
                        
                        <div class="floating-card card-2">
                            <div class="card-content">
                                <i class="fas fa-tshirt"></i>
                                <span>Clothes</span>
                            </div>
                        </div>
                        
                        <div class="floating-card card-3">
                            <div class="card-content">
                                <i class="fas fa-home"></i>
                                <span>Home Items</span>
                            </div>
                        </div>
                        
                        <div class="floating-card card-4">
                            <div class="card-content">
                                <i class="fas fa-gamepad"></i>
                                <span>Games</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Admin Controls Section - only admins can see this -->
<?php if (isset($_SESSION['role']) && $_SESSION['role'] == 3): ?>
<section class="admin-controls mb-5">
    <div class="container">
        <div class="card border-0 shadow">
            <div class="card-header bg-primary text-white">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-shield fa-2x me-3"></i>
                    <h5 class="mb-0 fw-bold">Admin Stuff - Only You Can See This!</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3 col-sm-6">
                        <a href="admin/products.php" class="text-decoration-none">
                            <div class="card h-100 text-center border-primary">
                                <div class="card-body">
                                    <i class="fas fa-box fa-2x mb-3 text-primary"></i>
                                    <h6>Manage Products</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="admin/users.php" class="text-decoration-none">
                            <div class="card h-100 text-center border-success">
                                <div class="card-body">
                                    <i class="fas fa-users fa-2x mb-3 text-success"></i>
                                    <h6>Manage Users</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="admin/categories.php" class="text-decoration-none">
                            <div class="card h-100 text-center border-warning">
                                <div class="card-body">
                                    <i class="fas fa-tags fa-2x mb-3 text-warning"></i>
                                    <h6>Manage Categories</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="admin/dashboard.php" class="admin-link">
                            <div class="hover-card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-tachometer-alt fa-2x mb-3"></i>
                                    <h6>Admin Dashboard</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories Section -->
<section class="categories-section mb-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold mb-2">Shop by Category</h2>
            <p class="lead text-muted">Explore our wide range of categories</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php if ($categories_result && mysqli_num_rows($categories_result) > 0): ?>
                <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="pages/browse.php?category_id=<?php echo $category['id']; ?>" 
                           class="text-decoration-none category-link">
                            <div class="category-card h-100">
                                <div class="card-body text-center">
                                    <div class="category-icon-wrapper">
                                        <i class="fas fa-layer-group fa-2x category-icon"></i>
                                    </div>
                                    <h5 class="card-title mb-0">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-light text-center">
                        <p class="mb-0">No categories available at the moment.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section - Show off our best stuff! -->
<section class="products-section mb-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold mb-2">⭐ Featured Products ⭐</h2>
            <p class="lead text-muted mb-0">Check out these awesome items that people are selling!</p>
            <a href="pages/browse.php" class="btn btn-primary mt-3">
                <i class="fas fa-th-large me-2"></i>See All Products
            </a>
        </div>

        <div class="row g-4">
            <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100">
                            <div class="position-relative">
                                <!-- Featured badge to make it look special -->
                                <div class="badge bg-warning position-absolute top-0 end-0 m-3">
                                    <i class="fas fa-star me-1"></i>Featured
                                </div>
                                <div class="product-img-container">
                                    <img src="<?php echo !empty($product['image_path']) ? $product['image_path'] : 'assets/images/no-image.jpg'; ?>"
                                         class="card-img-top" alt="<?php echo htmlspecialchars($product['title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                    <div class="quick-view-btn">
                                        <a href="pages/product_detail.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-light rounded-circle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <h5 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                <?php if (!empty($product['short_description'])): ?>
                                    <p class="product-desc">
                                        <?php echo htmlspecialchars(mb_strimwidth($product['short_description'], 0, 85, "...")); ?>
                                    </p>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="price-tag">R<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="seller-info"><?php echo htmlspecialchars($product['seller_name']); ?></span>
                                </div>
                            </div>

                            <div class="card-footer border-0">
                                <a href="pages/product_detail.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        No featured products available at the moment.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">How MarketConnect Works</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h4>Join Us</h4>
                        <p>Quick and free sign-up. Join our vibrant community of local buyers and sellers.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-camera fa-3x mb-3"></i>
                        <h4>List Items</h4>
                        <p>Share your items with great photos and fair prices that reflect our community values.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-handshake fa-3x mb-3"></i>
                        <h4>Connect</h4>
                        <p>Chat via WhatsApp, negotiate prices, and meet safely within our trusted community.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="pages/register.php" class="btn btn-primary btn-lg">Join Our Community</a>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section py-5">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Transform How You Buy and Sell?</h2>
        <p class="lead mb-4">Join thousands of people already using MarketConnect!</p>
        <?php if (!is_logged_in()): ?>
            <a href="pages/register.php" class="btn btn-warning btn-lg me-2">Create Account</a>
            <a href="pages/login.php" class="btn btn-outline-warning btn-lg">Sign In</a>
        <?php else: ?>
            <a href="pages/browse.php" class="btn btn-warning btn-lg me-2">Discover Products</a>
            <a href="pages/sell.php" class="btn btn-outline-warning btn-lg">Start Selling</a>
        <?php endif; ?>
    </div>
</section>

<?php
if ($products_result) {
    mysqli_free_result($products_result);
}
if ($categories_result) {
    mysqli_free_result($categories_result);
}
include_once 'includes/footer.php';
?>
