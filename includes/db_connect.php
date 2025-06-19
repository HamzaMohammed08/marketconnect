<?php
/**
 * Database Connection File
 * 
 * This file establishes a connection to the MySQL database using mysqli_connect.
 * It should be included in all files that need database access using require_once.
 */

// Database configuration
$db_host = "localhost";     // Database host (usually localhost for XAMPP/WAMP)
$db_user = "root";          // Database username (default is root for local development)
$db_pass = "";              // Database password (often empty for local development)
$db_name = "ecomm_platform"; // Database name (must match the one in database.sql)

// Establish database connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check if connection was successful
if (!$conn) {
    // If connection fails, terminate script and display error message
    die("Connection failed: " . mysqli_connect_error());
}

// Optional: Set character set to ensure proper handling of special characters
mysqli_set_charset($conn, "utf8mb4");

// Note: Don't close the connection here as it will be used in the files that include this one
// The connection will automatically close when the script ends

// You can use this file in other PHP files with:
// require_once 'includes/db_connect.php';
?>
