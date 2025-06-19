<?php
// Simple Admin Edit Product
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = (int)$_GET['id'];

// Get product details
$product_query = "SELECT p.*, c.name as category_name, u.name as seller_name
                 FROM products p
                 JOIN categories c ON p.category_id = c.id
                 JOIN users u ON p.seller_id = u.id
                 WHERE p.id = $product_id";
$result = mysqli_query($conn, $product_query);

if (mysqli_num_rows($result) == 0) {
    header("Location: products.php");
    exit;
}

$product = mysqli_fetch_assoc($result);

// Get categories
$categories = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name");

// Handle form submission
if ($_POST) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $location = $_POST['location'];
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    $update_query = "UPDATE products SET 
                    title = '$title', 
                    description = '$description', 
                    price = $price, 
                    category_id = $category_id, 
                    location = '$location',
                    status = '$status',
                    featured = $featured
                    WHERE id = $product_id";
    
    if (mysqli_query($conn, $update_query)) {
        $message = "Product updated successfully";
        // Refresh product data
        $result = mysqli_query($conn, $product_query);
        $product = mysqli_fetch_assoc($result);
    }
}

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
                <h1 class="h2">Edit Product</h1>
                <div>
                    <a href="products.php" class="btn btn-outline-secondary">Back to Products</a>
                    <a href="../pages/product_detail.php?id=<?php echo $product_id; ?>" class="btn btn-outline-primary">View Product</a>
                </div>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5>Product Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category_id" required>
                                        <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $product['price']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($product['location']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="pending" <?php echo $product['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $product['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $product['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="featured" <?php echo $product['featured'] == 1 ? 'checked' : ''; ?>>
                                <label class="form-check-label">Featured Product</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Seller Information</label>
                            <div class="alert alert-info">
                                <strong>Seller:</strong> <?php echo htmlspecialchars($product['seller_name']); ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
