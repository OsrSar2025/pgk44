<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $userId = isset($data['user_id']) ? intval($data['user_id']) : null;
    $autoWin = isset($data['auto_win']) ? intval($data['auto_win']) : null;

    if (!$userId || ($autoWin !== 0 && $autoWin !== 1)) {
        echo json_encode([
            'success' => false,
            'message' => 'ข้อมูลไม่ถูกต้อง'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE user SET auto_win = ? WHERE id = ?");
    $result = $stmt->execute([$autoWin, $userId]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'อัปเดตค่า Auto Win สำเร็จ',
            'data' => [
                'user_id' => $userId,
                'auto_win' => $autoWin
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่สามารถอัปเดตข้อมูลได้'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

