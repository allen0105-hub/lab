<?php
session_start();
require_once "includes/config.php";

date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $hour = $_POST['hour'] ?? '';
    $reservation_type = $_POST['reservation_type'] ?? '';
    $reason = $_POST['reason'] ?? '';

    // Validation
    if (empty($date) || empty($hour) || empty($reservation_type) || empty($reason)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    try {
        // Check if slot already reserved with status 'Approved'
$stmt = $pdo->prepare("SELECT id FROM reservations WHERE day_date = ? AND hour = ? AND status = 'Approved'");
$stmt->execute([$date, $hour]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Slot already reserved with an approved reservation']);
    exit;
}


        // Check if admin created a schedule
        $stmt = $pdo->prepare("SELECT id FROM schedule WHERE day_date = ? AND hour = ?");
        $stmt->execute([$date, $hour]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
        $scheduleId = $schedule ? $schedule['id'] : null;

        // Insert reservation
        $stmt = $pdo->prepare("
            INSERT INTO reservations (user_id, schedule_id, day_date, hour, reservation_type, reason, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())
        ");
        $stmt->execute([$userId, $scheduleId, $date, $hour, $reservation_type, $reason]);

        echo json_encode(['success' => true, 'message' => 'Reservation saved successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
