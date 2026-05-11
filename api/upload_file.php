<?php
require_once 'db_connect.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    if (!isset($_FILES['file'])) {
        throw new Exception('No file uploaded');
    }
    
    $file = $_FILES['file'];
    $fileType = $_POST['file_type'] ?? ''; // 'image' or 'video'
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'ไฟล์ใหญ่เกินค่าที่เซิร์ฟเวอร์กำหนด',
            UPLOAD_ERR_FORM_SIZE => 'ไฟล์ใหญ่เกินค่าที่ฟอร์มกำหนด',
            UPLOAD_ERR_PARTIAL => 'อัปโหลดไฟล์ไม่ครบ กรุณาลองใหม่',
            UPLOAD_ERR_NO_FILE => 'ไม่พบไฟล์ที่อัปโหลด',
            UPLOAD_ERR_NO_TMP_DIR => 'เซิร์ฟเวอร์ไม่มีโฟลเดอร์ชั่วคราว',
            UPLOAD_ERR_CANT_WRITE => 'เซิร์ฟเวอร์ไม่สามารถเขียนไฟล์ได้',
            UPLOAD_ERR_EXTENSION => 'การอัปโหลดถูกบล็อกโดยส่วนเสริมของเซิร์ฟเวอร์'
        ];

        $errorMessage = $uploadErrors[$file['error']] ?? ('File upload error: ' . $file['error']);
        throw new Exception($errorMessage);
    }
    
    // Validate file type (support wider real-world MIME variants)
    $allowedImageTypes = [
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/heic',
        'image/heif'
    ];
    $allowedVideoTypes = [
        'video/mp4',
        'video/x-msvideo',
        'video/avi',
        'video/quicktime',
        'video/mov',
        'video/x-ms-wmv',
        'video/wmv',
        'video/webm'
    ];

    $fileMimeType = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $fileMimeType = finfo_file($finfo, $file['tmp_name']) ?: '';
            finfo_close($finfo);
        }
    }
    if (!$fileMimeType && function_exists('mime_content_type')) {
        $fileMimeType = mime_content_type($file['tmp_name']) ?: '';
    }
    if (!$fileMimeType && !empty($file['type'])) {
        $fileMimeType = $file['type'];
    }
    if (!$fileMimeType) {
        throw new Exception('Cannot detect file MIME type');
    }
    
    if ($fileType === 'image' && !in_array($fileMimeType, $allowedImageTypes)) {
        throw new Exception('Invalid image file type');
    }
    
    if ($fileType === 'video' && !in_array($fileMimeType, $allowedVideoTypes)) {
        throw new Exception('Invalid video file type');
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = '';
    if ($fileType === 'image') {
        $uploadDir = '../image/image_chat/';
    } elseif ($fileType === 'video') {
        $uploadDir = '../image/vdo_chat/';
    } else {
        throw new Exception('Invalid file type specified');
    }
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Return relative path from web root
        $relativePath = str_replace('../', '', $filePath);
        
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file_path' => $relativePath,
            'file_size' => $file['size'],
            'mime_type' => $fileMimeType,
            'file_name' => $fileName
        ]);
    } else {
        throw new Exception('Failed to move uploaded file');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
