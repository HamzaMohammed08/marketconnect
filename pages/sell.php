<?php
/**
 * Sell Product Page
 */

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_login();

$errors = [];
$title = $description = $price = $condition = '';
$category_id = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $condition = trim($_POST['condition']);
    $category_id = (int)$_POST['category_id'];
    $seller_id = $_SESSION['user_id'];
    
    // Basic validation
    if (empty($title)) {
        $errors['title'] = "Title is required";
    }
    if (empty($description)) {
        $errors['description'] = "Description is required";
    }
    if (empty($price)) {
        $errors['price'] = "Price is required";
    }
    if (empty($condition)) {
        $errors['condition'] = "Condition is required";
    }
    if ($category_id <= 0) {
        $errors['category'] = "Please select a category";
    }
    
    // Handle image upload
    $image_name = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        $file_type = $_FILES['product_image']['type'];
        $file_size = $_FILES['product_image']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $errors['image'] = "Only JPG, PNG, and GIF images are allowed";
        } elseif ($file_size > $max_size) {
            $errors['image'] = "Image size should not exceed 5MB";
        } else {
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $image_name = time() . '_' . $_FILES['product_image']['name'];
            $target_file = $upload_dir . $image_name;

            if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                $errors['image'] = "Failed to upload image";
            }
        }
    }
    
    if (empty($errors)) {
        // Insert using exact same method that worked in minimal test
        $insert_query = "INSERT INTO products (seller_id, category_id, title, description, price, condition_status, stock, status) VALUES (?, ?, ?, ?, ?, ?, 1, 'pending')";
        $stmt = mysqli_prepare($conn, $insert_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iissds", $seller_id, $category_id, $title, $description, $price, $condition);
            
            if (mysqli_stmt_execute($stmt)) {
                $product_id = mysqli_insert_id($conn);
                
                // Add image if uploaded
                if (!empty($image_name)) {
                    $image_path = 'uploads/' . $image_name;
                    $image_query = "INSERT INTO product_images (product_id, image_path, display_order) VALUES (?, ?, 0)";
                    $img_stmt = mysqli_prepare($conn, $image_query);
                    mysqli_stmt_bind_param($img_stmt, "is", $product_id, $image_path);
                    mysqli_stmt_execute($img_stmt);
                    mysqli_stmt_close($img_stmt);
                }
                
                $_SESSION['message'] = "Your product has been listed and is pending approval.";
                header("Location: dashboard.php");
                exit;
            } else {
                $errors['db'] = "Failed to list product: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors['db'] = "Database preparation failed: " . mysqli_error($conn);
        }
    }
}

// Get categories
$categories_result = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell an Item - MarketConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light bg-white border-bottom shadow-sm">
        <div class="container-fluid px-3 px-md-4">
            <a class="navbar-brand fw-bold fs-4" href="../index.php">
                <i class="fas fa-store me-2 text-primary"></i>MarketConnect
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link btn btn-outline-secondary btn-sm px-3 py-2" href="dashboard.php">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Sell an Item</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" 
                                       id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                                <?php if (isset($errors['title'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['title']; ?></div>
                                <?php endif; ?>
                                <div class="form-text">Be clear and descriptive about what you're selling</div>
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>" 
                                        id="category_id" name="category_id" required>
                                    <option value="0">Select a category</option>
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <?php if (isset($errors['category'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['category']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                                          id="description" name="description" rows="5" required><?php echo htmlspecialchars($description); ?></textarea>
                                <?php if (isset($errors['description'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                                <?php endif; ?>
                                <div class="form-text">Include details about condition, features, and why you're selling</div>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Price (R)</label>
                                <input type="number" step="0.01" min="0.01" 
                                       class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" 
                                       id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required>
                                <?php if (isset($errors['price'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['price']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="condition" class="form-label">Condition</label>
                                <select class="form-select <?php echo isset($errors['condition']) ? 'is-invalid' : ''; ?>" 
                                        id="condition" name="condition" required>
                                    <option value="">Select condition</option>
                                    <option value="new" <?php echo $condition == 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="like_new" <?php echo $condition == 'like_new' ? 'selected' : ''; ?>>Like New</option>
                                    <option value="good" <?php echo $condition == 'good' ? 'selected' : ''; ?>>Good</option>
                                    <option value="fair" <?php echo $condition == 'fair' ? 'selected' : ''; ?>>Fair</option>
                                    <option value="poor" <?php echo $condition == 'poor' ? 'selected' : ''; ?>>Poor</option>
                                </select>
                                <?php if (isset($errors['condition'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['condition']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="product_image" class="form-label">Product Image</label>
                                <input type="file" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" 
                                       id="product_image" name="product_image">
                                <?php if (isset($errors['image'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
                                <?php endif; ?>
                                <div class="form-text">Upload a clear image of your product (Max: 5MB, JPG/PNG/GIF)</div>
                                <div class="mt-2">
                                    <img id="image_preview" src="#" alt="Preview" style="max-width: 200px; max-height: 200px; display: none;">
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">List Item for Sale</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Image preview
    document.getElementById('product_image').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image_preview').src = e.target.result;
                document.getElementById('image_preview').style.display = 'block';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    </script>
</body>
</html>
