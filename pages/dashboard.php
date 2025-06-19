<?php
/**
 * User Dashboard Page
 *
 * Displays user's listed products, messages, and account information.
 */

// Include database connection and authentication
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

// Require user to be logged in
require_login();

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get user's products
$products_query = "SELECT p.*, c.name as category_name
                  FROM products p
                  JOIN categories c ON p.category_id = c.id
                  WHERE p.seller_id = ?
                  ORDER BY p.created_at DESC";
$stmt = mysqli_prepare($conn, $products_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$products_result = mysqli_stmt_get_result($stmt);

// Get unread messages count
$messages_query = "SELECT COUNT(*) as unread_count
                  FROM messages
                  WHERE receiver_id = ? AND read_status = 0";
$stmt = mysqli_prepare($conn, $messages_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$messages_result = mysqli_stmt_get_result($stmt);
$unread_messages = mysqli_fetch_assoc($messages_result)['unread_count'];

// Include header
include_once '../includes/header.php';
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">My Dashboard</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="#my-listings" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="fas fa-tag me-2"></i> My Listings
                </a>
                <a href="seller_purchase_requests.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-shopping-cart me-2"></i> Purchase Requests
                </a>
                <a href="my_purchase_requests.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-list me-2"></i> My Requests
                </a>
                <a href="#messages" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-envelope me-2"></i> Messages
                    <?php if ($unread_messages > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $unread_messages; ?></span>
                    <?php endif; ?>
                </a>
                <a href="#account" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-user me-2"></i> Account Settings
                </a>
                <a href="sell.php" class="list-group-item list-group-item-action text-primary">
                    <i class="fas fa-plus-circle me-2"></i> Sell New Item
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9">
        <div class="tab-content">
            <!-- My Listings Tab -->
            <div class="tab-pane fade show active" id="my-listings">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">My Listed Products</h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($products_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Price</th>
                                            <th>Views</th>
                                            <th>Status</th>
                                            <th>Date Listed</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo !empty($product['image']) ? '../uploads/' . $product['image'] : '../assets/images/no-image.jpg'; ?>"
                                                         alt="<?php echo htmlspecialchars($product['title']); ?>"
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                </td>
                                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                                <!-- CURRENCY CHANGE: Already using format_currency() function to display prices in South African Rands (R) format -->
                                                <td><?php echo format_currency($product['price']); ?></td>
                                                <!-- PRODUCT ANALYTICS: Show view count for seller's reference -->
                                                <!-- This helps sellers see which products are popular -->
                                                <td>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-eye me-1"></i>
                                                        <?php echo number_format($product['views']); ?>
                                                    </span>
                                                </td>
                                                <!-- END PRODUCT ANALYTICS -->
                                                <td>
                                                    <?php if ($product['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php elseif ($product['status'] == 'approved'): ?>
                                                        <span class="badge bg-success">Approved</span>
                                                    <?php elseif ($product['status'] == 'rejected'): ?>
                                                        <span class="badge bg-danger">Rejected</span>
                                                    <?php elseif ($product['status'] == 'sold'): ?>
                                                        <span class="badge bg-secondary">Sold</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                                <td>
                                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $product['id']; ?>" data-type="product" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="lead">You haven't listed any products yet.</p>
                                <a href="sell.php" class="btn btn-primary">Sell an Item</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Messages Tab -->
            <div class="tab-pane fade" id="messages">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">My Messages</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-4">
                            <a href="messages.php" class="btn btn-primary">View All Messages</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Settings Tab -->
            <div class="tab-pane fade" id="account">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Account Settings</h5>
                    </div>
                    <div class="card-body">
                        <form id="accountForm" method="POST" action="update_account.php">
                            <!-- Account settings form fields would go here -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" readonly>
                                <div class="form-text">Email cannot be changed</div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Account</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation INFO-->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="delete_modal_text">Are you sure you want to delete this item?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm_delete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab navigation from URL hash
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`a[href="${hash}"]`);
        if (tab) {
            const bsTab = new bootstrap.Tab(tab);
            bsTab.show();
        }
    }

    // Handle delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const confirmDeleteBtn = document.getElementById('confirm_delete');

    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-id');
            const itemType = this.getAttribute('data-type');

            confirmDeleteBtn.setAttribute('data-id', itemId);
            confirmDeleteBtn.setAttribute('data-type', itemType);
        });
    });

    confirmDeleteBtn.addEventListener('click', function() {
        const itemId = this.getAttribute('data-id');
        const itemType = this.getAttribute('data-type');

        window.location.href = `delete_item.php?type=${itemType}&id=${itemId}`;
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>
