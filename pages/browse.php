<?php
/**
 * Browse Products Page
 *
 * Displays all approved products with filtering options.
 */

// Include database connection
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

// Initialize variables for filtering
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_term = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 500000;
$sort_by = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'newest';

// Build the base query with product images
$query = "SELECT p.*, c.name as category_name, u.name as seller_name,
                 (SELECT pi.image_path FROM product_images pi 
                  WHERE pi.product_id = p.id 
                  ORDER BY pi.display_order ASC LIMIT 1) as first_image
          FROM products p
          JOIN categories c ON p.category_id = c.id
          JOIN users u ON p.seller_id = u.id
          WHERE p.status = 'approved'";

// Add filters to query
$params = [];
$types = "";

// Category filter
if ($category_id > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

// Search term filter
if (!empty($search_term)) {
    $query .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

// Price range filter
$query .= " AND p.price BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;
$types .= "dd";

// Add sorting
switch ($sort_by) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'oldest':
        $query .= " ORDER BY p.created_at ASC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY p.created_at DESC";
        break;
}

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $query);

if (!empty($params)) {
    // Dynamically bind parameters
    $ref = [];
    foreach ($params as $key => $value) {
        $ref[$key] = &$params[$key];
    }

    array_unshift($ref, $stmt, $types);
    call_user_func_array('mysqli_stmt_bind_param', $ref);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get all categories for filter sidebar
$categories_query = "SELECT id, name, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status = 'approved') as product_count
                    FROM categories c
                    ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

// Include header
include_once '../includes/header.php';
?>

<div class="row">
    <!-- Filter Sidebar -->
    <div class="col-lg-3 col-md-4 mb-4">
        <div class="card shadow tablet-filter-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Filters</h5>
                <button class="btn btn-link text-white p-0 d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="card-body collapse show" id="filterCollapse">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET">
                    <!-- Search -->
                    <div class="mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search products...">
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="0">All Categories</option>
                            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?> (<?php echo $category['product_count']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-3">
                        <label for="price_range" class="form-label">Price Range</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="number" class="form-control" id="min_price" name="min_price"
                                       value="<?php echo $min_price; ?>" min="0" placeholder="Min">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" id="max_price" name="max_price"
                                       value="<?php echo $max_price; ?>" min="0" placeholder="Max">
                            </div>
                        </div>
                    </div>

                    <!-- Sort By -->
                    <div class="mb-3">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort_by == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Apply Filters Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-outline-secondary">Reset Filters</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Listings -->
    <div class="col-lg-9 col-md-8">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0">Browse Products</h5>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-primary btn-sm d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="fas fa-filter me-1"></i>Filters
                    </button>
                    <span class="text-muted"><?php echo mysqli_num_rows($result); ?> products found</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="row g-3 tablet-products-grid">
                        <?php while ($product = mysqli_fetch_assoc($result)): ?>
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-4 tablet-product-col">
                                <div class="card h-100 shadow-sm tablet-product-card">
                                    <div class="tablet-product-img-container">
                                        <img src="<?php echo !empty($product['first_image']) ? '../' . $product['first_image'] : '../assets/images/no-image.jpg'; ?>"
                                             class="card-img-top tablet-product-img" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    </div>
                                    <div class="card-body tablet-product-body">
                                        <h5 class="card-title tablet-product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                        <p class="card-text tablet-product-desc"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . (strlen($product['description']) > 80 ? '...' : ''); ?></p>
                                        <div class="tablet-product-info">
                                            <div class="tablet-price-container">
                                                <span class="text-primary fw-bold tablet-price"><?php echo format_currency($product['price']); ?></span>
                                            </div>
                                            <div class="tablet-seller-info">
                                                <small class="text-muted">By <?php echo htmlspecialchars($product['seller_name']); ?></small>
                                            </div>
                                        </div>
                                        <div class="tablet-badges-container mt-2">
                                            <span class="badge bg-secondary tablet-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                            <span class="badge bg-info tablet-badge"><?php echo get_condition_text($product['condition_status']); ?></span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white tablet-product-footer">
                                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary w-100 tablet-view-btn">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No products found</h4>
                        <p class="text-muted">Try adjusting your search or filter criteria</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when category or sort changes
    const categorySelect = document.getElementById('category');
    const sortSelect = document.getElementById('sort');

    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            this.form.submit();
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>
