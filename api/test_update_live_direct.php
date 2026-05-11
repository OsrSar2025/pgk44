<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db_connect.php';

// Test direct update
$test_id = 8; // เปลี่ยนเป็น ID ที่มีในตาราง
$new_views = 999.5;
$new_title = "TEST UPDATED " . date('H:i:s');

echo json_encode([
    'step' => '1. Before Update',
    'message' => 'Getting current data...'
]) . "\n\n";

// Get before
$stmt = $pdo->prepare("SELECT * FROM live WHERE id = ?");
$stmt->execute([$test_id]);
$before = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'step' => '2. Current Data',
    'data' => $before
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Update
$sql = "UPDATE live SET title = ?, views = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$result = $stmt->execute([$new_title, $new_views, $test_id]);

echo json_encode([
    'step' => '3. Update Executed',
    'result' => $result,
    'rowCount' => $stmt->rowCount(),
    'errorInfo' => $stmt->errorInfo(),
    'sent_data' => [
        'id' => $test_id,
        'title' => $new_title,
        'views' => $new_views,
        'views_type' => gettype($new_views)
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Get after
$stmt = $pdo->prepare("SELECT * FROM live WHERE id = ?");
$stmt->execute([$test_id]);
$after = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'step' => '4. After Update',
    'data' => $after,
    'comparison' => [
        'title_changed' => $before['title'] !== $after['title'],
        'views_changed' => $before['views'] != $after['views'],
        'before_views' => $before['views'],
        'after_views' => $after['views'],
        'before_views_type' => gettype($before['views']),
        'after_views_type' => gettype($after['views'])
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Try to check if MySQL is actually executing
$stmt = $pdo->query("SHOW SESSION STATUS LIKE 'Com_update'");
$update_count = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'step' => '5. MySQL Status',
    'data' => $update_count
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>

