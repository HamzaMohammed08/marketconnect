<?php
// Shopping Cart Functions
// Basic cart management using sessions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Add item to cart
function add_to_cart($conn, $product_id, $quantity = 1) {
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;

    if ($product_id <= 0 || $quantity <= 0) {
        return false;
    }

    // Check if product exists using prepared statement
    $product_query = "SELECT id, price, seller_id FROM products WHERE id = ?";
    $stmt = $conn->prepare($product_query);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $stmt->close();
        return false;
    }

    $product = $result->fetch_assoc();
    $stmt->close();

    // Check if user is trying to add their own product
    if (isset($_SESSION['user_id']) && $product['seller_id'] == $_SESSION['user_id']) {
        return false;
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    return true;
}

// Update cart quantity
function update_cart_quantity($conn, $product_id, $quantity) {
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;

    if ($product_id <= 0 || $quantity <= 0) {
        return false;
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $quantity;
        return true;
    }
    return false;
}

// Remove item from cart
function remove_from_cart($conn, $product_id) {
    $product_id = (int)$product_id;

    if ($product_id <= 0) {
        return false;
    }

    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        return true;
    }
    return false;
}

// Get cart items with product details
function get_cart_items($conn) {
    $cart_items = [];

    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        
        // Sanitize product IDs to ensure they are integers
        $product_ids = array_map('intval', $product_ids);
        $product_ids = array_filter($product_ids, function($id) { return $id > 0; });
        
        if (!empty($product_ids)) {
            // Create placeholders for prepared statement
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
            
            $query = "SELECT p.id, p.title, p.price, p.seller_id, u.name as seller_name 
                     FROM products p
                     JOIN users u ON p.seller_id = u.id
                     WHERE p.id IN ($placeholders)";
            
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $types = str_repeat('i', count($product_ids));
                $stmt->bind_param($types, ...$product_ids);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    while ($product = $result->fetch_assoc()) {
                        $product_id = $product['id'];
                        $cart_items[] = [
                            'product_id' => $product_id,
                            'title' => $product['title'],
                            'price' => $product['price'],
                            'seller_name' => $product['seller_name'],
                            'seller_id' => $product['seller_id'],
                            'quantity' => $_SESSION['cart'][$product_id],
                            'subtotal' => $_SESSION['cart'][$product_id] * $product['price']
                        ];
                    }
                }
                $stmt->close();
            }
        }
    }

    return $cart_items;
}

// Get cart total
function get_cart_total($cart_items) {
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

// Get cart count
function get_cart_count($conn) {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

// Clear cart
function clear_cart($conn) {
    $_SESSION['cart'] = [];
    return true;
}
?>
