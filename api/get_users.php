<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Get all users
try {
    $stmt = $pdo->query("SELECT id, username, password, number as credit, status, auto_win FROM user ORDER BY id DESC");
    $users = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
} catch(PDOException $e) {
    // ถ้าคอลัมน์ status ยังไม่มี ให้ดึงข้อมูลแบบเดิม
    if (strpos($e->getMessage(), "Unknown column 'status'") !== false || strpos($e->getMessage(), "Unknown column 'auto_win'") !== false) {
        try {
            $stmt = $pdo->query("SELECT id, username, password, number as credit FROM user ORDER BY id DESC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // เพิ่มค่าเริ่มต้นสำหรับ status และ auto_win หากไม่มีคอลัมน์
            foreach ($users as &$user) {
                if (!isset($user['status'])) {
                    $user['status'] = 'active';
                }
                if (!isset($user['auto_win'])) {
                    $user['auto_win'] = 0;
                }
            }
            unset($user);
            
            echo json_encode([
                'success' => true,
                'data' => $users,
                'note' => 'Please add status/auto_win columns to user table'
            ]);
        } catch(PDOException $e2) {
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
