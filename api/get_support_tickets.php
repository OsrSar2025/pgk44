<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'db_connect.php';

try {
    // Get support tickets from database
    // Use the message table as support tickets
    $sql = "SELECT 
                id as ticket_id,
                id as message_id,
                CONCAT('TKT', LPAD(id, 3, '0')) as ticket_number,
                user_name as user,
                user_id,
                CASE 
                    WHEN message_text != '' THEN message_text
                    WHEN emoji != '' THEN CONCAT('ส่งสติกเกอร์: ', emoji)
                    WHEN image_path != '' THEN 'ส่งรูปภาพ'
                    WHEN video_path != '' THEN 'ส่งวิดีโอ'
                    ELSE 'ไม่มีหัวข้อ'
                END as subject,
                CASE 
                    WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'High'
                    WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'Normal'
                    ELSE 'Low'
                END as priority,
                created_at as date,
                CASE 
                    WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'Pending'
                    ELSE 'Resolved'
                END as status
            FROM message 
            WHERE user_id IS NOT NULL AND user_id != ''
            ORDER BY created_at DESC
            LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tickets = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $tickets
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
