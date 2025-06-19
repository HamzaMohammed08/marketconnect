<?php
// Basic authentication functions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirect to login if not logged in
function require_login($redirect_url = '') {
    if (!is_logged_in()) {
        if (!empty($redirect_url)) {
            $_SESSION['redirect_after_login'] = $redirect_url;
        }
        $_SESSION['message'] = "Please log in to access this page.";
        // Use relative path for better compatibility
        $base_path = dirname($_SERVER['SCRIPT_NAME']);
        if (strpos($base_path, '/admin') !== false) {
            header("Location: ../pages/login.php");
        } else {
            header("Location: pages/login.php");
        }
        exit;
    }
}

// Set message
function set_message($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3;
}

// Require admin access
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_message("You don't have permission to access this area.", "danger");
        // Use relative path for better compatibility
        $base_path = dirname($_SERVER['SCRIPT_NAME']);
        if (strpos($base_path, '/admin') !== false) {
            header("Location: ../index.php");
        } else {
            header("Location: index.php");
        }
        exit;
    }
}

// Basic input cleaning
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
