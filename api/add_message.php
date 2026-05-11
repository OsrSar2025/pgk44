<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $messageText = $input['message_text'] ?? '';
    $emoji = $input['emoji'] ?? '';
    $imagePath = $input['image_path'] ?? '';
    $videoPath = $input['video_path'] ?? '';
    $imageSize = $input['image_size'] ?? 0;
    $videoSize = $input['video_size'] ?? 0;
    $mimeType = $input['mime_type'] ?? '';
    $userId = $input['user_id'] ?? '';
    $userName = $input['user_name'] ?? '';
    
    
    // Validate required fields - at least one content field must be provided
    $hasContent = !empty($messageText) || !empty($emoji) || !empty($imagePath) || !empty($videoPath);
    if (!$hasContent) {
        throw new Exception('Message content is required');
    }
    
    // Prepare SQL statement
    $sql = "INSERT INTO message (message_text, emoji, image_path, video_path, image_size, video_size, mime_type, user_id, user_name, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement');
    }
    
    $stmt->execute([
        $messageText, 
        $emoji, 
        $imagePath, 
        $videoPath, 
        $imageSize, 
        $videoSize, 
        $mimeType, 
        $userId, 
        $userName
    ]);
    
    $messageId = $pdo->lastInsertId();
    echo json_encode([
        'success' => true, 
        'message' => 'Message saved successfully',
        'message_id' => $messageId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
