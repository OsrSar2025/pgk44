<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get current server time
    $serverTime = date('Y-m-d H:i:s');
    $serverTimestamp = time();
    
    // Return server time in JSON format
    echo json_encode([
        'success' => true,
        'server_time' => $serverTime,
        'server_timestamp' => $serverTimestamp,
        'timezone' => date_default_timezone_get()
    ]);
    
} catch (Exception $e) {
    // Return error if something goes wrong
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get server time',
        'message' => $e->getMessage()
    ]);
}
?>
