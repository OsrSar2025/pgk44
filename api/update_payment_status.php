<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $user_id = $data['user_id'] ?? '';
    $date = $data['date'] ?? '';
    $status = $data['status'] ?? '';
    
    if (empty($user_id) || empty($date) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit;
    }
    
    // ตรวจสอบว่าสถานะที่ส่งมาถูกต้อง
    $validStatuses = ['pending', 'processing', 'completed', 'failed'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'สถานะไม่ถูกต้อง']);
        exit;
    }
    
    // อัปเดตสถานะในฐานข้อมูล
    $stmt = $pdo->prepare("UPDATE history_payment SET status = ? WHERE user_id = ? AND date = ?");
    $result = $stmt->execute([$status, $user_id, $date]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'อัปเดตสถานะสำเร็จ',
            'affected_rows' => $stmt->rowCount()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบข้อมูลที่ต้องการอัปเดต'
        ]);
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
