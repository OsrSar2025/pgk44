<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON input'
        ]);
        exit();
    }
    
    $title = $input['title'] ?? '';
    $views = $input['views'] ?? 0;
    $unit = $input['unit'] ?? 'หมื่น';
    
    if (empty($title)) {
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกชื่อเพลง'
        ]);
        exit();
    }
    
    try {
        $sql = "INSERT INTO index1 (title, views, unit) VALUES (?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $views, $unit]);
        
        echo json_encode([
            'success' => true,
            'message' => 'เพิ่มเพลงสำเร็จ',
            'id' => $pdo->lastInsertId()
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
