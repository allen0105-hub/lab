<?php
require_once "includes/config.php";
header('Content-Type: application/json');

$date = $_POST['date'] ?? '';
$hour = $_POST['hour'] ?? '';
$department = $_POST['department'] ?? '';
$year_level = $_POST['year_level'] ?? '';
$section = $_POST['section'] ?? '';

if (!$date || !$hour || !$department || !$year_level || !$section) {
    echo json_encode(['success'=>false,'message'=>'All fields are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO schedule (day_date, hour, department, year_level, section) VALUES (?,?,?,?,?)");
    $stmt->execute([$date,$hour,$department,$year_level,$section]);
    echo json_encode(['success'=>true]);
} catch(PDOException $e){
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>
