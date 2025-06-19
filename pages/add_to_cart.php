<?php
/**
 * Add to Cart Handler
 * 
 * Processes requests to add products to the shopping cart.
 */

// Include database connection and helper functions
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/cart_functions.php';

// Check if product ID is provided
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    set_message("Product ID is required", "danger");
    header("Location: browse.php");
    exit;
}

$product_id = (int)$_POST['product_id'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate quantity
if ($quantity <= 0) {
    $quantity = 1;
}

// Add product to cart
if (add_to_cart($conn, $product_id, $quantity)) {
    set_message("Product added to cart successfully", "success");
} else {
    set_message("Failed to add product to cart", "danger");
}

// Redirect back to the product page
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : "product_detail.php?id=$product_id";
header("Location: $redirect");
exit;
?>
