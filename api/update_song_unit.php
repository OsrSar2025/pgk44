<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $unit = $_POST['unit'] ?? '';
    
    if (empty($id) || empty($unit)) {
        echo json_encode([
            'success' => false,
            'message' => 'ข้อมูลไม่ครบถ้วน'
        ]);
        exit();
    }
    
    try {
        $sql = "UPDATE index1 SET unit = ? WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$unit, $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'อัปเดตหน่วยสำเร็จ'
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
