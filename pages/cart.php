<?php
/**
 * Shopping Cart Page
 * 
 * Displays the user's shopping cart and allows them to manage items.
 */

// Include database connection and helper functions
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/cart_functions.php';
require_once '../includes/helpers.php';  // CURRENCY CHANGE: Added helpers.php for format_currency()

// Process cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        
        switch ($action) {
            case 'update':
                $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
                if ($quantity > 0) {
                    if (update_cart_quantity($conn, $product_id, $quantity)) {
                        set_message("Cart updated successfully", "success");
                    } else {
                        set_message("Failed to update cart", "danger");
                    }
                } else {
                    // If quantity is 0 or negative, remove the item
                    if (remove_from_cart($conn, $product_id)) {
                        set_message("Item removed from cart", "success");
                    } else {
                        set_message("Failed to remove item from cart", "danger");
                    }
                }
                break;
                
            case 'remove':
                if (remove_from_cart($conn, $product_id)) {
                    set_message("Item removed from cart", "success");
                } else {
                    set_message("Failed to remove item from cart", "danger");
                }
                break;
                
            case 'clear':
                if (clear_cart($conn)) {
                    set_message("Cart cleared successfully", "success");
                } else {
                    set_message("Failed to clear cart", "danger");
                }
                break;
        }
        
        // Redirect to avoid form resubmission
        header("Location: cart.php");
        exit;
    }
}

// Get cart items
$cart_items = get_cart_items($conn);
$cart_total = get_cart_total($cart_items);

// Include header
include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Shopping Cart</h4>
            </div>
            <div class="card-body">
                <?php if (empty($cart_items)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h5>Your cart is empty</h5>
                        <p class="text-muted">Browse our products and add items to your cart.</p>
                        <a href="browse.php" class="btn btn-primary mt-3">Browse Products</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo !empty($item['image']) ? '../uploads/' . $item['image'] : '../assets/images/no-image.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                     class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0">
                                                        <a href="product_detail.php?id=<?php echo $item['product_id']; ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($item['title']); ?>
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">Seller: <?php echo htmlspecialchars($item['seller_name']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <!-- CURRENCY CHANGE: Updated from $<?php echo number_format($item['price'], 2); ?> to use format_currency() for South African Rands -->
                                        <td><?php echo format_currency($item['price']); ?></td>
                                        <td>
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="quantity-form">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <div class="input-group" style="width: 120px;">
                                                    <button type="button" class="btn btn-outline-secondary quantity-btn" data-action="decrease">-</button>
                                                    <input type="number" name="quantity" class="form-control text-center quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="10">
                                                    <button type="button" class="btn btn-outline-secondary quantity-btn" data-action="increase">+</button>
                                                </div>
                                            </form>
                                        </td>
                                        <!-- CURRENCY CHANGE: Updated from $<?php echo number_format($item['subtotal'], 2); ?> to use format_currency() for South African Rands -->
                                        <td><?php echo format_currency($item['subtotal']); ?></td>
                                        <td>
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <!-- CURRENCY CHANGE: Updated from $<?php echo number_format($cart_total, 2); ?> to use format_currency() for South African Rands -->
                                    <td class="fw-bold"><?php echo format_currency($cart_total); ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-trash"></i> Clear Cart
                            </button>
                        </form>
                        
                        <div>
                            <a href="browse.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                            <a href="checkout.php" class="btn btn-success">
                                <i class="fas fa-shopping-cart"></i> Proceed to Checkout
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Quantity adjustment functionality
document.addEventListener('DOMContentLoaded', function() {
    const quantityBtns = document.querySelectorAll('.quantity-btn');
    
    quantityBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            const form = this.closest('.quantity-form');
            const input = form.querySelector('.quantity-input');
            let value = parseInt(input.value);
            
            if (action === 'increase') {
                if (value < parseInt(input.max)) {
                    input.value = value + 1;
                }
            } else if (action === 'decrease') {
                if (value > parseInt(input.min)) {
                    input.value = value - 1;
                }
            }
            
            // Submit the form to update the cart
            form.submit();
        });
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>
