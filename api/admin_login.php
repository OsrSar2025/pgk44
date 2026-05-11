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
    
    // Get admin user from database
    $sql = "SELECT id, username, password FROM user_admin WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        throw new Exception('Invalid username or password');
    }
    
    // Verify password (plain text comparison)
    if ($password !== $admin['password']) {
        throw new Exception('Invalid username or password');
    }
    
    // Remove password from response
    unset($admin['password']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'admin' => $admin
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
