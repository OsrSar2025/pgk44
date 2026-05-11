<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    // Get all withdraw passwords with user information
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.user_id,
            p.username,
            p.withdrawal_code,
            p.transaction_date
        FROM password p
        ORDER BY p.id DESC
    ");
    $stmt->execute();
    $passwords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $passwords
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>