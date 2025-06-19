<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include cart functions if not already included
if (!function_exists('get_cart_count')) {
    require_once __DIR__ . '/db_connect.php';
    require_once __DIR__ . '/cart_functions.php';
}

// Function to get unread message count
if (!function_exists('get_unread_message_count')) {
    function get_unread_message_count($conn, $user_id) {
        if (!$conn || !$user_id) return 0;
        
        $query = "SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND read_status = 0";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        return $row['unread_count'] ?? 0;
    }
}

// Get cart count for display in header
$cart_count = isset($conn) ? get_cart_count($conn) : 0;

// Get unread message count for display in header
$unread_message_count = (isset($conn) && isset($_SESSION['user_id'])) ? get_unread_message_count($conn, $_SESSION['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketConnect - Your Local Marketplace</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
</head>
<body>
    <!-- Admin Top Bar (only for admins) -->
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3): ?>
        <div class="admin-topbar bg-dark text-white py-2">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small>
                            <i class="fas fa-user-shield me-2"></i>
                            <strong>Admin Mode:</strong> You are viewing the site as an administrator
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="/admin/dashboard.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                            <a href="/admin/products.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-box me-1"></i>Products
                            </a>
                            <a href="/admin/users.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-users me-1"></i>Users
                            </a>
                            <a href="/admin/categories.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-tags me-1"></i>Categories
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/index.php">
                <i class="fas fa-store me-2 text-primary"></i>MarketConnect
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/pages/browse.php">
                            <i class="fas fa-search me-1"></i>Browse Products
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/pages/sell.php">
                            <i class="fas fa-tag me-1"></i>Sell Item
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="/pages/cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Cart
                            <?php if ($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $cart_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="/pages/messages.php">
                                <i class="fas fa-envelope me-1"></i>Messages
                                <?php if ($unread_message_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $unread_message_count; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <div class="user-avatar me-2">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                        <?php echo isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U'; ?>
                                    </div>
                                </div>
                                <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3): ?>
                                    <li><a class="dropdown-item" href="/admin/dashboard.php">
                                        <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="/admin/products.php">
                                        <i class="fas fa-box me-2"></i>Manage Products
                                    </a></li>
                                    <li><a class="dropdown-item" href="/admin/users.php">
                                        <i class="fas fa-users me-2"></i>Manage Users
                                    </a></li>
                                    <li><a class="dropdown-item" href="/admin/categories.php">
                                        <i class="fas fa-tags me-2"></i>Manage Categories
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/pages/dashboard.php">
                                    <i class="fas fa-home me-2"></i>My Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="/pages/my_purchase_requests.php">
                                    <i class="fas fa-shopping-bag me-2"></i>My Purchase Requests
                                </a></li>
                                <li><a class="dropdown-item" href="/pages/seller_purchase_requests.php">
                                    <i class="fas fa-bell me-2"></i>Purchase Requests for Me
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary nav-link text-white" href="/pages/register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container mt-4 mb-4">
        <!-- Display flash messages if any -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; ?> alert-dismissible fade show">
                <?php
                    echo $_SESSION['message'];
                    // Clear the message after displaying it
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

<style>
.admin-topbar {
    position: sticky;
    top: 0;
    z-index: 1050;
    border-bottom: 2px solid #007bff;
}

.navbar {
    border-bottom: 1px solid #dee2e6;
}

.user-avatar {
    display: inline-block;
}

.dropdown-header {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.85rem;
}
</style>
