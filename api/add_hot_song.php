<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

try {
    $title = $_POST['title'] ?? '';
    $views = $_POST['views'] ?? 0;
    $image_url = '';
    
    if (empty($title)) {
        throw new Exception('กรุณากรอกชื่อเพลง');
    }
    
    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../Photo/hot_song/';
        
        // Create directory if not exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('รองรับเฉพาะไฟล์ภาพ (jpg, jpeg, png, gif, webp)');
        }
        
        // Generate unique filename
        $newFileName = 'hot_song_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $image_url = 'Photo/hot_song/' . $newFileName;
        } else {
            throw new Exception('ไม่สามารถอัปโหลดรูปภาพได้');
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO songs (title, image_url, views) VALUES (?, ?, ?)");
    $stmt->execute([$title, $image_url, $views]);
    
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มเพลงสำเร็จ'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
