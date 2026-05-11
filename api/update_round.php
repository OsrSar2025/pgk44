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

    if (!isset($input['number']) || empty(trim($input['number']))) {
        echo json_encode(['success' => false, 'message' => 'Number is required']);
        exit;
    }

    $number = trim($input['number']);

    // Validate that it's a number
    if (!preg_match('/^\d+$/', $number)) {
        echo json_encode(['success' => false, 'message' => 'Number must contain only digits']);
        exit;
    }

    // Check if round data exists
    $sql = "SELECT id FROM round ORDER BY id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing record
        $sql = "UPDATE round SET number = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$number, $existing['id']]);
    } else {
        // Insert new record
        $sql = "INSERT INTO round (number) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$number]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Round number updated successfully',
        'number' => $number
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Fatal Error: ' . $e->getMessage()]);
}
?>
