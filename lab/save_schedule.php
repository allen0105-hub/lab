<?php
require_once "includes/config.php";
header('Content-Type: application/json');

$date = $_POST['date'] ?? '';
$hour = $_POST['hour'] ?? '';
$section = $_POST['section'] ?? '';

if (!$date || !$hour || !$section) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO schedule (day_date, hour, section) VALUES (?, ?, ?)");
    $stmt->execute([$date, $hour, $section]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
