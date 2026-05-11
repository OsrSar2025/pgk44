<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $messageId = $input['message_id'] ?? null;
    
    if (!$messageId) {
        throw new Exception('Message ID is required');
    }
    
    // Validate that message exists and is from Admin
    $checkStmt = $pdo->prepare("SELECT id, user_name FROM message WHERE id = ?");
    $checkStmt->execute([$messageId]);
    $message = $checkStmt->fetch();
    
    if (!$message) {
        throw new Exception('Message not found');
    }
    
    if ($message['user_name'] !== 'Admin') {
        throw new Exception('Only admin messages can be deleted');
    }
    
    // Delete message
    $sql = "DELETE FROM message WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement');
    }
    
    $stmt->execute([$messageId]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Message deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>

