<?php
// Prevent any output before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unexpected output
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit;
}

try {
    // Include database connection
    if (!file_exists('db_connect.php')) {
        throw new Exception('Database connection file not found');
    }
    
    require_once 'db_connect.php';
    
    // Check if $pdo is set
    if (!isset($pdo)) {
        throw new Exception('Database connection not established');
    }
    
    $userId = '';
    
    // Handle both GET and POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawInput = file_get_contents('php://input');
        if (empty($rawInput)) {
            throw new Exception('Empty request body');
        }
        $input = json_decode($rawInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: ' . json_last_error_msg());
        }
        $userId = isset($input['user_id']) ? trim($input['user_id']) : '';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $userId = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
    }
    
    if (empty($userId)) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'User ID is required'
        ]);
        exit;
    }
    
    // Check if auto_win column exists, if not use default query
    try {
        // Try to get user data with auto_win column
        $stmt = $pdo->prepare("SELECT id, username, user_name, name, full_name, status, auto_win FROM user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If auto_win column doesn't exist, try without it
        $stmt = $pdo->prepare("SELECT id, username, user_name, name, full_name, status FROM user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $user['auto_win'] = 0; // Default value
        }
    }
    
    if ($user) {
        // Ensure auto_win has default value if column missing
        if (!isset($user['auto_win'])) {
            $user['auto_win'] = 0;
        }
        if (!isset($user['status'])) {
            $user['status'] = 'active';
        }
        
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'data' => $user
        ]);
    } else {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
    
} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
?>
