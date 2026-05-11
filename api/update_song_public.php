<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $id = $_POST['id'] ?? '';
    $song_name = $_POST['song_name'] ?? '';
    $views = $_POST['views'] ?? 0;
    
    if (empty($id) || empty($song_name)) {
        echo json_encode(['success' => false, 'message' => 'ID and song name are required']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE index1 SET title = ?, views = ? WHERE id = ?");
    $result = $stmt->execute([$song_name, $views, $id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Song updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update song'
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
