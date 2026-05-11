<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Custom logging function
function logToFile($message) {
    $logFile = __DIR__ . '/../update_live_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $views = (float)($_POST['views'] ?? 0); // เปลี่ยนเป็น FLOAT เพื่อรองรับทศนิยม
    
    // Log incoming data
    logToFile("========== NEW UPDATE REQUEST ==========");
    logToFile("Raw POST: " . json_encode($_POST));
    logToFile("Parsed - ID: $id, Title: $title, Views: $views (" . gettype($views) . ")");
    
    // Validate required fields
    if (!$id || $id <= 0) {
        throw new Exception('Valid Live ID is required');
    }
    
    if (!$title || strlen($title) === 0) {
        throw new Exception('Title is required');
    }
    
    // Get current live data
    $stmt = $pdo->prepare("SELECT image FROM live WHERE id = ?");
    $stmt->execute([$id]);
    $current_live = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_live) {
        throw new Exception('Live not found');
    }
    
    $image_path = $current_live['image'];
    
    // Handle new file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../Photo/live/';
        
        // Create directory if not exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            // Delete old image if exists
            if ($current_live['image']) {
                $old_image_path = '../' . $current_live['image'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            
            $new_filename = 'live_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'Photo/live/' . $new_filename;
            } else {
                throw new Exception('Failed to upload image');
            }
        } else {
            throw new Exception('Invalid file type. Allowed: jpg, jpeg, png, gif, webp');
        }
    }
    
    // Get current data before update for comparison
    $stmt = $pdo->prepare("SELECT id, title, views, image FROM live WHERE id = ?");
    $stmt->execute([$id]);
    $beforeUpdate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$beforeUpdate) {
        throw new Exception("Live with ID $id not found");
    }
    
    // Log before update
    logToFile("BEFORE UPDATE: " . json_encode($beforeUpdate));
    logToFile("WILL UPDATE TO: title='$title', views=$views, image='$image_path'");
    
    // Update live record - use explicit CAST to match table structure (DECIMAL(15,1))
    $sql = "UPDATE live SET title = ?, views = CAST(? AS DECIMAL(15,1)), image = ? WHERE id = ?";
    logToFile("SQL: $sql");
    logToFile("Params: [title='$title', views=$views, image='$image_path', id=$id]");
    
    $stmt = $pdo->prepare($sql);
    
    // Execute with array
    $result = $stmt->execute([$title, $views, $image_path, $id]);
    
    // Log execute result and error info
    $errorInfo = $stmt->errorInfo();
    logToFile("Execute result: " . ($result ? 'TRUE' : 'FALSE'));
    logToFile("Error info: " . json_encode($errorInfo));
    
    if (!$result) {
        logToFile("❌ Execute FAILED!");
        throw new Exception('Failed to execute update query: ' . json_encode($errorInfo));
    }
    
    // Get number of rows affected
    $rowCount = $stmt->rowCount();
    
    // Log result
    logToFile("✅ Execute SUCCESS - Rows affected: $rowCount");
    
    // Force flush/commit to ensure data is written
    $pdo->exec("FLUSH TABLES");
    logToFile("🔄 Flushed tables to ensure data persistence");
    
    // Get updated data from database to confirm
    $stmt = $pdo->prepare("SELECT id, title, views, image FROM live WHERE id = ?");
    $stmt->execute([$id]);
    $afterUpdate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$afterUpdate) {
        throw new Exception('Live not found after update');
    }
    
    logToFile("AFTER UPDATE: " . json_encode($afterUpdate));
    
    // Check if data actually changed
    $titleChanged = $beforeUpdate['title'] !== $afterUpdate['title'];
    $viewsChanged = $beforeUpdate['views'] != $afterUpdate['views'];
    $imageChanged = $beforeUpdate['image'] !== $afterUpdate['image'];
    $somethingChanged = $titleChanged || $viewsChanged || $imageChanged;
    
    logToFile("COMPARISON:");
    logToFile("  - Title changed: " . ($titleChanged ? 'YES' : 'NO') . " ('{$beforeUpdate['title']}' -> '{$afterUpdate['title']}')");
    logToFile("  - Views changed: " . ($viewsChanged ? 'YES' : 'NO') . " ({$beforeUpdate['views']} -> {$afterUpdate['views']})");
    logToFile("  - Image changed: " . ($imageChanged ? 'YES' : 'NO'));
    logToFile("  - Something changed: " . ($somethingChanged ? 'YES' : 'NO'));
    
    // If rowCount is 0 but we want to force it to show as updated
    $effectiveRowCount = $somethingChanged ? ($rowCount > 0 ? $rowCount : 1) : $rowCount;
    
    // Force show as updated if data was sent (even if no change)
    if ($effectiveRowCount == 0 && $rowCount == 0) {
        $effectiveRowCount = 1; // Force show as updated
        logToFile("⚠️ FORCED UPDATE - No actual change but showing as updated");
    }
    
    logToFile("EFFECTIVE ROW COUNT: $effectiveRowCount (actual: $rowCount)");
    logToFile("========== REQUEST COMPLETED SUCCESSFULLY ==========\n");
    
    echo json_encode([
        'success' => true,
        'message' => 'Live updated successfully',
        'rows_affected' => $effectiveRowCount,
        'actual_rows_affected' => $rowCount,
        'data' => $afterUpdate,
        'before' => $beforeUpdate,
        'debug' => [
            'sent_title' => $title,
            'sent_views' => $views,
            'sent_views_type' => gettype($views),
            'before_title' => $beforeUpdate['title'],
            'before_views' => $beforeUpdate['views'],
            'before_views_type' => gettype($beforeUpdate['views']),
            'after_title' => $afterUpdate['title'],
            'after_views' => $afterUpdate['views'],
            'after_views_type' => gettype($afterUpdate['views']),
            'title_changed' => $titleChanged,
            'views_changed' => $viewsChanged,
            'image_changed' => $imageChanged
        ]
    ]);
} catch(PDOException $e) {
    logToFile("❌ DATABASE ERROR: " . $e->getMessage());
    logToFile("========== REQUEST FAILED ==========\n");
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    logToFile("❌ ERROR: " . $e->getMessage());
    logToFile("========== REQUEST FAILED ==========\n");
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
