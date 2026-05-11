<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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
    
    $message = $input['message'] ?? '';
    $status = $input['status'] ?? 'active';
    
    // Validate required fields
    if (empty($message)) {
        throw new Exception('Message is required');
    }
    
    // Insert new working hours record into content column only
    $insertSql = "INSERT INTO open (content) VALUES (?)";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([$message]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Working hours saved successfully',
        'id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
