<?php
// Create database and tables
require_once 'db_connect.php';

try {
    // Connect to MySQL without database first
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS tiktok CHARACTER SET utf8 COLLATE utf8_general_ci");
    $pdo->exec("USE tiktok");
    
    // Create index1 table
    $pdo->exec("CREATE TABLE IF NOT EXISTS index1 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        minBet DECIMAL(10,2) DEFAULT 0.00,
        description TEXT
    )");
    
    // Create songs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS songs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image VARCHAR(255),
        title VARCHAR(255),
        unit DECIMAL(10,2) DEFAULT 0.00
    )");
    
    // Create movie table
    $pdo->exec("CREATE TABLE IF NOT EXISTS movie (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image VARCHAR(255),
        title VARCHAR(255),
        views DECIMAL(10,2) DEFAULT 0.00
    )");
    
    // Create finance table
    $pdo->exec("CREATE TABLE IF NOT EXISTS finance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image VARCHAR(255),
        username VARCHAR(255),
        amount DECIMAL(10,2) DEFAULT 0.00
    )");
    
    // Create live table
    $pdo->exec("CREATE TABLE IF NOT EXISTS live (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image VARCHAR(255),
        title VARCHAR(255),
        viewers DECIMAL(10,2) DEFAULT 0.00
    )");
    
    // Create notifications table
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert sample data
    $pdo->exec("INSERT IGNORE INTO index1 (id, minBet, description) VALUES 
        (1, 100.00, 'VIP1 - ระบบสวัสดิการระดับพื้นฐาน'),
        (2, 500.00, 'VIP2 - ระบบสวัสดิการระดับกลาง'),
        (3, 1000.00, 'VIP3 - ระบบสวัสดิการระดับสูง'),
        (4, 2000.00, 'VIP4 - ระบบสวัสดิการระดับพรีเมียม'),
        (5, 5000.00, 'VIP5 - ระบบสวัสดิการระดับสุดยอด')");
    
    $pdo->exec("INSERT IGNORE INTO songs (id, image, title, unit) VALUES 
        (1, 'Photo/hot_song/1.jpg', 'เพลงฮิต 1', 150.50),
        (2, 'Photo/hot_song/2.jpg', 'เพลงฮิต 2', 200.75),
        (3, 'Photo/hot_song/3.jpg', 'เพลงฮิต 3', 180.25),
        (4, 'Photo/hot_song/4.jpg', 'เพลงฮิต 4', 220.00),
        (5, 'Photo/hot_song/5.jpg', 'เพลงฮิต 5', 190.50)");
    
    $pdo->exec("INSERT IGNORE INTO movie (id, image, title, views) VALUES 
        (1, 'Photo/movie/1.jpg', 'หนังเรื่อง 1', 1500.25),
        (2, 'Photo/movie/2.jpg', 'หนังเรื่อง 2', 2000.75),
        (3, 'Photo/movie/3.jpg', 'หนังเรื่อง 3', 1800.50),
        (4, 'Photo/movie/4.jpg', 'หนังเรื่อง 4', 2200.00),
        (5, 'Photo/movie/5.jpg', 'หนังเรื่อง 5', 1900.25)");
    
    $pdo->exec("INSERT IGNORE INTO finance (id, image, username, amount) VALUES 
        (1, 'Photo/finance/1.jpg', 'ผู้ใช้ 1', 50000.00),
        (2, 'Photo/finance/2.jpg', 'ผู้ใช้ 2', 45000.50),
        (3, 'Photo/finance/3.jpg', 'ผู้ใช้ 3', 40000.75),
        (4, 'Photo/finance/4.jpg', 'ผู้ใช้ 4', 35000.25),
        (5, 'Photo/finance/5.jpg', 'ผู้ใช้ 5', 30000.00)");
    
    $pdo->exec("INSERT IGNORE INTO live (id, image, title, viewers) VALUES 
        (1, 'Photo/live/1.jpg', 'ไลฟ์สด 1', 500.25),
        (2, 'Photo/live/2.jpg', 'ไลฟ์สด 2', 750.50),
        (3, 'Photo/live/3.jpg', 'ไลฟ์สด 3', 600.75),
        (4, 'Photo/live/4.jpg', 'ไลฟ์สด 4', 800.00),
        (5, 'Photo/live/5.jpg', 'ไลฟ์สด 5', 650.25)");
    
    $pdo->exec("INSERT IGNORE INTO notifications (id, message) VALUES 
        (1, 'ยินดีต้อนรับสู่แพลตฟอร์ม TikTok'),
        (2, 'ระบบจะอัพเดทข้อมูลทุก 5 นาที'),
        (3, 'กรุณาติดต่อฝ่ายบริการลูกค้าสำหรับข้อมูลเพิ่มเติม')");
    
    echo "Database and tables created successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
