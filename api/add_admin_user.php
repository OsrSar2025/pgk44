<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    // Validate required fields
    if (empty($username) || empty($password)) {
        throw new Exception('Username and password are required');
    }
    
    // Validate username length
    if (strlen($username) < 3) {
        throw new Exception('Username must be at least 3 characters long');
    }
    
    // Validate password length
    if (strlen($password) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }
    
    // Check if username already exists
    $checkSql = "SELECT COUNT(*) FROM user_admin WHERE username = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$username]);
    
    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception('Username already exists');
    }
    
    // Store password as plain text
    $sql = "INSERT INTO user_admin (username, password) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $password]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Admin user created successfully',
        'admin_id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
