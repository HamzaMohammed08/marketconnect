<?php
// Simple Products Management
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// Handle product actions
if ($_POST && isset($_POST['action'])) {
    $product_id = (int)$_POST['product_id'];
    
    if ($_POST['action'] == 'approve') {
        mysqli_query($conn, "UPDATE products SET status = 'approved' WHERE id = $product_id");
        $message = "Product approved successfully";
    } elseif ($_POST['action'] == 'reject') {
        mysqli_query($conn, "UPDATE products SET status = 'rejected' WHERE id = $product_id");
        $message = "Product rejected";
    } elseif ($_POST['action'] == 'delete') {
        mysqli_query($conn, "DELETE FROM products WHERE id = $product_id");
        $message = "Product deleted";
    }
}

// Get all products
$products = mysqli_query($conn, "SELECT p.*, c.name as category_name, u.name as seller_name
                                FROM products p
                                JOIN categories c ON p.category_id = c.id
                                JOIN users u ON p.seller_id = u.id
                                ORDER BY p.created_at DESC");

include_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <i class="fas fa-user-shield fa-3x text-primary"></i>
                    <h6 class="mt-2">Admin Panel</h6>
                </div>
                
                <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                    <span>Main Management</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">
                            <i class="fas fa-box me-2"></i>Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-tags me-2"></i>Categories
                        </a>
                    </li>
                </ul>

                <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                    <span>Product Management</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="pending_products.php">
                            <i class="fas fa-clock me-2"></i>Pending Approval
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="featured_products.php">
                            <i class="fas fa-star me-2"></i>Featured Products
                        </a>
                    </li>
                </ul>

                <div class="d-grid gap-2 px-3 mt-4">
                    <a href="../index.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-home me-2"></i>Back to Site
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Products</h1>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Products Table -->
            <div class="card">
                <div class="card-header">
                    <h5>All Products</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Seller</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = mysqli_fetch_assoc($products)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['title']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td>R<?php echo number_format($product['price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                                        <td>
                                            <?php if ($product['status'] == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php elseif ($product['status'] == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- View Button -->
                                            <a href="../pages/product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            
                                            <?php if ($product['status'] == 'pending'): ?>
                                                <!-- Approve Button -->
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                </form>
                                                
                                                <!-- Reject Button -->
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-warning">Reject</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <!-- Delete Button -->
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this product?')">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
