<?php
// Checkout Page

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/cart_functions.php';
require_once '../includes/helpers.php';

require_login('checkout.php');

$cart_items = get_cart_items($conn);
$cart_total = get_cart_total($cart_items);

if (empty($cart_items)) {
    $_SESSION['message'] = "Your cart is empty";
    header("Location: cart.php");
    exit;
}

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Checkout</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="process_purchase_request.php">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    
                    <h5>Payment Method</h5>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" value="cash_pickup" checked>
                            <label class="form-check-label">Cash on Pickup</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" value="cash_delivery">
                            <label class="form-check-label">Cash on Delivery</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" value="bank_transfer">
                            <label class="form-check-label">Bank Transfer</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" value="other">
                            <label class="form-check-label">Other Payment Method</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100">Submit Order</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <?php foreach ($cart_items as $item): ?>
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6><?php echo $item['title']; ?></h6>
                            <small>Qty: <?php echo $item['quantity']; ?></small>
                        </div>
                        <span><?php echo format_currency($item['subtotal']); ?></span>
                    </div>
                    <hr>
                <?php endforeach; ?>
                
                <div class="d-flex justify-content-between">
                    <strong>Total: <?php echo format_currency($cart_total); ?></strong>
                </div>
                
                <a href="cart.php" class="btn btn-outline-secondary w-100 mt-3">Back to Cart</a>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
