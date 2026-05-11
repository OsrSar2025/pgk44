<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = (int)($input['id'] ?? 0);
    
    // Validate required fields
    if (!$id || $id <= 0) {
        throw new Exception('Valid Bank ID is required');
    }
    
    // Check if bank exists
    $stmt = $pdo->prepare("SELECT id FROM bank WHERE id = ?");
    $stmt->execute([$id]);
    $existing_bank = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_bank) {
        throw new Exception('Bank account not found');
    }
    
    // Delete bank record
    $sql = "DELETE FROM bank WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$id]);
    
    if (!$result) {
        throw new Exception('Failed to execute delete query');
    }
    
    $rowCount = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bank account deleted successfully',
        'rows_affected' => $rowCount
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>