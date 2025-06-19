<?php
/**
 * API Endpoint: Get Cities by Province
 * 
 * Returns a list of cities for a given province ID in JSON format.
 */

// Include database connection
require_once '../includes/db_connect.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if province_id is provided
if (!isset($_GET['province_id']) || empty($_GET['province_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Province ID is required',
        'cities' => []
    ]);
    exit;
}

$province_id = (int)$_GET['province_id'];

// Get cities for the province
$cities_query = "SELECT id, name FROM cities WHERE province_id = ? ORDER BY name";
$stmt = mysqli_prepare($conn, $cities_query);
mysqli_stmt_bind_param($stmt, "i", $province_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$cities = [];
while ($city = mysqli_fetch_assoc($result)) {
    $cities[] = [
        'id' => $city['id'],
        'name' => $city['name']
    ];
}

// Return cities as JSON
echo json_encode([
    'success' => true,
    'province_id' => $province_id,
    'cities' => $cities
]);
?>
