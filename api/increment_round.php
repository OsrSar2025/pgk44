<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    include 'db_connect.php';

    // ดึงข้อมูลรอบปัจจุบัน
    $stmt = $pdo->prepare("SELECT id, number FROM `round` ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $currentRound = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($currentRound) {
        // บวก 1 เข้าไป
        $newRoundNumber = (string)((int)$currentRound['number'] + 1);
        
        // อัปเดตค่าใหม่
        $stmt = $pdo->prepare("UPDATE `round` SET `number` = ? WHERE id = ?");
        $stmt->execute([$newRoundNumber, $currentRound['id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Round incremented successfully',
            'old_number' => $currentRound['number'],
            'new_number' => $newRoundNumber
        ]);
    } else {
        // ถ้าไม่มีข้อมูล ให้สร้างรอบแรก
        $defaultRound = '1';
        $stmt = $pdo->prepare("INSERT INTO `round` (`number`) VALUES (?)");
        $stmt->execute([$defaultRound]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Round created successfully',
            'new_number' => $defaultRound
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fatal Error: ' . $e->getMessage()
    ]);
}
?>

