<?php
// ไฟล์นี้จะถูกเรียกใช้โดย cron job หรือ scheduled task
// เรียกใช้ทุก 1 นาที เพื่อตรวจสอบและอัปเดต pending bets

// ตั้งค่า timezone
date_default_timezone_set('Asia/Bangkok');

// เรียกใช้ auto_update_pending_bets.php โดยตรง (ไม่ผ่าน HTTP)
// เพราะเราอยู่ใน PHP แล้ว ไม่ต้องใช้ cURL - เร็วกว่าและไม่ต้องพึ่งพา HTTP server

// ตั้งค่า timezone
date_default_timezone_set('Asia/Bangkok');

// เปลี่ยน directory เป็นโฟลเดอร์ api
$scriptDir = dirname(__FILE__);
chdir($scriptDir);

// เรียกใช้ auto_update_pending_bets.php โดยตรง
ob_start();
include 'auto_update_pending_bets.php';
$response = ob_get_clean();

// Parse JSON response
$data = json_decode($response, true);

// Log ผลลัพธ์
$logMessage = date('Y-m-d H:i:s') . " - Updated: " . ($data['updated_count'] ?? 0) . " bets, Found: " . ($data['expired_bets_found'] ?? 0) . " pending\n";
file_put_contents('cron_update_bets.log', $logMessage, FILE_APPEND | LOCK_EX);

// แสดงผลลัพธ์
echo "Cron job executed at " . date('Y-m-d H:i:s') . "\n";
if ($data && isset($data['success'])) {
    echo "Success: Updated {$data['updated_count']} bets, Found {$data['expired_bets_found']} pending\n";
} else {
    echo "Response: $response\n";
}
?>
