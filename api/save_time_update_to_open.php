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
    
    // Check if open table exists and has the right structure
    $checkTableSql = "SHOW TABLES LIKE 'open'";
    $checkTableStmt = $pdo->prepare($checkTableSql);
    $checkTableStmt->execute();
    $tableExists = $checkTableStmt->fetch();
    
    if (!$tableExists) {
        // Create open table if it doesn't exist
        $createTableSql = "CREATE TABLE IF NOT EXISTS open (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message TEXT NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTableSql);
    }
    
    // Check if record exists in open table
    $checkSql = "SELECT id FROM open ORDER BY id DESC LIMIT 1";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute();
    $existingRecord = $checkStmt->fetch();
    
    if ($existingRecord) {
        // Update existing record
        $updateSql = "UPDATE open SET message = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$message, $status, $existingRecord['id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Time update updated successfully in open table',
            'action' => 'updated'
        ]);
    } else {
        // Insert new record
        $insertSql = "INSERT INTO open (message, status, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([$message, $status]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Time update created successfully in open table',
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
