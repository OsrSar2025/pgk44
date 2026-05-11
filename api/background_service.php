<?php
// Background Service สำหรับ Auto Update
// ไฟล์นี้จะทำงานในพื้นหลังและอัปเดตสถานะอัตโนมัติ

// ตั้งค่า timezone
date_default_timezone_set('Asia/Bangkok');

// ตั้งค่าให้ทำงานต่อเนื่อง
set_time_limit(0);
ignore_user_abort(true);

// ตั้งค่า header
header('Content-Type: text/plain');
header('Cache-Control: no-cache');

echo "Background Service Started at " . date('Y-m-d H:i:s') . "\n";
echo "This service will run continuously and update pending bets every 30 seconds.\n";
echo "Press Ctrl+C to stop.\n\n";

// ฟังก์ชันสำหรับเรียกใช้ auto-update
function runAutoUpdate() {
    $url = 'http://localhost/pkg44/api/auto_update_pending_bets.php';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return "Error: $error";
    }
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            return "Success: Updated {$data['updated_count']} bets, Found {$data['expired_bets_found']} pending";
        } else {
            return "API Error: " . ($data['message'] ?? 'Unknown error');
        }
    } else {
        return "HTTP Error: $httpCode";
    }
}

// วนลูปทำงานต่อเนื่อง
$iteration = 0;
while (true) {
    $iteration++;
    $timestamp = date('Y-m-d H:i:s');
    
    echo "[$timestamp] Iteration #$iteration - Running auto-update...\n";
    
    $result = runAutoUpdate();
    echo "[$timestamp] Result: $result\n";
    
    // รอ 30 วินาที
    sleep(30);
    
    // ตรวจสอบว่ายังมีคนเข้าถึงหรือไม่
    if (connection_aborted()) {
        echo "[$timestamp] Connection aborted, stopping service.\n";
        break;
    }
}
?>
