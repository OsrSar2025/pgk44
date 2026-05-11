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
    
    $id = $input['id'] ?? '';
    $title = $input['title'] ?? '';
    $views = $input['views'] ?? 0;
    $unit = $input['unit'] ?? 'หมื่น';
    
    if (empty($id) || empty($title)) {
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'
        ]);
        exit();
    }
    
    try {
        $sql = "UPDATE index1 SET title = ?, views = ?, unit = ? WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $views, $unit, $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'แก้ไขเพลงสำเร็จ'
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
