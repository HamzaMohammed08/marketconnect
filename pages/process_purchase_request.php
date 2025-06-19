<?php
// Process Purchase Request
// Submit purchase request to seller

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/cart_functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_items = get_cart_items($conn);
    
    if (empty($cart_items)) {
        header("Location: cart.php");
        exit;
    }
    
    $buyer_id = $_SESSION['user_id'];
    $buyer_name = $_POST['firstName'] . ' ' . $_POST['lastName'];
    $buyer_phone = $_POST['phone'];
    $buyer_email = $_POST['email'];
    $payment_method = $_POST['paymentMethod'];
    
    // Simple validation
    if (empty($buyer_name) || empty($buyer_phone)) {
        $_SESSION['message'] = "Name and phone are required";
        header("Location: checkout.php");
        exit;
    }
    
    // Validate payment method against allowed values
    $allowed_payment_methods = ['cash_pickup', 'cash_delivery', 'bank_transfer', 'other'];
    if (!in_array($payment_method, $allowed_payment_methods)) {
        $_SESSION['message'] = "Invalid payment method selected";
        header("Location: checkout.php");
        exit;
    }
    
    // Create purchase requests for each item using prepared statements
    $query = "INSERT INTO purchase_requests (buyer_id, seller_id, product_id, quantity, total_amount) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $_SESSION['message'] = "Database error occurred";
        header("Location: checkout.php");
        exit;
    }
    
    foreach ($cart_items as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $stmt->bind_param("iiiid", $buyer_id, $item['seller_id'], $item['product_id'], $item['quantity'], $item_total);
        
        if (!$stmt->execute()) {
            $_SESSION['message'] = "Failed to submit purchase request";
            header("Location: checkout.php");
            exit;
        }
    }
    
    $stmt->close();
    
    // Set confirmation data for the confirmation page
    $_SESSION['purchase_confirmation'] = [
        'total_amount' => array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cart_items)),
        'total_requests' => count($cart_items),
        'seller_count' => count(array_unique(array_column($cart_items, 'seller_id'))),
        'payment_method' => $payment_method
    ];
    
    // Clear cart
    clear_cart($conn);
    
    $_SESSION['message'] = "Purchase request sent! Seller will contact you soon.";
    header("Location: purchase_confirmation.php");
    exit;
}
?>
