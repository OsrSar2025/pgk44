<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

try {
    include 'db_connect.php';
    
    echo "<h1>🔧 Fix All Profits NOW</h1>\n";
    echo "<p>Updating all profit values...</p>\n";
    echo "<hr>\n";
    
    // ดึง bets ที่ชนะทั้งหมด
    $select_sql = "SELECT user_id, betting_red, betting_blue, percentage, status, profit, date FROM bet_history WHERE status IN ('red win', 'blue win') ORDER BY date DESC";
    $stmt = $pdo->prepare($select_sql);
    $stmt->execute();
    $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found <strong>" . count($bets) . "</strong> winning bets</p>\n";
    echo "<hr>\n";
    
    $updated_count = 0;
    
    foreach ($bets as $bet) {
        $user_id = $bet['user_id'];
        $date = $bet['date'];
        $betting_red = intval($bet['betting_red']);
        $betting_blue = intval($bet['betting_blue']);
        $percentage = floatval($bet['percentage']);
        $status = $bet['status'];
        $old_profit = floatval($bet['profit']);
        
        // คำนวณ profit ใหม่
        $calculated_profit = 0;
        if ($status === 'red win') {
            $calculated_profit = floor($betting_red * ($percentage / 100));
        } else if ($status === 'blue win') {
            $calculated_profit = floor($betting_blue * ($percentage / 100));
        }
        
        // อัปเดตเฉพาะเมื่อ profit ต่างกัน
        if ($calculated_profit != $old_profit) {
            $update_sql = "UPDATE bet_history SET profit = ? WHERE user_id = ? AND date = ? AND betting_red = ? AND betting_blue = ? AND status = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_result = $update_stmt->execute([$calculated_profit, $user_id, $date, $betting_red, $betting_blue, $status]);
            
            if ($update_result) {
                $updated_count++;
                echo "<div style='background:#fff; border:1px solid #ddd; padding:10px; margin:5px 0;'>";
                echo "<strong>{$user_id}</strong> - UPDATED<br>";
                echo "Date: {$date}<br>";
                echo "Betting: {$betting_red}, {$betting_blue} | %: {$percentage} | Status: {$status}<br>";
                echo "Old: <span style='color:red; font-weight:bold;'>{$old_profit}</span> → New: <span style='color:green; font-weight:bold;'>{$calculated_profit}</span>";
                echo "</div>";
            }
        }
    }
    
    echo "<p style='color:green; font-weight:bold;'>✅ Updated <strong>{$updated_count}</strong> rows</p>\n";
    echo "<hr>\n";
    echo "<p style='color:green; font-weight:bold;'>✅ Done! All profits have been recalculated.</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

?>
<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
</style>
