<?php
/**
 * เพิ่มคอลัมน์ status ในตาราง user
 * ใช้สำหรับเก็บสถานะของผู้ใช้ เช่น 'active', 'inactive', 'banned'
 */

header('Content-Type: application/json');
require_once 'db_connect.php';

try {
    // ตรวจสอบว่าคอลัมน์ status มีอยู่แล้วหรือไม่
    $checkColumn = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'status'");
    $columnExists = $checkColumn->rowCount() > 0;
    
    if ($columnExists) {
        echo json_encode([
            'success' => false,
            'message' => 'คอลัมน์ status มีอยู่ในตาราง user แล้ว',
            'action' => 'skipped'
        ]);
        exit;
    }
    
    // เพิ่มคอลัมน์ status
    $pdo->exec("ALTER TABLE `user` 
        ADD COLUMN `status` VARCHAR(50) DEFAULT 'active' 
        AFTER `number`");
    
    // อัปเดตข้อมูลที่มีอยู่แล้วให้มี status เป็น 'active'
    $pdo->exec("UPDATE `user` SET `status` = 'active' WHERE `status` IS NULL");
    
    // เพิ่ม index เพื่อเพิ่มความเร็วในการค้นหา
    try {
        $pdo->exec("CREATE INDEX `idx_status` ON `user`(`status`)");
    } catch (PDOException $e) {
        // ถ้า index มีอยู่แล้วให้ข้าม
        if (strpos($e->getMessage(), 'Duplicate key name') === false) {
            throw $e;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มคอลัมน์ status ในตาราง user สำเร็จ',
        'action' => 'added',
        'column_info' => [
            'name' => 'status',
            'type' => 'VARCHAR(50)',
            'default' => 'active',
            'position' => 'after number'
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
        'error' => $e->getCode()
    ]);
}
?>

