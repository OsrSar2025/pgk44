<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if 'message' column exists in 'open' table
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `open` LIKE 'message'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        // Add 'message' column
        $pdo->exec("ALTER TABLE `open` ADD COLUMN `message` TEXT DEFAULT NULL");
        echo json_encode(['success' => true, 'message' => 'Added message column to open table.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Message column already exists in open table.']);
    }

    // Check if 'status' column exists in 'open' table
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `open` LIKE 'status'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        // Add 'status' column
        $pdo->exec("ALTER TABLE `open` ADD COLUMN `status` ENUM('active', 'inactive') DEFAULT 'active'");
        echo json_encode(['success' => true, 'message' => 'Added status column to open table.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Status column already exists in open table.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error altering open table: ' . $e->getMessage()
    ]);
}
?>
