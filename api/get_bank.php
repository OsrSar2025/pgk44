<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
    $getAll = isset($_GET['all']) ? $_GET['all'] === 'true' : false;
    
    try {
        if ($getAll) {
            // Get all bank accounts
            $stmt = $pdo->prepare("SELECT * FROM bank ORDER BY id DESC");
            $stmt->execute();
            $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $banks
            ]);
        } else {
            // Get bank account data for user (get the latest one)
            if (!empty($userId)) {
                $stmt = $pdo->prepare("SELECT * FROM bank WHERE user_id = ? ORDER BY id DESC LIMIT 1");
                $stmt->execute([$userId]);
            } else {
                // If no user_id, get the latest bank record
                $stmt = $pdo->prepare("SELECT * FROM bank ORDER BY id DESC LIMIT 1");
                $stmt->execute();
            }
            
            $bank = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($bank) {
                echo json_encode([
                    'success' => true,
                    'data' => $bank
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลบัญชีธนาคาร'
                ]);
            }
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>
