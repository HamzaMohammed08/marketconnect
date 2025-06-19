<?php
// Simple Admin Dashboard
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// Get basic statistics
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$pending_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE status = 'pending'"))['count'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];

// Get recent products (simplified)
$recent_products = mysqli_query($conn, "SELECT p.id, p.title, p.price, p.status, u.name as seller_name 
                                       FROM products p 
                                       JOIN users u ON p.seller_id = u.id 
                                       ORDER BY p.created_at DESC LIMIT 5");

// Get recent users
$recent_users = mysqli_query($conn, "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");

include_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Simple Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <h5>Admin Panel</h5>
                </div>
                
                <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                    <span>Main Management</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box me-2"></i>Products
                            <span class="badge bg-secondary ms-1"><?php echo $total_products; ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>Users
                            <span class="badge bg-info ms-1"><?php echo $total_users; ?></span>
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
                            <?php if ($pending_products > 0): ?>
                                <span class="badge bg-warning ms-1"><?php echo $pending_products; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="featured_products.php">
                            <i class="fas fa-star me-2"></i>Featured Products
                        </a>
                    </li>
                </ul>

                <div class="mt-4">
                    <a href="../index.php" class="btn btn-outline-primary btn-sm">Back to Site</a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Dashboard</h1>
            </div>

            <!-- Simple Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5>Total Products</h5>
                            <h2><?php echo $total_products; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5>Pending Products</h5>
                            <h2><?php echo $pending_products; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>Total Users</h5>
                            <h2><?php echo $total_users; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Content -->
            <div class="row">
                <div class="col-md-8">
                    <!-- Recent Products Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-box me-2"></i>Recent Products</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Price</th>
                                            <th>Seller</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($product = mysqli_fetch_assoc($recent_products)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['title']); ?></td>
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
                                                    <a href="../pages/product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="products.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-arrow-right me-1"></i>View All Products
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Recent Users -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-users me-2"></i>Recent Users</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php while ($user = mysqli_fetch_assoc($recent_users)): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <?php if ($user['role'] == 3): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php elseif ($user['role'] == 2): ?>
                                                    <span class="badge bg-info">Seller</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Buyer</span>
                                                <?php endif; ?>
                                                <br><small class="text-muted"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="users.php" class="btn btn-success btn-sm">
                                <i class="fas fa-arrow-right me-1"></i>Manage Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
