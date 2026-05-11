<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $user_id = $input['user_id'] ?? '';
    $amount = isset($input['amount']) ? floatval($input['amount']) : -1;
    $user_name = $input['user_name'] ?? '';
    
    if (empty($user_id) || $amount < 0) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วนหรือยอดเงินไม่ถูกต้อง']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT user_id, amount FROM balance WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE balance SET amount = ?, date = NOW() WHERE user_id = ?");
        $result = $stmt->execute([$amount, $user_id]);
    } else {
        $user_name = $user_name ?: 'User ' . $user_id;
        $stmt = $pdo->prepare("INSERT INTO balance (user_id, user_name, amount, date) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$user_id, $user_name, $amount]);
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'อัปเดตยอดเงินสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปเดตยอดเงินได้']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
