<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $id = (int)($input['id'] ?? 0);
    $level = trim($input['level'] ?? '');
    $minimum_amount = (float)($input['minimum_amount'] ?? 0);
    $percentage = (float)($input['percentage'] ?? 0);
    $blue = trim($input['blue'] ?? '');
    $red = trim($input['red'] ?? '');
    $details = trim($input['details'] ?? '');

    if (!$id || $id <= 0) {
        throw new Exception('Valid ID is required');
    }

    $stmt = $pdo->prepare("UPDATE level_data SET level = ?, minimum_amount = ?, percentage = ?, blue = ?, red = ?, details = ? WHERE id = ?");
    $stmt->execute([$level, $minimum_amount, $percentage, $blue, $red, $details, $id]);

    $rowCount = $stmt->rowCount();

    echo json_encode([
        'success' => true,
        'message' => 'Level data updated successfully',
        'rows_affected' => $rowCount
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>