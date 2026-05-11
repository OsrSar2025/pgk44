<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

try {
    $id = $_POST['id'] ?? 0;
    
    // Get finance data to delete image file
    $stmt = $pdo->prepare("SELECT image FROM finance WHERE id = ?");
    $stmt->execute([$id]);
    $finance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete image file if exists
    if ($finance && $finance['image'] && file_exists('../' . $finance['image'])) {
        unlink('../' . $finance['image']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM finance WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Finance record deleted successfully'
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
