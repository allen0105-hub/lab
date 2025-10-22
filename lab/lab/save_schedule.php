<?php
require_once "includes/config.php";
header('Content-Type: application/json');

$date = $_POST['date'] ?? '';
$hour = $_POST['hour'] ?? '';
$year_section = $_POST['year_section'] ?? '';

if (!$date || !$hour || !$year_section) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO schedule (day_date, hour, year_section) VALUES (?, ?, ?)");
    $stmt->execute([$date, $hour, $year_section]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
