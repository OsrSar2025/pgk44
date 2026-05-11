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
    
    $userId = $input['user_id'] ?? '';
    $labelName = $input['label'] ?? '';
    $labelColor = $input['label_color'] ?? '';
    
    // Validate required fields
    if (empty($userId) || empty($labelName) || empty($labelColor)) {
        throw new Exception('Missing required fields: user_id, label, label_color');
    }
    
    // Check if user already has any label (only allow one label per user)
    $checkSql = "SELECT id, label, label_color FROM labels WHERE user_id = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$userId]);
    $existingLabel = $checkStmt->fetch();
    
    if ($existingLabel) {
        // Update existing label (only one label per user)
        $updateSql = "UPDATE labels SET label = ?, label_color = ?, updated_at = NOW() WHERE user_id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$labelName, $labelColor, $userId]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Label updated successfully',
            'action' => 'updated',
            'old_label' => $existingLabel['label'],
            'old_color' => $existingLabel['label_color']
        ]);
    } else {
        // Insert new label
        $insertSql = "INSERT INTO labels (user_id, label, label_color, created_at, updated_at) 
                      VALUES (?, ?, ?, NOW(), NOW())";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([$userId, $labelName, $labelColor]);
        
        $labelId = $pdo->lastInsertId();
        echo json_encode([
            'success' => true, 
            'message' => 'Label saved successfully',
            'label_id' => $labelId,
            'action' => 'created'
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
