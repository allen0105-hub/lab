<?php
require_once "includes/config.php";

$date = $_GET['date'] ?? null;
$hour = $_GET['hour'] ?? null;

if(!$date || !$hour){ exit("Invalid request"); }

// Fetch admin schedule
$stmt = $pdo->prepare("SELECT * FROM schedule WHERE day_date=? AND hour=?");
$stmt->execute([$date,$hour]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch reservations
$stmt = $pdo->prepare("
    SELECT r.id, r.day_date, r.hour, r.reservation_type, r.reason, r.status,
           u.name, u.department, u.classification
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    WHERE r.day_date=? AND r.hour=?
");
$stmt->execute([$date,$hour]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:9999;" onclick="this.remove()">
  <div style="background:#fff;padding:20px;border-radius:12px;max-width:500px;width:90%;max-height:80%;overflow:auto;" onclick="event.stopPropagation()">
    <h3>Slot Details — <?php echo htmlspecialchars($date)." ".str_pad($hour,2,"0",STR_PAD_LEFT).":00"; ?></h3>

    <?php if($admin): ?>
      <h4>Admin Schedule</h4>
      <p><b>Department:</b> <?php echo htmlspecialchars($admin['department']); ?></p>
      <p><b>Year Level:</b> <?php echo htmlspecialchars($admin['year_level']); ?></p>
      <p><b>Section:</b> <?php echo htmlspecialchars($admin['section']); ?></p>
      <hr>
    <?php endif; ?>

    <?php if($reservations): ?>
      <h4>User Reservations</h4>
      <ul>
      <?php foreach($reservations as $r): ?>
        <li>
          <b><?php echo htmlspecialchars($r['name']); ?></b> 
          (<?php echo htmlspecialchars($r['department'])." — ".htmlspecialchars($r['classification']); ?>)
          <br>
          <i><?php echo htmlspecialchars($r['reservation_type']); ?> — <?php echo htmlspecialchars($r['reason']); ?></i>
          <br>
          <span>Status: <?php echo htmlspecialchars($r['status']); ?></span>
        </li>
      <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No reservations.</p>
    <?php endif; ?>

    <button onclick="document.getElementById('detailsModal').remove()" style="margin-top:10px;padding:6px 12px;border:none;background:#003366;color:#fff;border-radius:6px;">Close</button>
  </div>
</div>
