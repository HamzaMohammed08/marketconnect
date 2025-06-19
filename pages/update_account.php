<?php
/**
 * Update Account Script
 * 
 * Handles updating user account information and password.
 */

// Include database connection and authentication
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Require user to be logged in
require_login();

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = sanitize_input($_POST['name']);
    $phone = sanitize_input($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Initialize error array
    $errors = [];
    
    // Validate name
    if (empty($name)) {
        $errors['name'] = "Name is required";
    } elseif (strlen($name) < 3) {
        $errors['name'] = "Name must be at least 3 characters";
    }
    
    // Validate phone (optional)
    if (!empty($phone) && !preg_match("/^[0-9]{10}$/", $phone)) {
        $errors['phone'] = "Phone number must be 10 digits";
    }
    
    // Get current user data
    $user_query = "SELECT password FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    // Check if password change is requested
    $update_password = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // All password fields must be filled
        if (empty($current_password)) {
            $errors['current_password'] = "Current password is required to change password";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors['current_password'] = "Current password is incorrect";
        }
        
        if (empty($new_password)) {
            $errors['new_password'] = "New password is required";
        } elseif (strlen($new_password) < 6) {
            $errors['new_password'] = "New password must be at least 6 characters";
        }
        
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = "Passwords do not match";
        }
        
        $update_password = true;
    }
    
    // If no errors, update user information
    if (empty($errors)) {
        if ($update_password) {
            // Update name, phone, and password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $hashed_password, $user_id);
        } else {
            // Update only name and phone
            $update_query = "UPDATE users SET name = ?, phone = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssi", $name, $phone, $user_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            // Update session data
            $_SESSION['user_name'] = $name;
            
            // Set success message
            set_message("Your account has been updated successfully", "success");
            
            // Redirect to dashboard
            header("Location: dashboard.php#account");
            exit;
        } else {
            // Update failed
            set_message("Failed to update account: " . mysqli_error($conn), "danger");
            header("Location: dashboard.php#account");
            exit;
        }
    } else {
        // Set error message
        $error_msg = "Please correct the following errors:<br>";
        foreach ($errors as $error) {
            $error_msg .= "- $error<br>";
        }
        set_message($error_msg, "danger");
        header("Location: dashboard.php#account");
        exit;
    }
} else {
    // If not POST request, redirect to dashboard
    header("Location: dashboard.php");
    exit;
}
?>
