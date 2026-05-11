<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $user_id = $_POST['user_id'] ?? '';
    
    if (empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบ User ID']);
        exit;
    }
    
    // ลบข้อมูลจาก table balance
    $stmt = $pdo->prepare("DELETE FROM balance WHERE user_id = ?");
    $result = $stmt->execute([$user_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'ลบข้อมูล balance สำเร็จ'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบข้อมูลได้']);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
