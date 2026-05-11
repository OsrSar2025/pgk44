<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

try {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $views = $_POST['views'] ?? 0;
    
    // Get current movie data
    $stmt = $pdo->prepare("SELECT image FROM movie WHERE id = ?");
    $stmt->execute([$id]);
    $current_movie = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $image_path = $current_movie['image'];
    
    // Handle new file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../Photo/movie/';
        
        // Create directory if not exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            // Delete old image if exists
            if ($current_movie['image'] && file_exists('../' . $current_movie['image'])) {
                unlink('../' . $current_movie['image']);
            }
            
            $new_filename = 'movie_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'Photo/movie/' . $new_filename;
            }
        }
    }
    
    $stmt = $pdo->prepare("UPDATE movie SET image = ?, title = ?, views = ? WHERE id = ?");
    $stmt->execute([$image_path, $title, $views, $id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Movie updated successfully'
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
