<?php
// Debug file to see what data is being sent
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

// Log all POST data
$logData = [
    'POST' => $_POST,
    'FILES' => isset($_FILES['image']) ? [
        'name' => $_FILES['image']['name'],
        'size' => $_FILES['image']['size'],
        'error' => $_FILES['image']['error']
    ] : 'No file',
    'timestamp' => date('Y-m-d H:i:s')
];

// Get current data from database
if (isset($_POST['id']) && $_POST['id']) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM live WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $currentData = $stmt->fetch(PDO::FETCH_ASSOC);
        $logData['current_db_data'] = $currentData;
    } catch(PDOException $e) {
        $logData['db_error'] = $e->getMessage();
    }
}

// Save to log file
file_put_contents(
    '../debug_update_live.log', 
    date('Y-m-d H:i:s') . " - " . json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", 
    FILE_APPEND
);

echo json_encode([
    'success' => true,
    'message' => 'Debug data logged',
    'data' => $logData
]);
?>

