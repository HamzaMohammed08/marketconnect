<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get product
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ? AND seller_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "ii", $product_id, $user_id);
mysqli_stmt_execute($stmt);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$product) {
    header("Location: dashboard.php");
    exit;
}

// Get images
$stmt = mysqli_prepare($conn, "SELECT id, image_path FROM product_images WHERE product_id = ?");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$product_images = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

// Delete image if requested
if (isset($_POST['delete_image'])) {
    $image_id = (int)$_POST['image_id'];
    mysqli_query($conn, "DELETE FROM product_images WHERE id = $image_id AND product_id = $product_id");
    header("Location: edit_product.php?id=$product_id");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'])) {
    // Update product details
    $update = mysqli_query($conn, "UPDATE products SET 
        title = '" . mysqli_real_escape_string($conn, $_POST['title']) . "',
        description = '" . mysqli_real_escape_string($conn, $_POST['description']) . "',
        price = " . floatval($_POST['price']) . ",
        condition_status = '" . mysqli_real_escape_string($conn, $_POST['condition']) . "',
        stock = " . (int)$_POST['stock'] . "
        WHERE id = $product_id AND seller_id = $user_id");

    // Handle new image if uploaded
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] == 0) {
        $upload_dir = '../uploads/';
        $new_filename = time() . '_' . uniqid() . '.jpg';
        
        if (move_uploaded_file($_FILES['new_image']['tmp_name'], $upload_dir . $new_filename)) {
            mysqli_query($conn, "INSERT INTO product_images (product_id, image_path) 
                VALUES ($product_id, 'uploads/$new_filename')");
        }
    }

    if ($update) {
        header("Location: product_detail.php?id=$product_id");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4>Edit Product</h4>
            </div>
            <div class="card-body">
                <!-- Show current images -->
                <?php if (!empty($product_images)): ?>
                    <div class="row mb-4">
                        <?php foreach ($product_images as $image): ?>
                            <div class="col-md-3">
                                <div class="card">
                                    <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         class="card-img-top" style="height: 150px; object-fit: cover;">
                                    <div class="card-body p-2">
                                        <form method="post" class="text-center">
                                            <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                            <button type="submit" name="delete_image" class="btn btn-danger btn-sm">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" 
                            value="<?php echo htmlspecialchars($product['title']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4" required><?php 
                            echo htmlspecialchars($product['description']); 
                        ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Price (R)</label>
                        <input type="number" step="0.01" name="price" class="form-control" 
                            value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Condition</label>
                        <select name="condition" class="form-select" required>
                            <?php
                            $conditions = ['new' => 'New', 'like_new' => 'Like New', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor'];
                            foreach ($conditions as $value => $label):
                                $selected = ($product['condition_status'] == $value) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $value; ?>" <?php echo $selected; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Stock</label>
                        <input type="number" name="stock" class="form-control" 
                            value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Add New Image</label>
                        <input type="file" name="new_image" class="form-control" accept="image/*">
                    </div>

                    <div class="row">
                        <div class="col">
                            <button type="submit" class="btn btn-primary w-100">Update Product</button>
                        </div>
                        <div class="col">
                            <a href="product_detail.php?id=<?php echo $product_id; ?>" 
                               class="btn btn-secondary w-100">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
