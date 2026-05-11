<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $song_name = $_POST['song_name'] ?? '';
    $views = $_POST['views'] ?? 0;
    
    if (empty($song_name)) {
        echo json_encode(['success' => false, 'message' => 'Song name is required']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO index1 (title, views) VALUES (?, ?)");
    $result = $stmt->execute([$song_name, $views]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Song added successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add song'
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
