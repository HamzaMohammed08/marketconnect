<?php
/**
 * Delete Item Script
 * Handles deletion of products, messages, and reviews
 * Users can delete their own content, admins can delete anything
 */

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Require login
require_login();

// Validate input
if (!isset($_GET['type']) || !isset($_GET['id']) || empty($_GET['type']) || empty($_GET['id'])) {
    set_message("Invalid request", "danger");
    header("Location: dashboard.php");
    exit;
}

$type = sanitize_input($_GET['type']);
$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$redirect_url = 'dashboard.php';

// Handle different deletion types
switch ($type) {
    case 'product':
        // Check product ownership
        $check_query = "SELECT seller_id, image FROM products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $product = mysqli_fetch_assoc($result);

            // Only owner or admin can delete
            if ($product['seller_id'] != $user_id && !is_admin()) {
                set_message("You don't have permission to delete this product", "danger");
                header("Location: dashboard.php");
                exit;
            }

            // Delete product
            $delete_query = "DELETE FROM products WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                // Remove image file if exists
                if (!empty($product['image'])) {
                    $image_path = '../uploads/' . $product['image'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                set_message("Product deleted successfully", "success");
            } else {
                set_message("Failed to delete product: " . mysqli_error($conn), "danger");
            }
        } else {
            set_message("Product not found", "danger");
        }
        break;

    case 'message':
        // Check message ownership (sender or receiver)
        $check_query = "SELECT sender_id, receiver_id FROM messages WHERE id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $message = mysqli_fetch_assoc($result);

            // Only sender, receiver, or admin can delete
            if ($message['sender_id'] != $user_id && $message['receiver_id'] != $user_id && !is_admin()) {
                set_message("You don't have permission to delete this message", "danger");
                header("Location: messages.php");
                exit;
            }

            // Delete message
            $delete_query = "DELETE FROM messages WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                set_message("Message deleted successfully", "success");
                $redirect_url = 'messages.php';
            } else {
                set_message("Failed to delete message: " . mysqli_error($conn), "danger");
                $redirect_url = 'messages.php';
            }
        } else {
            set_message("Message not found", "danger");
            $redirect_url = 'messages.php';
        }
        break;

    case 'review':
        // Check review ownership
        $check_query = "SELECT user_id FROM reviews WHERE id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $review = mysqli_fetch_assoc($result);

            // Only reviewer or admin can delete
            if ($review['user_id'] != $user_id && !is_admin()) {
                set_message("You don't have permission to delete this review", "danger");
                header("Location: dashboard.php");
                exit;
            }

            // Delete review
            $delete_query = "DELETE FROM reviews WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                set_message("Review deleted successfully", "success");
            } else {
                set_message("Failed to delete review: " . mysqli_error($conn), "danger");
            }
        } else {
            set_message("Review not found", "danger");
        }
        break;

    default:
        set_message("Invalid item type", "danger");
        break;
}

// Redirect back
header("Location: $redirect_url");
exit;
?>
