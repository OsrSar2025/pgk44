<?php
// Set error reporting to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    include 'db_connect.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        $input = $_POST;
    }

    $required_fields = ['level', 'minimum_amount', 'percentage', 'blue', 'red', 'details'];

    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit;
        }
    }

    $level = trim($input['level']);
    $minimum_amount = floatval($input['minimum_amount']);
    $percentage = floatval($input['percentage']);
    $blue = trim($input['blue']);
    $red = trim($input['red']);
    $details = trim($input['details']);

    $sql = "INSERT INTO level_data (level, minimum_amount, percentage, blue, red, details) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$level, $minimum_amount, $percentage, $blue, $red, $details]);

    echo json_encode([
        'success' => true, 
        'message' => 'Level data added successfully',
        'id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Fatal Error: ' . $e->getMessage()]);
}
?>
