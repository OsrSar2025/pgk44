<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $input['user_id'] ?? null;
    
    if (!$user_id) {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบ User ID'
        ]);
        exit;
    }
    
    try {
        // ดึงข้อมูลการบริจาคของผู้ใช้จาก table donate
        $stmt = $pdo->prepare("SELECT * FROM donate WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $donation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($donation) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'total_amount' => $donation['amount'],
                    'donation_count' => $donation['number'],
                    'last_donation_date' => $donation['date']
                ]
            ]);
        } else {
            // ถ้าไม่มีข้อมูลการบริจาค ให้ส่งค่าเริ่มต้น
            echo json_encode([
                'success' => true,
                'data' => [
                    'total_amount' => 0,
                    'donation_count' => 0,
                    'last_donation_date' => null
                ]
            ]);
        }
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>
