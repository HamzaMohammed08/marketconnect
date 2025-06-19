<?php
/**
 * Seller Profile Page
 *
 * Displays seller information, ratings, and products.
 */

// Include database connection
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

// Check if seller ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_message("Seller ID is required", "danger");
    header("Location: browse.php");
    exit;
}

$seller_id = (int)$_GET['id'];

// Get seller details
$seller_query = "SELECT id, name, email, created_at FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $seller_query);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if seller exists
if (mysqli_num_rows($result) == 0) {
    set_message("Seller not found", "danger");
    header("Location: browse.php");
    exit;
}

$seller = mysqli_fetch_assoc($result);

// Get reviews for products sold by this seller (since we don't have seller_reviews table in simplified DB)
$reviews_query = "SELECT r.*, u.name as reviewer_name, p.title as product_title
                 FROM reviews r
                 JOIN users u ON r.user_id = u.id
                 JOIN products p ON r.product_id = p.id
                 WHERE p.seller_id = ?
                 ORDER BY r.created_at DESC";
$stmt = mysqli_prepare($conn, $reviews_query);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$reviews_result = mysqli_stmt_get_result($stmt);

// Calculate average rating
$avg_rating = 0;
$total_reviews = mysqli_num_rows($reviews_result);
if ($total_reviews > 0) {
    $rating_sum = 0;
    mysqli_data_seek($reviews_result, 0); // Reset result pointer
    while ($review = mysqli_fetch_assoc($reviews_result)) {
        $rating_sum += $review['rating'];
    }
    $avg_rating = $rating_sum / $total_reviews;
    mysqli_data_seek($reviews_result, 0); // Reset result pointer again
}

// Get seller's products
$products_query = "SELECT p.*, c.name as category_name
                  FROM products p
                  JOIN categories c ON p.category_id = c.id
                  WHERE p.seller_id = ? AND p.status = 'approved'
                  ORDER BY p.created_at DESC";
$stmt = mysqli_prepare($conn, $products_query);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$products_result = mysqli_stmt_get_result($stmt);

// Include header
include_once '../includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="browse.php">Browse</a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($seller['name']); ?></li>
    </ol>
</nav>

<div class="container mt-4">
    <div class="row">
        <!-- Seller Info -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Seller Profile</h4>
                </div>
                <div class="card-body">
                    <h3><?php echo htmlspecialchars($seller['name']); ?></h3>
                    <p class="text-muted">Member since <?php echo date('F Y', strtotime($seller['created_at'])); ?></p>

                    <?php if ($total_reviews > 0): ?>
                        <div class="mb-3">
                            <div class="star-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= round($avg_rating)): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php else: ?>
                                        <i class="far fa-star text-warning"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span class="ms-2"><?php echo number_format($avg_rating, 1); ?> (<?php echo $total_reviews; ?> reviews)</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No reviews yet</p>
                    <?php endif; ?>

                    <?php if (is_logged_in() && $_SESSION['user_id'] != $seller_id): ?>
                        <div class="d-grid gap-2">
                            <a href="messages.php?user=<?php echo $seller_id; ?>" class="btn btn-primary">
                                <i class="fas fa-envelope me-2"></i> Contact Seller
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Reviews by Customers -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Customer Reviews</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong><?php echo htmlspecialchars($review['reviewer_name']); ?></strong>
                                        <small class="text-muted ms-2">
                                            <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">Review for: <em><?php echo htmlspecialchars($review['product_title']); ?></em></small>
                                    </div>
                                    <div class="star-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <i class="fas fa-star text-warning"></i>
                                            <?php else: ?>
                                                <i class="far fa-star text-warning"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php if (!empty($review['comment'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No reviews yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Seller Products -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0">Products by <?php echo htmlspecialchars($seller['name']); ?></h4>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($products_result) > 0): ?>
                        <div class="row">
                            <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <img src="<?php echo !empty($product['image']) ? '../uploads/' . $product['image'] : '../assets/images/no-image.jpg'; ?>"
                                             class="card-img-top" alt="<?php echo htmlspecialchars($product['title']); ?>"
                                             style="height: 200px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                            <p class="card-text text-truncate"><?php echo htmlspecialchars($product['description']); ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-primary fw-bold"><?php echo format_currency($product['price']); ?></span>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-white">
                                            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary w-100">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted">This seller has no active listings</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
