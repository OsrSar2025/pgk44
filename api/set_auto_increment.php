<?php
require_once 'db_connect.php';

try {
    // ปรับ AUTO_INCREMENT ให้เริ่มที่ 24571
    $sql = "ALTER TABLE `user` AUTO_INCREMENT = 24571";
    $pdo->exec($sql);
    
    echo "สำเร็จ! ตาราง user จะเริ่มนับ ID จาก 24571 แล้ว";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
