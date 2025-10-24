<?php
session_start();
require_once "includes/config.php"; // $pdo

header('Content-Type: application/json');

$date = $_GET['date'] ?? null;
$hour = $_GET['hour'] ?? null;

if (!$date || !$hour) {
    echo json_encode(['success' => false, 'message' => 'Missing date or hour']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT day_date, section FROM schedule WHERE day_date = ? AND hour = ?");
    $stmt->execute([$date, $hour]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$schedules) {
        echo json_encode(['success' => false, 'message' => 'No admin schedule found for this slot']);
        exit;
    }

    // Return only date and section
    echo json_encode(['success' => true, 'adminSchedules' => $schedules]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
