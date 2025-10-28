<?php
session_start();
require_once "includes/config.php"; // database connection

// Ensure user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user's reservations
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE user_id = ? ORDER BY day_date DESC, hour ASC");
$stmt->execute([$user_id]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Reservations | TCC Lab Reservation System</title>
<style>
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(180deg, #fffdf6, #fff4cc);
  color: #003366;
  margin: 0;
  padding: 0;
}

.header {
  background: #003366;
  color: #ffcc00;
  text-align: center;
  padding: 14px 0;
  font-size: 20px;
  font-weight: bold;
  letter-spacing: 1px;
}

/* Back button */
.back-btn {
  position: fixed;
  top: 12px;
  left: 12px;
  background: #ffcc00;
  color: #003366;
  font-weight: bold;
  border: 2px solid #003366;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 13px;
  text-decoration: none;
  transition: all 0.2s ease;
  z-index: 20;
}

.back-btn:hover {
  background: #003366;
  color: #ffcc00;
}

.container {
  max-width: 1000px;
  margin: 30px auto;
  background: #ffffff;
  border: 3px solid #003366;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

/* Table */
.schedule-wrap { 
  width: 100%;
  overflow-x: hidden;
}

table {
  border-collapse: separate;
  border-spacing: 3px;
  width: 100%;
  table-layout: fixed;
}

th, td {
  padding: 6px;
  text-align: center;
  vertical-align: middle;
  border-radius: 6px;
  font-size: clamp(8px, 1.8vw, 11px);
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

th {
  background: #003366;
  color: #fff;
  position: sticky;
  top: 0;
  z-index: 2;
  font-size: clamp(9px, 2vw, 12px);
}

td {
  background: #fffdf6;
  border: 1px solid #00336640;
  height: 50px;
  color: #003366;
}

td.past {
  background: #607d8b;
  color: #fff;
}

td.approved {
  background: #4caf50;
  color: #fff;
}

td.pending {
  background: #ffeb3b;
  color: #222;
}

td.denied {
  background: #f44336;
  color: #fff;
}

tr:hover td {
  background: #e6f0ff;
}


/* Responsive */
@media (max-width: 600px) {
  .container {
    width: 90%;
    padding: 14px;
  }
  th, td {
    font-size: clamp(8px, 3vw, 10px);
    padding: 4px;
  }
}
</style>
</head>
<body>
<a href="user_dashboard.php" class="back-btn">← Back</a>
<div class="header">My Reservations</div>

<div class="container">
<section class="reservations" id="reservationSection">
  <div class="schedule-wrap">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Hour</th>
          <th>Type</th>
          <th>Reason</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($activities)): ?>
          <tr><td colspan="5" style="padding:14px;text-align:center;">No reservations yet</td></tr>
        <?php else: 
          foreach($activities as $act):
            $slotTime = new DateTime($act['day_date'].' '.str_pad($act['hour'],2,'0',STR_PAD_LEFT).':00:00');
            $displayStatus = $slotTime < new DateTime() ? 'past' : strtolower($act['status']);
        ?>
          <tr>
            <td><?php echo htmlspecialchars($act['day_date']); ?></td>
            <td><?php echo date("g A", strtotime($act['hour'].":00")); ?></td>
            <td><?php echo htmlspecialchars($act['reservation_type']); ?></td>
            <td><?php echo htmlspecialchars($act['reason']); ?></td>
            <td class="<?php echo htmlspecialchars($displayStatus); ?>">
              <?php echo ucfirst($displayStatus); ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div style="text-align:center;">
  </div>
</section>
</div>

</body>
</html>
