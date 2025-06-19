<?php
// Simple Users Management
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// Handle user actions
if ($_POST && isset($_POST['action'])) {
    $user_id = (int)$_POST['user_id'];
    
    if ($_POST['action'] == 'change_role') {
        $new_role = (int)$_POST['new_role'];
        mysqli_query($conn, "UPDATE users SET role = $new_role WHERE id = $user_id");
        $message = "User role updated successfully";
    } elseif ($_POST['action'] == 'delete_user') {
        mysqli_query($conn, "DELETE FROM users WHERE id = $user_id AND role != 3");
        $message = "User deleted successfully";
    }
}

// Get all users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

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
                        <a class="nav-link active" href="users.php">
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
                <h1 class="h2">User Management</h1>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h5>All Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $users_data = [];
                                while ($user = mysqli_fetch_assoc($users)): 
                                    $users_data[] = $user; // Store for modals
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['role'] == 3): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php elseif ($user['role'] == 2): ?>
                                                <span class="badge bg-info">Seller</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Buyer</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if ($user['role'] != 3): ?>
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                                    Manage User
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Protected Admin</span>
                                            <?php endif; ?>
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

<!-- User Management Modals -->
<?php foreach ($users_data as $user): ?>
    <?php if ($user['role'] != 3): ?>
        <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage User: <?php echo htmlspecialchars($user['name']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?><br>
                            <strong>Current Role:</strong> 
                            <?php if ($user['role'] == 2): ?>
                                <span class="badge bg-info">Seller</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Buyer</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Change Role -->
                        <div class="mb-3">
                            <label class="form-label">Change Role:</label>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="change_role">
                                <div class="d-grid gap-2">
                                    <button type="submit" name="new_role" value="1" class="btn <?php echo $user['role'] == 1 ? 'btn-secondary' : 'btn-outline-secondary'; ?>" <?php echo $user['role'] == 1 ? 'disabled' : ''; ?>>
                                        Make Buyer
                                    </button>
                                    <button type="submit" name="new_role" value="2" class="btn <?php echo $user['role'] == 2 ? 'btn-info' : 'btn-outline-info'; ?>" <?php echo $user['role'] == 2 ? 'disabled' : ''; ?>>
                                        Make Seller
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <hr>
                        
                        <!-- Delete User -->
                        <div class="text-center">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="delete_user">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-2"></i>Delete User
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php include_once '../includes/footer.php'; ?>
