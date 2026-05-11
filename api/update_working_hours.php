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
    
    $id = $input['id'] ?? '';
    $message = $input['message'] ?? '';
    
    // Validate required fields
    if (empty($id) || empty($message)) {
        throw new Exception('ID and message are required');
    }
    
    // Update working hours record
    $updateSql = "UPDATE open SET content = ? WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$message, $id]);
    
    if ($updateStmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Working hours updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No record updated'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
