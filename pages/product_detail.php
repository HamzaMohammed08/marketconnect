<?php
// Product Detail Page
// Simple product display with functionality

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: browse.php");
    exit;
}

$product_id = (int)$_GET['id'];

// Get product details
$product_query = "SELECT p.*, c.name as category_name, u.name as seller_name, u.id as seller_id
                 FROM products p
                 JOIN categories c ON p.category_id = c.id
                 JOIN users u ON p.seller_id = u.id
                 WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $product_query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: browse.php");
    exit;
}

$product = mysqli_fetch_assoc($result);

// --- INCREMENT PRODUCT VIEW COUNT ---
// This feature tracks how many times a product page is viewed
// It helps sellers understand how popular their products are
// We only count views from users who are NOT the seller (to prevent self-inflation)
// This is a simple but effective analytics feature for  the marketplace
if (is_logged_in() && $_SESSION['user_id'] != $product['seller_id']) {
    // Only increment view count if the viewer is not the seller
    // This prevents sellers from artificially inflating their own view counts
    $update_views_query = "UPDATE products SET views = views + 1 WHERE id = ?";
    $stmt_views = mysqli_prepare($conn, $update_views_query);
    mysqli_stmt_bind_param($stmt_views, "i", $product_id);
    mysqli_stmt_execute($stmt_views);
    mysqli_stmt_close($stmt_views);
} elseif (!is_logged_in()) {
    // Also count views from non-logged-in users (anonymous visitors)
    // This gives sellers a more complete picture of interest in their products
    $update_views_query = "UPDATE products SET views = views + 1 WHERE id = ?";
    $stmt_views = mysqli_prepare($conn, $update_views_query);
    mysqli_stmt_bind_param($stmt_views, "i", $product_id);
    mysqli_stmt_execute($stmt_views);
    mysqli_stmt_close($stmt_views);
}
// --- END VIEW COUNT TRACKING ---

// Get first product image
$image_query = "SELECT image_path FROM product_images WHERE product_id = ? ORDER BY display_order ASC LIMIT 1";
$stmt = mysqli_prepare($conn, $image_query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$image_result = mysqli_stmt_get_result($stmt);
$product_image = mysqli_fetch_assoc($image_result);

// Get product reviews
$reviews_query = "SELECT r.*, u.name as reviewer_name
                 FROM reviews r
                 JOIN users u ON r.user_id = u.id
                 WHERE r.product_id = ?
                 ORDER BY r.created_at DESC";
$stmt = mysqli_prepare($conn, $reviews_query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$reviews_result = mysqli_stmt_get_result($stmt);

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
    
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $product['seller_id'];
    
    if (!empty($subject) && !empty($message)) {
        $insert_query = "INSERT INTO messages (sender_id, receiver_id, product_id, subject, message)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "iiiss", $sender_id, $receiver_id, $product_id, $subject, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='alert alert-success'>Message sent successfully!</div>";
        }
    }
}

include_once '../includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="browse.php">Browse</a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['title']); ?></li>
    </ol>
</nav>

<div class="container mt-4">
    <div class="row">
        <!-- Product Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Product Image -->
                        <div class="col-md-6">
                            <img src="<?php echo !empty($product_image['image_path']) ? '../' . $product_image['image_path'] : '../assets/images/no-image.jpg'; ?>"
                                 class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['title']); ?>"
                                 style="height: 300px; object-fit: cover; width: 100%;">
                        </div>
                        
                        <!-- Product Info -->
                        <div class="col-md-6">
                            <h1><?php echo htmlspecialchars($product['title']); ?></h1>
                            <p class="text-muted"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            
                            <h2 class="text-primary"><?php echo format_currency($product['price']); ?></h2>
                            
                            <p><strong>Condition:</strong> <?php echo ucfirst($product['condition_status']); ?></p>
                            <p><strong>Stock:</strong> <?php echo $product['stock']; ?> available</p>
                            <p><strong>Seller:</strong> <?php echo htmlspecialchars($product['seller_name']); ?></p>
                            
                            <?php if (is_logged_in() && $_SESSION['user_id'] != $product['seller_id']): ?>
                                <!-- Add to Cart Form -->
                                <form method="POST" action="add_to_cart.php" class="mb-3">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <div class="row">
                                        <div class="col-4">
                                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                        </div>
                                        <div class="col-8">
                                            <button type="submit" class="btn btn-success w-100">Add to Cart</button>
                                        </div>
                                    </div>
                                </form>
                                
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#contactModal">
                                    Contact Seller
                                </button>
                            <?php elseif (is_logged_in() && $_SESSION['user_id'] == $product['seller_id']): ?>
                                <!-- Owner actions -->
                                <!-- SELLER ANALYTICS DISPLAY -->
                                <!-- Show view count to the product owner for their reference -->
                                <div class="alert alert-info mb-3">
                                    <h6 class="mb-1"><i class="fas fa-chart-line me-2"></i>Product Analytics</h6>
                                    <p class="mb-0">
                                        <i class="fas fa-eye me-1"></i>
                                        <strong><?php echo number_format($product['views']); ?></strong> total views
                                        <small class="text-muted d-block">This shows how many people have viewed your product page</small>
                                    </p>
                                </div>
                                <!-- END SELLER ANALYTICS -->
                                <div class="d-grid gap-2">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-edit me-2"></i> Edit Product
                                    </a>
                                    <a href="dashboard.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                                    </a>
                                </div>
                            <?php elseif (!is_logged_in()): ?>
                                <a href="login.php" class="btn btn-success w-100">Login to Purchase</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="card mt-4">
                <div class="card-header">
                    <h4>Description</h4>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
            </div>
            
            <!-- Reviews -->
            <div class="card mt-4">
                <div class="card-header">
                    <h4>Reviews</h4>
                </div>
                <div class="card-body">
                    <?php if (is_logged_in() && $_SESSION['user_id'] != $product['seller_id']): ?>
                        <a href="review.php?product_id=<?php echo $product_id; ?>" class="btn btn-outline-primary mb-3">
                            Write a Review
                        </a>
                    <?php endif; ?>
                    
                    <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($review['reviewer_name']); ?></strong>
                                    <div>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <i class="fas fa-star text-warning"></i>
                                            <?php else: ?>
                                                <i class="far fa-star text-warning"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                <?php if (!empty($review['comment'])): ?>
                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">No reviews yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Seller Info -->
            <div class="card">
                <div class="card-header">
                    <h5>Seller Information</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo htmlspecialchars($product['seller_name']); ?></h6>
                    <p class="text-muted">Member since <?php echo date('F Y', strtotime($product['created_at'])); ?></p>
                    <a href="seller_profile.php?id=<?php echo $product['seller_id']; ?>" class="btn btn-outline-primary">
                        View Seller Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Seller Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Contact Seller</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" 
                               value="Inquiry about: <?php echo htmlspecialchars($product['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
