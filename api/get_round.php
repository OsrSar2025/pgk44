<?php
// Set error reporting to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    include 'db_connect.php';
    
    $sql = "SELECT * FROM round ORDER BY id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $round_data = $stmt->fetch();

    if ($round_data) {
        echo json_encode([
            'success' => true,
            'data' => $round_data
        ]);
    } else {
        // If no data exists, create default
        $default_number = '25681019228';
        $sql = "INSERT INTO round (number) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$default_number]);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $pdo->lastInsertId(),
                'number' => $default_number
            ]
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fatal Error: ' . $e->getMessage()
    ]);
}
?>
