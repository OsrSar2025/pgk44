<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$userId = $_GET['user_id'] ?? '24548';

$stmt = $pdo->prepare("SELECT id, username, password, LENGTH(password) as pwd_length FROM user WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($user, JSON_PRETTY_PRINT);
?>

