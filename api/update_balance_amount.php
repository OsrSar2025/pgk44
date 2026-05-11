<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $user_id = $_POST['user_id'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    
    if (empty($user_id) || $amount < 0) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วนหรือยอดเงินไม่ถูกต้อง']);
        exit;
    }
    
    // อัปเดตยอดเงินใน table balance
    $stmt = $pdo->prepare("UPDATE balance SET amount = ?, date = NOW() WHERE user_id = ?");
    $result = $stmt->execute([$amount, $user_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'อัปเดตยอดเงินสำเร็จ'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปเดตยอดเงินได้']);
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
