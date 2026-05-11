<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    include 'db_connect.php';
    
    // ตั้ง PHP timezone เป็นเวลาไทย
    date_default_timezone_set('Asia/Bangkok');
    
    // ตั้ง MySQL timezone เป็นเวลาไทยอีกครั้งเพื่อให้แน่ใจ
    $pdo->exec("SET time_zone = '+07:00'");

    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Get user_id from request (optional for admin panel)
    $userId = isset($data['user_id']) ? $data['user_id'] : null;
    
    // Build SQL query based on whether user_id is provided
    // ฐานข้อมูลตั้ง timezone เป็น +07:00 แล้ว ดังนั้น date field เป็นเวลาไทยอยู่แล้ว
    // ใช้ date โดยตรงและ format ด้วย PHP เพื่อให้แน่ใจว่าเป็นเวลาไทย
    if ($userId) {
        // Get bet history for specific user only
        $sql = "SELECT 
                    user_id,
                    user_name,
                    betting_red,
                    betting_blue,
                    status,
                    jackpot,
                    profit,
                    date,
                    percentage
                FROM bet_history 
                WHERE user_id = :user_id
                ORDER BY date DESC
                LIMIT 100";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
        $stmt->execute();
        $betHistory = $stmt->fetchAll();
    } else {
        // Get all bet history for admin panel
        $sql = "SELECT 
                    user_id,
                    user_name,
                    betting_red,
                    betting_blue,
                    status,
                    jackpot,
                    profit,
                    date,
                    percentage
                FROM bet_history 
                ORDER BY date DESC
                LIMIT 1000";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $betHistory = $stmt->fetchAll();
    }
    
    // Format วันที่ให้เป็นเวลาไทย (UTC+7) สำหรับแต่ละ record
    // เพื่อให้แน่ใจว่าเป็นเวลาไทย
    foreach ($betHistory as &$bet) {
        if (isset($bet['date'])) {
            // ถ้า date เป็น string ให้ format เป็นเวลาไทย
            if (is_string($bet['date'])) {
                try {
                    // Parse วันที่จากฐานข้อมูล (ซึ่งเป็นเวลาไทย UTC+7)
                    // และ format ใหม่เป็นเวลาไทย
                    $dateObj = new DateTime($bet['date'], new DateTimeZone('Asia/Bangkok'));
                    $bet['date'] = $dateObj->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    // ถ้า parse ไม่ได้ ให้ใช้เวลาปัจจุบันของไทย
                    $bet['date'] = date('Y-m-d H:i:s');
                }
            }
        }
    }
    unset($bet); // unset reference
    
    echo json_encode([
        'success' => true,
        'data' => $betHistory
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
