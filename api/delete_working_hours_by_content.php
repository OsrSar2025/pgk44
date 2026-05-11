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
    
    $content = $input['content'] ?? '';
    
    // Validate required fields
    if (empty($content)) {
        throw new Exception('Content is required');
    }
    
    // Delete working hours record by content
    $deleteSql = "DELETE FROM open WHERE content = ?";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute([$content]);
    
    if ($deleteStmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Working hours deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No record deleted - content not found'
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
