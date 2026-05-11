<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? $_POST['id'] ?? 0;
    
    if (!$id) {
        throw new Exception('Live ID is required');
    }
    
    // Get live data to delete image file
    $stmt = $pdo->prepare("SELECT image FROM live WHERE id = ?");
    $stmt->execute([$id]);
    $live = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$live) {
        throw new Exception('Live not found');
    }
    
    // Delete image file if exists
    if ($live['image']) {
        $imagePath = '../' . $live['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Delete live record
    $stmt = $pdo->prepare("DELETE FROM live WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Live deleted successfully'
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
