<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

try {
    // ดึงรายชื่อผู้ใช้ที่มี status = 'active'
    $stmt = $pdo->prepare("SELECT id, username FROM user WHERE status = 'active' ORDER BY id DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
} catch (PDOException $e) {
    // ถ้าคอลัมน์ status ยังไม่มี ให้ดึงข้อมูลทั้งหมด
    if (strpos($e->getMessage(), "Unknown column 'status'") !== false) {
        try {
            $stmt = $pdo->query("SELECT id, username FROM user ORDER BY id DESC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $users,
                'note' => 'Please run add_status_to_user_table.php to add status column'
            ]);
        } catch (PDOException $e2) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e2->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
?>

