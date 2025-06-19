<?php
// Simple Categories Management
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// Handle category actions
if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_category') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        mysqli_query($conn, "INSERT INTO categories (name, description) VALUES ('$name', '$description')");
        $message = "Category added successfully";
    } elseif ($_POST['action'] == 'edit_category') {
        $category_id = (int)$_POST['category_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        mysqli_query($conn, "UPDATE categories SET name = '$name', description = '$description' WHERE id = $category_id");
        $message = "Category updated successfully";
    } elseif ($_POST['action'] == 'delete_category') {
        $category_id = (int)$_POST['category_id'];
        mysqli_query($conn, "DELETE FROM categories WHERE id = $category_id");
        $message = "Category deleted successfully";
    }
}

// Get all categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

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
                        <a class="nav-link active" href="categories.php">
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
                <h1 class="h2">Category Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    Add Category
                </button>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Categories Table -->
            <div class="card">
                <div class="card-header">
                    <h5>All Categories</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td><?php echo htmlspecialchars($category['description']); ?></td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button class="btn btn-sm btn-outline-primary" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', '<?php echo htmlspecialchars($category['description']); ?>')">
                                                Edit
                                            </button>
                                            
                                            <!-- Delete Button -->
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this category?')">
                                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                <input type="hidden" name="action" value="delete_category">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_category">
                    
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_category">
                    <input type="hidden" name="category_id" id="editCategoryId">
                    
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="name" id="editCategoryName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editCategoryDescription" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(id, name, description) {
    document.getElementById('editCategoryId').value = id;
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editCategoryDescription').value = description;
    
    var modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}
</script>

<?php include_once '../includes/footer.php'; ?>
