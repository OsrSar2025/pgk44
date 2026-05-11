<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db_connect.php';

// Force update test
$id = 10;
$new_views = 8888.888;
$new_title = "FORCE TEST " . date('H:i:s');

echo "=== FORCE UPDATE TEST ===\n\n";

// 1. Get current data
$stmt = $pdo->prepare("SELECT * FROM live WHERE id = ?");
$stmt->execute([$id]);
$before = $stmt->fetch(PDO::FETCH_ASSOC);

echo "BEFORE:\n";
echo "ID: {$before['id']}\n";
echo "Title: {$before['title']}\n";
echo "Views: {$before['views']}\n";
echo "Image: {$before['image']}\n\n";

// 2. Force update with explicit casting
$sql = "UPDATE live SET title = ?, views = CAST(? AS DECIMAL(10,2)), image = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);

echo "SQL: $sql\n";
echo "Params: [title='$new_title', views=$new_views, image='{$before['image']}', id=$id]\n\n";

$result = $stmt->execute([$new_title, $new_views, $before['image'], $id]);

echo "Execute result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
echo "Rows affected: " . $stmt->rowCount() . "\n";
echo "Error info: " . json_encode($stmt->errorInfo()) . "\n\n";

// 3. Force commit
$pdo->exec("COMMIT");
$pdo->exec("FLUSH TABLES");

// 4. Get updated data
$stmt = $pdo->prepare("SELECT * FROM live WHERE id = ?");
$stmt->execute([$id]);
$after = $stmt->fetch(PDO::FETCH_ASSOC);

echo "AFTER:\n";
echo "ID: {$after['id']}\n";
echo "Title: {$after['title']}\n";
echo "Views: {$after['views']}\n";
echo "Image: {$after['image']}\n\n";

echo "COMPARISON:\n";
echo "Title changed: " . ($before['title'] !== $after['title'] ? 'YES' : 'NO') . "\n";
echo "Views changed: " . ($before['views'] != $after['views'] ? 'YES' : 'NO') . "\n";
echo "Before views: {$before['views']}\n";
echo "After views: {$after['views']}\n";

// 5. Try alternative update method
echo "\n=== ALTERNATIVE UPDATE METHOD ===\n";
$alt_views = 7777.777;
$alt_sql = "UPDATE live SET views = $alt_views WHERE id = $id";
echo "Direct SQL: $alt_sql\n";

$result2 = $pdo->exec($alt_sql);
echo "Direct exec result: $result2\n";

// Check again
$stmt = $pdo->prepare("SELECT views FROM live WHERE id = ?");
$stmt->execute([$id]);
$final = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Final views: {$final['views']}\n";
?>
