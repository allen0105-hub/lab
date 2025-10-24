<?php
session_start();
require_once "includes/config.php";

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false, 'message'=>'Not logged in']);
    exit();
}

$date = $_GET['date'] ?? null;
$hour = $_GET['hour'] ?? null;

if(!$date || !$hour){
    echo json_encode(['success'=>false, 'message'=>'Missing parameters']);
    exit();
}

// Fetch reservations for this slot
$stmt = $pdo->prepare("
    SELECT u.name, u.section, u.classification, r.reservation_type, r.reason, r.status
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    WHERE r.day_date = ? AND r.hour = ?
");
$stmt->execute([$date, $hour]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'reservations' => $reservations
]);
exit();
