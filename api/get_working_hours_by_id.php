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
    
    // Validate required fields
    if (empty($id)) {
        throw new Exception('ID is required');
    }
    
    // Get working hours data by ID
    $sql = "SELECT * FROM open WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug: Check what we found
    error_log("Looking for ID: " . $id);
    error_log("Found data: " . json_encode($data));
    
    if ($data) {
        echo json_encode([
            'success' => true,
            'data' => $data,
            'debug' => [
                'searched_id' => $id,
                'found_id' => $data['id'] ?? 'no id field'
            ]
        ]);
    } else {
        // Try to get all records to see what's available
        $allSql = "SELECT id, content FROM open LIMIT 5";
        $allStmt = $pdo->prepare($allSql);
        $allStmt->execute();
        $allData = $allStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => false,
            'message' => 'Record not found with ID: ' . $id,
            'debug' => [
                'searched_id' => $id,
                'available_records' => $allData
            ]
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
