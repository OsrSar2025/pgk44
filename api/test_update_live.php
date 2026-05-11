<?php
// Test file to check update_live.php
header('Content-Type: text/html; charset=utf-8');

require_once 'db_connect.php';

echo "<h2>Test Update Live API</h2>";

// Get all lives
try {
    $stmt = $pdo->query("SELECT id, image, title, views FROM live ORDER BY id DESC LIMIT 5");
    $lives = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Available Lives:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Image</th><th>Title</th><th>Views</th></tr>";
    
    foreach ($lives as $live) {
        echo "<tr>";
        echo "<td>{$live['id']}</td>";
        echo "<td>";
        if ($live['image']) {
            echo "<img src='../{$live['image']}' width='50' height='50'><br>";
            echo $live['image'];
        } else {
            echo "No image";
        }
        echo "</td>";
        echo "<td>{$live['title']}</td>";
        echo "<td>{$live['views']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Test form
    if (!empty($lives)) {
        $firstLive = $lives[0];
        echo "<h3>Test Update Form (ID: {$firstLive['id']})</h3>";
        echo "<form method='POST' enctype='multipart/form-data' action='update_live.php'>";
        echo "<input type='hidden' name='id' value='{$firstLive['id']}'>";
        echo "Title: <input type='text' name='title' value='{$firstLive['title']}'><br><br>";
        echo "Views: <input type='number' name='views' value='{$firstLive['views']}'><br><br>";
        echo "Image: <input type='file' name='image' accept='image/*'><br><br>";
        echo "<input type='submit' value='Test Update'>";
        echo "</form>";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

