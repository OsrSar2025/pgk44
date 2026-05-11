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
    
    $oldContent = $input['old_content'] ?? '';
    $newContent = $input['new_content'] ?? '';
    
    // Debug: Log the received data
    error_log("Update Working Hours - Old Content: " . $oldContent);
    error_log("Update Working Hours - New Content: " . $newContent);
    
    // Validate required fields
    if (empty($oldContent) || empty($newContent)) {
        throw new Exception('Old content and new content are required');
    }
    
    // First, check if the record exists (use TRIM to handle whitespace)
    $checkSql = "SELECT * FROM open WHERE TRIM(content) = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([trim($oldContent)]);
    $existingRecord = $checkStmt->fetch();
    
    if (!$existingRecord) {
        // Try to find by partial match
        $partialSql = "SELECT * FROM open WHERE content LIKE ?";
        $partialStmt = $pdo->prepare($partialSql);
        $partialStmt->execute(['%' . trim($oldContent) . '%']);
        $partialRecord = $partialStmt->fetch();
        
        if ($partialRecord) {
            // Use the actual content from database for update
            $actualContent = $partialRecord['content'];
            $updateSql = "UPDATE open SET content = ? WHERE content = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$newContent, $actualContent]);
        } else {
            // Debug: Get all records to see what's in the database
            $allStmt = $pdo->query("SELECT * FROM open");
            $allRecords = $allStmt->fetchAll();
            error_log("All records in open table: " . json_encode($allRecords));
            
            throw new Exception('Record not found with content: ' . $oldContent);
        }
    } else {
        // Update working hours record by content
        $updateSql = "UPDATE open SET content = ? WHERE content = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$newContent, $existingRecord['content']]);
    }
    
    if ($updateStmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Working hours updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No record updated - content not found'
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
