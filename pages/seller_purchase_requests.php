<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

// Ensure user is logged in
require_login();
$user_id = $_SESSION['user_id'];

// Handle status updates
if ($_POST && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];
    $seller_response = isset($_POST['seller_response']) ? trim($_POST['seller_response']) : '';
    
    // Verify this request belongs to the current seller
    $verify_sql = "SELECT id FROM purchase_requests WHERE id = ? AND seller_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $request_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        if ($action === 'accept') {
            $update_sql = "UPDATE purchase_requests SET status = 'accepted', seller_response = ?, updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $seller_response, $request_id);
            if ($update_stmt->execute()) {
                $success_message = "Purchase request accepted successfully!";
            }
        } elseif ($action === 'reject') {
            $update_sql = "UPDATE purchase_requests SET status = 'rejected', seller_response = ?, updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $seller_response, $request_id);
            if ($update_stmt->execute()) {
                $success_message = "Purchase request rejected.";
            }
        } elseif ($action === 'complete') {
            $update_sql = "UPDATE purchase_requests SET status = 'completed', updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $request_id);
            if ($update_stmt->execute()) {
                $success_message = "Purchase request marked as completed!";
            }
        }
    }
}

// Get purchase requests for this seller's products
$sql = "SELECT pr.*, p.title as product_title, p.price as product_price, 
        pi.image_path as product_image, seller.name as seller_name, seller.email as seller_email,
        buyer.name as buyer_name, buyer.email as buyer_email, buyer.phone as buyer_phone
        FROM purchase_requests pr
        JOIN products p ON pr.product_id = p.id
        LEFT JOIN product_images pi ON pr.product_id = pi.product_id AND pi.display_order = (
            SELECT MIN(display_order) FROM product_images WHERE product_id = pr.product_id
        )
        JOIN users seller ON pr.seller_id = seller.id
        JOIN users buyer ON pr.buyer_id = buyer.id
        WHERE pr.seller_id = ?
        ORDER BY 
            CASE pr.status 
                WHEN 'pending' THEN 1 
                WHEN 'accepted' THEN 2 
                WHEN 'completed' THEN 3 
                WHEN 'rejected' THEN 4 
                WHEN 'cancelled' THEN 5 
            END,
            pr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requests = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Purchase Requests for My Products</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($requests->num_rows > 0): ?>
                <?php while ($request = $requests->fetch_assoc()): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <?php if ($request['product_image']): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($request['product_image']); ?>" 
                                             class="img-fluid rounded" alt="Product Image">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <span class="text-muted">Product Image</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-9">
                                    <h5 class="card-title"><?php echo htmlspecialchars($request['product_title']); ?></h5>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Buyer:</strong> <?php echo htmlspecialchars($request['buyer_name']); ?></p>
                                            <p class="mb-1"><strong>Buyer Email:</strong> <?php echo htmlspecialchars($request['buyer_email']); ?></p>
                                            <?php if ($request['buyer_phone']): ?>
                                                <p class="mb-1"><strong>Buyer Phone:</strong> <?php echo htmlspecialchars($request['buyer_phone']); ?></p>
                                            <?php endif; ?>
                                            <p class="mb-1"><strong>Quantity:</strong> <?php echo $request['quantity']; ?></p>
                                            <p class="mb-1"><strong>Total Amount:</strong> R<?php echo number_format($request['total_amount'], 2); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Payment Method:</strong> 
                                                <span class="badge bg-info"><?php echo isset($request['payment_method']) ? ucwords(str_replace('_', ' ', $request['payment_method'])) : 'Not specified'; ?></span>
                                            </p>
                                            <p class="mb-1"><strong>Status:</strong> 
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
                                    
                                    <?php if (!empty($request['buyer_message'])): ?>
                                        <div class="alert alert-light">
                                            <h6>Buyer's Message</h6>
                                            <p class="mb-0"><?php echo htmlspecialchars($request['buyer_message']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($request['seller_response'])): ?>
                                        <div class="alert alert-info">
                                            <h6>Your Response</h6>
                                            <p class="mb-0"><?php echo htmlspecialchars($request['seller_response']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($request['delivery_address'])): ?>
                                        <div class="alert alert-primary">
                                            <h6>Delivery Address</h6>
                                            <p class="mb-0"><?php echo htmlspecialchars($request['delivery_address']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Action buttons based on status -->
                                    <div class="mt-3">
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Response Message (Optional):</label>
                                                    <textarea name="seller_response" class="form-control" rows="2" placeholder="Add a message for the buyer..."></textarea>
                                                </div>
                                                <button type="submit" name="action" value="accept" class="btn btn-success me-2">Accept Request</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger">Reject Request</button>
                                            </form>
                                        <?php elseif ($request['status'] === 'accepted'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <button type="submit" name="action" value="complete" class="btn btn-primary me-2" 
                                                        onclick="return confirm('Mark this purchase as completed?')">Mark as Completed</button>
                                            </form>
                                            <span class="text-info">Contact the buyer to arrange the transaction.</span>
                                        <?php elseif ($request['status'] === 'completed'): ?>
                                            <span class="text-success">Transaction completed successfully!</span>
                                        <?php elseif ($request['status'] === 'rejected'): ?>
                                            <span class="text-danger">You declined this request.</span>
                                        <?php elseif ($request['status'] === 'cancelled'): ?>
                                            <span class="text-warning">Request was cancelled by the buyer.</span>
                                        <?php endif; ?>
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
                    <p class="mb-0">You haven't received any purchase requests for your products yet. Make sure your products are listed and visible to potential buyers.</p>
                    <a href="sell.php" class="btn btn-primary mt-3">Add New Product</a>
                    <a href="dashboard.php" class="btn btn-outline-primary mt-3 ms-2">View My Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
