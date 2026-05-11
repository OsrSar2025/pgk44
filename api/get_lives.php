<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

try {
    $stmt = $pdo->query("SELECT id, image, title, views FROM live ORDER BY id DESC");
    $lives = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add image_url field for compatibility
    foreach ($lives as &$live) {
        $live['image_url'] = $live['image'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $lives
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
