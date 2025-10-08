<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit;
}

require_once "includes/config.php";
date_default_timezone_set('Asia/Manila');

// Validate POST data
$reservationId = $_POST['reservation_id'] ?? '';
$status        = $_POST['status'] ?? '';
$reason        = $_POST['reason'] ?? '';

if (!$reservationId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Normalize status
$status = strtolower(trim($status));
$allowedStatus = ['pending', 'approved', 'denied'];

if (!in_array($status, $allowedStatus)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // Check if reservation exists
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->execute([$reservationId]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        echo json_encode(['success' => false, 'message' => 'Reservation not found']);
        exit;
    }

    // Update reservation
    $update = $pdo->prepare("UPDATE reservations SET status = ?, reason = ? WHERE id = ?");
    $update->execute([$status, $reason, $reservationId]);

    // Fetch latest reservation info (optional but safer)
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->execute([$reservationId]);
    $updatedReservation = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'reservation' => [
            'id' => (int)$updatedReservation['id'],
            'status' => $updatedReservation['status'],
            'reason' => $updatedReservation['reason']
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
