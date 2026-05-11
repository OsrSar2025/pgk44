<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    include 'db_connect.php';
    
    // ตั้ง PHP timezone เป็นเวลาไทย
    date_default_timezone_set('Asia/Bangkok');
    
    // ตั้ง MySQL timezone เป็นเวลาไทยอีกครั้งเพื่อให้แน่ใจ
    $pdo->exec("SET time_zone = '+07:00'");

    // รับข้อมูลจาก POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $input['user_id'] ?? '';
    $user_name = $input['user_name'] ?? '';
    $betting_red = $input['betting_red'] ?? 0;
    $betting_blue = $input['betting_blue'] ?? 0;
    $jackpot = $input['jackpot'] ?? 0;
    $percentage = $input['percentage'] ?? 5; // ค่าเริ่มต้น 5 (5%)
    $profit = $input['profit'] ?? 0;

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($user_id) || empty($user_name)) {
        echo json_encode(['success' => false, 'message' => 'User ID and User Name are required']);
        exit;
    }

    // ตรวจสอบว่ามีการเดิมพันอย่างน้อย 1 ทีม
    if ($betting_red <= 0 && $betting_blue <= 0) {
        echo json_encode(['success' => false, 'message' => 'Must bet on at least one team']);
        exit;
    }

           // บันทึกข้อมูลการเดิมพัน
           // ใช้ NOW() จาก MySQL เพื่อให้แน่ใจว่าเป็นเวลาไทย (MySQL ตั้ง timezone เป็น +07:00 แล้วใน db_connect.php)
           $stmt = $pdo->prepare("INSERT INTO bet_history (user_id, user_name, betting_red, betting_blue, date, jackpot, percentage, profit, status) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, 'pending')");
           $result = $stmt->execute([$user_id, $user_name, $betting_red, $betting_blue, $jackpot, $percentage, $profit]);

           if ($result) {
               // ใช้ user_id แทน lastInsertId เพราะไม่มี AUTO_INCREMENT
               $bet_id = $user_id;
               error_log('Bet saved with user_id: ' . $bet_id);
               echo json_encode([
                   'success' => true, 
                   'message' => 'Bet history saved successfully',
                   'bet_id' => $bet_id
               ]);
           } else {
               error_log('Failed to save bet history');
               echo json_encode(['success' => false, 'message' => 'Failed to save bet history']);
           }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Fatal Error: ' . $e->getMessage()]);
}
?>
