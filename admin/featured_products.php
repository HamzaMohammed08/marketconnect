<?php
// Simple Featured Products Management
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// Handle feature/unfeature actions
if ($_POST && isset($_POST['action'])) {
    $product_id = (int)$_POST['product_id'];
    
    if ($_POST['action'] == 'feature') {
        mysqli_query($conn, "UPDATE products SET featured = 1 WHERE id = $product_id");
        $message = "Product marked as featured";
    } elseif ($_POST['action'] == 'unfeature') {
        mysqli_query($conn, "UPDATE products SET featured = 0 WHERE id = $product_id");
        $message = "Product removed from featured";
    }
}

// Get featured products
$featured_products = mysqli_query($conn, "SELECT p.*, c.name as category_name, u.name as seller_name
                                         FROM products p
                                         JOIN categories c ON p.category_id = c.id
                                         JOIN users u ON p.seller_id = u.id
                                         WHERE p.featured = 1 AND p.status = 'approved'
                                         ORDER BY p.created_at DESC");

// Get non-featured products (for adding to featured)
$non_featured_products = mysqli_query($conn, "SELECT p.*, c.name as category_name, u.name as seller_name
                                             FROM products p
                                             JOIN categories c ON p.category_id = c.id
                                             JOIN users u ON p.seller_id = u.id
                                             WHERE p.featured = 0 AND p.status = 'approved'
                                             ORDER BY p.created_at DESC LIMIT 10");

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
                        <a class="nav-link" href="products.php">
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
                        <a class="nav-link active" href="featured_products.php">
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
                <h1 class="h2">Featured Products</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFeaturedModal">
                    Add Featured Product
                </button>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Featured Products Table -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Currently Featured Products</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($featured_products) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Seller</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($product = mysqli_fetch_assoc($featured_products)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['title']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td>R<?php echo number_format($product['price'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                                            <td>
                                                <!-- View Button -->
                                                <a href="../pages/product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                
                                                <!-- Remove Button -->
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <input type="hidden" name="action" value="unfeature">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <h4>No Featured Products</h4>
                            <p class="text-muted">You haven't featured any products yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Featured Product Modal -->
<div class="modal fade" id="addFeaturedModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Featured Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Seller</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = mysqli_fetch_assoc($non_featured_products)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['title']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td>R<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="action" value="feature">
                                            <button type="submit" class="btn btn-sm btn-success">Feature</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
