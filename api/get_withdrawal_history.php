<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT user_id, user_name, type, date, status, amount FROM history_payment WHERE type = 'ถอนเงิน' ORDER BY date DESC");
    $stmt->execute();
    $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $withdrawals
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
