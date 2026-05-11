<?php
/**
 * เพิ่มคอลัมน์ auto_win ในตาราง user
 * ใช้กำหนดค่าสำหรับการชนะอัตโนมัติ (ค่าเริ่มต้น 0)
 */

header('Content-Type: application/json');
require_once 'db_connect.php';

try {
    // ตรวจสอบว่าคอลัมน์ auto_win มีอยู่แล้วหรือไม่
    $checkColumn = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'auto_win'");
    $columnExists = $checkColumn->rowCount() > 0;

    if ($columnExists) {
        echo json_encode([
            'success' => false,
            'message' => 'คอลัมน์ auto_win มีอยู่ในตาราง user แล้ว',
            'action' => 'skipped'
        ]);
        exit;
    }

    // เพิ่มคอลัมน์ auto_win
    $pdo->exec("ALTER TABLE `user`
        ADD COLUMN `auto_win` TINYINT(1) DEFAULT 0
        AFTER `status`");

    // อัปเดตข้อมูลที่มีอยู่แล้วให้มีค่า auto_win = 0
    $pdo->exec("UPDATE `user` SET `auto_win` = 0 WHERE `auto_win` IS NULL");

    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มคอลัมน์ auto_win ในตาราง user สำเร็จ',
        'action' => 'added',
        'column_info' => [
            'name' => 'auto_win',
            'type' => 'TINYINT(1)',
            'default' => 0,
            'position' => 'after status'
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

