<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

try {
    $stmt = $pdo->query("SELECT id, image, title, views FROM movie ORDER BY id DESC");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add image_url field for compatibility
    foreach ($movies as &$movie) {
        $movie['image_url'] = $movie['image'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $movies
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
