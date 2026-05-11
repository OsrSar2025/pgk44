<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$userId = isset($input['user_id']) ? trim((string) $input['user_id']) : '';

if ($userId === '') {
    echo json_encode(['success' => false, 'message' => 'No user id']);
    exit;
}

/** คืนค่าเป็นตัวเลขจริงเสมอ (กันค่าในฐานข้อมูลเป็น string มี comma ทำให้ฝั่ง JS parse ผิด) */
function normalize_balance_amount($raw) {
    if ($raw === null || $raw === '') {
        return 0.0;
    }
    $s = str_replace([',', ' ', "\xc2\xa0"], '', (string) $raw);
    if ($s === '' || !is_numeric($s)) {
        return 0.0;
    }
    return round((float) $s, 2);
}

try {
    // ดึงยอดเงินจริงจากตาราง balance
    $stmt = $pdo->prepare("SELECT amount FROM balance WHERE user_id = ? ORDER BY date DESC LIMIT 1");
    $stmt->execute([$userId]);
    $balance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($balance) {
        $amount = normalize_balance_amount($balance['amount']);
        echo json_encode([
            'success' => true,
            'balance' => $amount
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'balance' => 0
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
