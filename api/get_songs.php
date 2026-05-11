<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

try {
    $sql = "SELECT id, title, views, unit, created_at FROM index1 ORDER BY id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $songs = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $songs
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
