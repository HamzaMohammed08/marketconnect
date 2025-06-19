<?php
/**
 * Review Page
 *
 * Allows users to leave reviews for products and sellers.
 */

// Include database connection and authentication
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Require user to be logged in
require_login();

// Initialize variables
$errors = [];
$product = null;
$seller = null;
$review_type = '';
$item_id = 0;

// Check if product_id or seller_id is provided
if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
    $review_type = 'product';
    $item_id = (int)$_GET['product_id'];

    // Get product details
    $product_query = "SELECT p.*, u.id as seller_id, u.name as seller_name
                     FROM products p
                     JOIN users u ON p.seller_id = u.id
                     WHERE p.id = ? AND p.status = 'approved'";
    $stmt = mysqli_prepare($conn, $product_query);
    mysqli_stmt_bind_param($stmt, "i", $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);

        // Check if user is the seller (can't review own product)
        if ($product['seller_id'] == $_SESSION['user_id']) {
            set_message("You cannot review your own product", "danger");
            header("Location: product_detail.php?id=$item_id");
            exit;
        }

        // Check if user has already reviewed this product
        $check_query = "SELECT id FROM reviews WHERE product_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ii", $item_id, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            set_message("You have already reviewed this product", "warning");
            header("Location: product_detail.php?id=$item_id");
            exit;
        }
    } else {
        set_message("Product not found", "danger");
        header("Location: browse.php");
        exit;
    }
} elseif (isset($_GET['seller_id']) && !empty($_GET['seller_id'])) {
    $review_type = 'seller';
    $item_id = (int)$_GET['seller_id'];

    // Get seller details
    $seller_query = "SELECT id, name, email FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $seller_query);
    mysqli_stmt_bind_param($stmt, "i", $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $seller = mysqli_fetch_assoc($result);

        // Check if user is trying to review themselves
        if ($seller['id'] == $_SESSION['user_id']) {
            set_message("You cannot review yourself", "danger");
            header("Location: seller_profile.php?id=$item_id");
            exit;
        }

        // Check if user has already reviewed this seller
        $check_query = "SELECT id FROM seller_reviews WHERE seller_id = ? AND reviewer_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ii", $item_id, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            set_message("You have already reviewed this seller", "warning");
            header("Location: seller_profile.php?id=$item_id");
            exit;
        }
    } else {
        set_message("Seller not found", "danger");
        header("Location: index.php");
        exit;
    }
} else {
    set_message("Invalid request", "danger");
    header("Location: index.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = sanitize_input($_POST['comment']);
    $reviewer_id = $_SESSION['user_id'];

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $errors['rating'] = "Please select a rating between 1 and 5 stars";
    }

    // If no errors, insert review into database
    if (empty($errors)) {
        if ($review_type == 'product') {
            // Insert product review
            $insert_query = "INSERT INTO reviews (product_id, user_id, rating, comment)
                            VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "iiis", $item_id, $reviewer_id, $rating, $comment);

            if (mysqli_stmt_execute($stmt)) {
                set_message("Your review has been submitted successfully", "success");
                header("Location: product_detail.php?id=$item_id");
                exit;
            } else {
                $errors['db'] = "Failed to submit review: " . mysqli_error($conn);
            }
        } elseif ($review_type == 'seller') {
            // Insert seller review
            $insert_query = "INSERT INTO seller_reviews (seller_id, reviewer_id, rating, comment)
                            VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "iiis", $item_id, $reviewer_id, $rating, $comment);

            if (mysqli_stmt_execute($stmt)) {
                set_message("Your review has been submitted successfully", "success");
                header("Location: seller_profile.php?id=$item_id");
                exit;
            } else {
                $errors['db'] = "Failed to submit review: " . mysqli_error($conn);
            }
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <?php if ($review_type == 'product'): ?>
                        Write a Review for "<?php echo htmlspecialchars($product['title']); ?>"
                    <?php else: ?>
                        Write a Review for <?php echo htmlspecialchars($seller['name']); ?>
                    <?php endif; ?>
                </h4>
            </div>
            <div class="card-body">
                <?php if (isset($errors['db'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['db']; ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?' . ($review_type == 'product' ? 'product_id=' : 'seller_id=') . $item_id); ?>">
                    <!-- Rating Stars -->
                    <div class="mb-4">
                        <label class="form-label">Your Rating</label>
                        <div class="star-rating mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="far fa-star rating-star" data-value="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating_value" value="0">
                        <?php if (isset($errors['rating'])): ?>
                            <div class="text-danger"><?php echo $errors['rating']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Comment -->
                    <div class="mb-3">
                        <label for="comment" class="form-label">Your Review (Optional)</label>
                        <textarea class="form-control" id="comment" name="comment" rows="5"><?php echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>
                        <div class="form-text">Share your experience with this <?php echo $review_type; ?></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                        <a href="<?php echo $review_type == 'product' ? 'product_detail.php?id=' . $item_id : 'seller_profile.php?id=' . $item_id; ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Star rating functionality
    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('rating_value');

    stars.forEach(function(star) {
        // Click event
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            ratingInput.value = value;

            // Update visual stars
            stars.forEach(function(s) {
                const starValue = s.getAttribute('data-value');
                if (starValue <= value) {
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        });

        // Hover effects
        star.addEventListener('mouseenter', function() {
            const value = this.getAttribute('data-value');

            stars.forEach(function(s) {
                const starValue = s.getAttribute('data-value');
                if (starValue <= value) {
                    s.classList.add('text-warning');
                }
            });
        });

        star.addEventListener('mouseleave', function() {
            stars.forEach(function(s) {
                s.classList.remove('text-warning');
            });
        });
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>
