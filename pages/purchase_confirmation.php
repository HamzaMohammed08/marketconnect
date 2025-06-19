<?php
/**
 * Purchase Confirmation Page
 * 
 * Shows confirmation after a purchase request has been submitted
 */

// Include database connection and helper functions
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

// Require user to be logged in
require_login('checkout.php');

// Check if we have confirmation data
if (!isset($_SESSION['purchase_confirmation'])) {
    set_message("No purchase confirmation found", "warning");
    header("Location: dashboard.php");
    exit;
}

$confirmation = $_SESSION['purchase_confirmation'];

// Clear the confirmation data
unset($_SESSION['purchase_confirmation']);

// Include header
include_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle text-success fa-4x mb-4"></i>
                <h2 class="text-success mb-3">Purchase Request Sent!</h2>
                
                <div class="alert alert-success">
                    <h5>What happens next?</h5>
                    <p class="mb-0">Your purchase request has been sent to <?php echo $confirmation['seller_count']; ?> seller(s). 
                    You will receive contact within 24 hours to arrange payment and pickup/delivery.</p>
                </div>
                
                <div class="row text-start">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-info-circle me-2"></i>Request Summary</h6>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Total Amount:</strong> <?php echo format_currency($confirmation['total_amount']); ?></li>
                                    <li><strong>Payment Method:</strong> 
                                        <?php
                                        switch($confirmation['payment_method']) {
                                            case 'cash_pickup':
                                                echo 'Cash on Pickup';
                                                break;
                                            case 'cash_delivery':
                                                echo 'Cash on Delivery';
                                                break;
                                            case 'mobile_payment':
                                                echo 'Mobile Payment (EFT)';
                                                break;
                                            case 'arrange_payment':
                                                echo 'Arrange with Seller';
                                                break;
                                        }
                                        ?>
                                    </li>
                                    <li><strong>Items:</strong> <?php echo $confirmation['total_requests']; ?> item(s)</li>
                                    <li><strong>Sellers:</strong> <?php echo $confirmation['seller_count']; ?> seller(s)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-clock me-2"></i>Timeline</h6>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="fas fa-check text-success me-2"></i>Request sent</li>
                                    <li><i class="fas fa-phone text-warning me-2"></i>Seller contact (within 24hrs)</li>
                                    <li><i class="fas fa-calendar text-info me-2"></i>Arrange meeting</li>
                                    <li><i class="fas fa-handshake text-muted me-2"></i>Complete transaction</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($confirmation['payment_method'] === 'cash_pickup'): ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Cash on Pickup:</strong> The seller will contact you to arrange a pickup location and time. 
                    Bring cash for payment when you collect your item(s).
                </div>
                <?php elseif ($confirmation['payment_method'] === 'cash_delivery'): ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-truck me-2"></i>
                    <strong>Cash on Delivery:</strong> The seller will contact you to arrange delivery to your area. 
                    Have cash ready for payment when the item(s) are delivered.
                </div>
                <?php elseif ($confirmation['payment_method'] === 'mobile_payment'): ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-mobile-alt me-2"></i>
                    <strong>Mobile Payment:</strong> The seller will provide banking details for transfer. 
                    Complete the payment before arranging pickup or delivery.
                </div>
                <?php elseif ($confirmation['payment_method'] === 'arrange_payment'): ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-comments me-2"></i>
                    <strong>Arrange with Seller:</strong> The seller will contact you to discuss payment options 
                    and arrange both payment and pickup/delivery details.
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="dashboard.php" class="btn btn-primary me-2">
                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                    </a>
                    <a href="browse.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-search me-2"></i>Continue Shopping
                    </a>
                    <a href="messages.php" class="btn btn-outline-info">
                        <i class="fas fa-envelope me-2"></i>Check Messages
                    </a>
                </div>
                
                <div class="mt-4 text-muted">
                    <small>
                        <i class="fas fa-shield-alt me-1"></i>
                        For your safety, always meet in public places and verify items before payment.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
