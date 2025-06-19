<?php
// Include authentication and database functions
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

// Ensure user is logged in
require_login();
$user_id = $_SESSION['user_id'];

// Handle request cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_request'])) {
    $request_id = (int)$_POST['request_id'];
    
    // Verify this request belongs to the current user and is cancellable
    $verify_sql = "SELECT id FROM purchase_requests WHERE id = ? AND buyer_id = ? AND status = 'pending'";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $request_id, $user_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->get_result()->num_rows > 0) {
        $cancel_sql = "UPDATE purchase_requests SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $cancel_stmt = $conn->prepare($cancel_sql);
        $cancel_stmt->bind_param("i", $request_id);
        
        if ($cancel_stmt->execute()) {
            $_SESSION['message'] = "Purchase request cancelled successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error cancelling request. Please try again.";
            $_SESSION['message_type'] = "danger";
        }
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get purchase requests for this buyer
$sql = "SELECT pr.*, p.title as product_title, p.price as product_price, 
        pi.image_path as product_image, seller.name as seller_name, seller.email as seller_email,
        seller.phone as seller_phone
        FROM purchase_requests pr
        JOIN products p ON pr.product_id = p.id
        LEFT JOIN product_images pi ON pr.product_id = pi.product_id AND pi.display_order = (
            SELECT MIN(display_order) FROM product_images WHERE product_id = pr.product_id
        )
        JOIN users seller ON pr.seller_id = seller.id
        WHERE pr.buyer_id = ?
        ORDER BY pr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requests = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">📋 My Purchase Requests</h2>
                <div>
                    <a href="browse.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Browse Products
                    </a>
                </div>
            </div>
            
            <?php if ($requests->num_rows > 0): ?>
                <?php while ($request = $requests->fetch_assoc()): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <?php if ($request['product_image']): ?>
                                        <img src="../<?php echo htmlspecialchars($request['product_image']); ?>" 
                                             class="img-fluid rounded" alt="Product Image">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <span class="text-muted">No Image Available</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-9">
                                    <h5 class="card-title mb-3"><?php echo htmlspecialchars($request['product_title']); ?></h5>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Seller:</strong> <?php echo htmlspecialchars($request['seller_name']); ?></p>
                                            <p class="mb-1"><strong>Total Amount:</strong> R<?php echo number_format($request['total_amount'], 2); ?></p>
                                            <p class="mb-1"><strong>Quantity:</strong> <?php echo $request['quantity']; ?></p>
                                            <p class="mb-1">
                                                <strong>Status:</strong> 
                                                <span class="badge bg-<?php 
                                                    echo $request['status'] === 'pending' ? 'warning' : 
                                                        ($request['status'] === 'completed' ? 'success' : 
                                                        ($request['status'] === 'accepted' ? 'info' : 
                                                        ($request['status'] === 'rejected' ? 'danger' : 'secondary'))); 
                                                ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <button type="submit" name="cancel_request" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to cancel this request?')">
                                                    Cancel Request
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <a href="messages.php?compose=1&seller_id=<?php echo $request['seller_id']; ?>&product_id=<?php echo $request['product_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-envelope"></i> Contact Seller
                                        </a>
                                        
                                        <a href="product_detail.php?id=<?php echo $request['product_id']; ?>" 
                                           class="btn btn-sm btn-outline-info">
                                            View Product
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            Request created: <?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?>
                            <?php if ($request['updated_at'] !== $request['created_at']): ?>
                                | Last updated: <?php echo date('M d, Y H:i', strtotime($request['updated_at'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <h5>No Purchase Requests</h5>
                    <p class="mb-0">You haven't made any purchase requests yet. Browse products and use the "Request Purchase" option to get in touch with sellers.</p>
                    <a href="browse.php" class="btn btn-primary mt-3">Browse Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
