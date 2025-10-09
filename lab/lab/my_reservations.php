<?php
session_start();
require_once "includes/config.php";
date_default_timezone_set('Asia/Manila');

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    header("Location: user_info.php");
    exit();
}

$userId = $_SESSION['user_id'];
$user   = $_SESSION['user'];

// Fetch user reservations
$stmt = $pdo->prepare("
    SELECT r.day_date, r.hour, r.reservation_type, r.reason, r.status
    FROM reservations r
    WHERE r.user_id = ?
    ORDER BY r.day_date ASC, r.hour ASC
");
$stmt->execute([$userId]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Reservations — TCC</title>
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
<style>
body { font-family:'Roboto',sans-serif; background:#f0f4fa; padding:20px; }
h2 { font-family:'Merriweather',serif; color:#003366; margin-bottom:14px; }
.back-btn { display:inline-block; margin-bottom:14px; padding:8px 12px; background:#0059b3; color:#fff; border-radius:6px; text-decoration:none; }
.back-btn:hover { background:#003366; }

.filter-box { margin-bottom:14px; }
select {
    padding:6px 10px;
    border:1px solid #ccc;
    border-radius:6px;
    font-size:14px;
}

/* Table */
table { border-collapse:collapse; width:100%; background:white; box-shadow:0 4px 12px rgba(0,0,0,.1); border-radius:8px; overflow:hidden; }
th,td { padding:10px; text-align:center; border-bottom:1px solid #ddd; font-size:14px; }
th { background:#003366; color:white; }
tr:hover { background:#f9f9f9; }

/* Status badges */
.status {
    padding:4px 8px;
    border-radius:12px;
    font-size:12px;
    font-weight:600;
    color:white;
}
.status.Approved { background:#28a745; }
.status.Denied { background:#dc3545; }
.status.Pending { background:#fd7e14; }
.status.Past { background:#6c757d; }

/* Card style for mobile */
@media (max-width: 768px) {
    table, thead, tbody, th, td, tr { display: block; }
    thead { display: none; }
    tr { 
        margin-bottom: 15px; 
        background: #fff; 
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        border-radius: 10px;
        padding: 12px;
    }
    td {
        text-align: left;
        border: none;
        padding: 6px 10px;
        font-size: 14px;
        display: flex;
        justify-content: space-between;
    }
    td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #003366;
    }
}
</style>
</head>
<body>

<a href="user_dashboard.php" class="back-btn">← Back to Dashboard</a>
<h2>My Reservations</h2>

<div class="filter-box">
    <label for="statusFilter"><strong>Filter by Status:</strong></label>
    <select id="statusFilter">
        <option value="All">All</option>
        <option value="Approved">Approved</option>
        <option value="Denied">Denied</option>
        <option value="Pending">Pending</option>
        <option value="Past">Past</option>
    </select>
</div>

<table id="reservationTable">
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
        <?php else: foreach($activities as $act):
            $slotTime = new DateTime($act['day_date'].' '.str_pad($act['hour'],2,'0',STR_PAD_LEFT).':00:00');
            $isPast = $slotTime < new DateTime();
            $displayStatus = $act['status']; // keep original status
            $displayStatusText = $isPast ? $act['status'].' (Past)' : $act['status'];
        ?>
        <tr data-status="<?php echo htmlspecialchars($displayStatusText); ?>">
            <td data-label="Date"><?php echo htmlspecialchars($act['day_date']); ?></td>
            <td data-label="Hour"><?php echo date("g A", strtotime($act['hour'].":00")); ?></td>
            <td data-label="Type"><?php echo htmlspecialchars($act['reservation_type']); ?></td>
            <td data-label="Reason"><?php echo htmlspecialchars($act['reason']); ?></td>
            <td data-label="Status">
                <span class="status <?php echo htmlspecialchars($displayStatus); ?>">
                    <?php echo htmlspecialchars($displayStatusText); ?>
                </span>
            </td>
        </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>


<script>
document.getElementById("statusFilter").addEventListener("change", function() {
    var filter = this.value;
    var rows = document.querySelectorAll("#reservationTable tbody tr");

    rows.forEach(function(row) {
        var status = row.getAttribute("data-status");
        if (filter === "All" || status === filter) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
</script>

</body>
</html>
