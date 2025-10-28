<?php
session_start();
require_once "includes/config.php"; // make sure $pdo is defined here

date_default_timezone_set('Asia/Manila');

// Redirect if user is not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    header("Location: user_info.php");
    exit();
}

$userId = $_SESSION['user_id'];
$user = $_SESSION['user'];

// Fetch schedules
$stmt = $pdo->query("SELECT * FROM schedule");
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize schedules by date and hour
$grid = [];
foreach ($schedules as $s) {
    $grid[$s['day_date']][$s['hour']] = $s;
}
// Fetch all approved reservations (for everyone)
$stmt = $pdo->query("
    SELECT r.day_date, r.hour, u.name, u.section, u.classification, 
           r.reservation_type, r.reason, r.status
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    WHERE r.status = 'Approved'
");
$approvedReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize approved reservations by date and hour
$approvedGrid = [];
foreach ($approvedReservations as $r) {
    $approvedGrid[$r['day_date']][$r['hour']] = $r;
}

// Current week dates (Mon - Sun)
$today = new DateTime();
$today->modify('Monday this week');
$weeks = [];
for ($i = 0; $i < 7; $i++) {
    $date = clone $today;
    $date->modify("+$i day");
    $weeks[$date->format('Y-m-d')] = $date;
}

// Hours 7AM–9PM
$hours = range(7, 21);

// Fetch user reservations (UPCOMING ONLY, ORDERED BY DATE ASC)
$stmt = $pdo->prepare("
    SELECT r.day_date, r.hour, r.reservation_type, r.reason, r.status
    FROM reservations r
    WHERE r.user_id = ?
    AND CONCAT(r.day_date, ' ', LPAD(r.hour, 2, '0'), ':00:00') >= NOW()
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
<title>TCC — User Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: 'Roboto', sans-serif; overflow-x:hidden; overflow-y:auto; }

body {
    background: url('images/tcc.jpg') no-repeat center center fixed;
    background-size: cover;
    color:#003366; padding: 24px;
}

/* Header */
.header {
  display:flex;
  align-items:center;
  justify-content:center;
  margin-bottom:20px;
}
.title {
  font-family: 'Poppins', sans-serif;
  font-size: 30px;
  font-weight: 700;
  color: #FFD700;
  text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
  text-align:center;
}
/* Container */
.container {
  width:100%;
  max-width:1240px;
  margin:0 auto 20px;
  background:transparent-white; /* semi-transparent for bg visibility */
  border-radius: 12px;
  padding:20px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

/* User card */
.user-strip { display:flex; justify-content:space-between; flex-wrap:wrap; margin-bottom:16px; }
.user-left { display:flex; align-items:center; gap:12px; }
.user-card { display:flex; align-items:center; gap:12px; background:#fffefb; border:3px solid #003366; border-radius:12px; padding:8px 12px; box-shadow:0 4px 12px rgba(0,0,0,.15); }
.portrait { width:64px; height:64px; border-radius:50%; background:#003366; display:flex; align-items:center; justify-content:center; font-weight:700; color:#ffcc00; font-size:20px; overflow:hidden; }
.portrait-img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
.user-meta h3 { margin-bottom:4px; font-size:16px; }
.user-meta p { font-size:12px; margin:2px 0; color:#003366; }

/* Buttons */
.control-btn {
  background: #0059b3;
  border:none;
  color:#fff;
  padding:8px 14px;
  border-radius:8px;
  cursor:pointer;
  font-family:'Poppins', sans-serif;
  font-size:14px;
  transition: background 0.2s;
}
.control-btn:hover {
  background:#003366;
}   
/* Table styles */
/* Table */
.schedule-wrap { 
  margin-top: 10px; 
  width: 100%; 
}

table {
  border-collapse: separate;
  border-spacing: 4px;
  width: 100%;
  table-layout: fixed;
}

th, td {
  padding: 6px;
  text-align: center;
  vertical-align: middle;
  border-radius: 6px;
  font-size: 10px;              /* smallest readable font */
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
  font-size: 11px;              /* slightly larger for header */
  letter-spacing: 0.3px;
}

td {
  background: #f9fbff;
  border: 1px solid #e0e6f0;
  height: 52px;
  color: #222;
  transition: background 0.2s;
}

td.empty:hover {
  background: #e6f0ff;
  cursor: pointer;
}

/* Reservation statuses */
td.filled { 
  background: #3399ff;
  color: #fff; 
}
td.pending { 
  background: #ffeb3b;
  color: #222; 
}
td.approved { 
  background: #4caf50;
  color: #fff; 
}
td.denied { 
  background: #f44336;
  color: #fff; 
}
td.past {
  background: #607d8b;
  color: #fff;
  cursor: not-allowed;
  pointer-events: none;
}

/* Responsive: ensure text still fits in small screens */
@media (max-width: 480px) {
  th, td {
    font-size: 9px;     /* tiniest font for mobile */
    padding: 4px;
  }
  td {
    height: 45px;
  }
}

/* Modal */
#reservationForm { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:10000; width:90%; max-width:380px; background: linear-gradient(180deg,#fffdf6,#fff3dc); border:3px solid #003366; border-radius:14px; padding:14px; box-shadow:0 30px 80px rgba(0,0,0,0.4); }
#reservationForm label { display:block; margin-top:8px; font-weight:700; font-size:13px; color:#003366; }
#reservationForm input, #reservationForm select, #reservationForm textarea { width:100%; margin-top:4px; padding:6px; border-radius:8px; border:1px solid #003366; background:#fffefb; font-size:13px; }
.modal-actions { display:flex; justify-content:space-between; margin-top:10px; }
.modal-btn { padding:8px 10px; border-radius:8px; border:2px solid #003366; background:#ffcc00; color:#003366; cursor:pointer; }
.modal-btn.cancel { background:#bbb; border-color:#999; color:#222; }

/* Responsive */
@media (max-width: 600px) {
  .title { font-size:22px; }
  .top-controls { flex-direction:column; align-items:stretch; gap:8px; }
  .wanted { flex-direction:column; align-items:flex-start; }
  .portrait { width:46px; height:46px; font-size:16px; }
  table { font-size:10px; }
  th, td { padding:4px; height:48px; font-size:10px; }
}</style>
</head>
<body>

<header class="header">
    <img src="images/logo.png" alt="TCC Logo" style="height:60px; margin-right:12px;">
    <div class="title">TCC Lab Reservations</div>
</header>

<main class="container">
    <section class="user-strip">
        <div class="user-left">
            <div class="user-card">
                <div class="portrait">
                    <?php
                    if (!empty($user['photo'])) {
                        echo '<img src="images/users/' . htmlspecialchars($user['photo']) . '" class="portrait-img" alt="User Photo">';
                    } else {
                        echo strtoupper(substr(htmlspecialchars($user['name']),0,1));
                    }
                    ?>
                </div>
                <div class="user-meta">
                    <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p><?php echo htmlspecialchars($user['section']); ?> — <?php echo htmlspecialchars($user['classification']); ?></p>
                </div>
            </div>
        </div>

        <div>
             <button class="control-btn" onclick="location.href='my_reservations.php'">My Reservations</button>
            <button class="control-btn" onclick="location.href='user_info.php'">Sign Out</button>
        </div>


<div style="
  background:#0059b3;
  color:white;
  padding:10px 14px;
  border-radius:8px;
  margin-top:20px;
  text-align:center;
  font-family:'Poppins', sans-serif;
  font-size:14px;
">
  <strong>Schedule Color Legend:</strong>
  <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:12px; margin-top:8px;">
    <div style="background:#3399ff; color:#fff; padding:6px 10px; border-radius:6px;">Admins Schedule</div>
    <div style="background:#ffeb3b; color:#222; padding:6px 10px; border-radius:6px;">Pending </div>
    <div style="background:#4caf50; color:#fff; padding:6px 10px; border-radius:6px;">Approved </div>
    <div style="background:#f44336; color:#fff; padding:6px 10px; border-radius:6px;">Denied </div>
    <div style="background:#607d8b; color:#fff; padding:6px 10px; border-radius:6px;">Past Time </div>
  </div>
</div>

    </section>

 <!-- Weekly Schedule Table -->
<section class="schedule-wrap">
    <h2 
        style="background: #0059b3; border:none; font-family:'Poppins', sans-serif; font-size:20px; color:white; padding:8px 14px; border-radius:8px; cursor:pointer; margin:14px 0; text-align:center; font-weight:500; transition: background 0.2s;"
        onmouseover="this.style.background='#004080';"
        onmouseout="this.style.background='#0059b3';">
        Current Week Schedule
    </h2>

    <?php 
    // ✅ STEP 2: Fetch approved reservations (visible to all users)
    $approvedStmt = $pdo->query("
        SELECT r.day_date, r.hour, u.name, u.section, u.classification, r.reservation_type, r.reason, r.status
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        WHERE r.status = 'Approved'
    ");
    $approvedReservations = $approvedStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <table>
    <thead>
        <tr>
            <th>Hour</th>
            <?php foreach ($weeks as $date => $dt) echo "<th>".$dt->format('D M d')."</th>"; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($hours as $hour): ?>
    <tr>
        <th><?php echo date("h A", strtotime("$hour:00")); ?></th>
        <?php foreach ($weeks as $date => $dt): ?>
        <?php
            $slotTime = new DateTime($date . " $hour:00");
            $now = new DateTime();
            $isPast = $slotTime < $now;

            $text = "";
            $finalClass = "empty";

            // ✅ STEP 3: Check if slot has approved reservation (visible to everyone)
            foreach($approvedReservations as $app){
                if($app['day_date']==$date && (int)$app['hour']==(int)$hour){
                    $text = htmlspecialchars($app['name']);
                    $finalClass = "approved"; // green slot
                    break;
                }
            }

            // check user’s own reservations (overrides)
            if(empty($text)){
                foreach($activities as $act){
                    if($act['day_date']==$date && (int)$act['hour']==(int)$hour){
                        $text = htmlspecialchars($user['name']) . " | " . htmlspecialchars($act['status']);
                        $finalClass = strtolower($act['status']);
                        break;
                    }
                }
            }

            // check admin schedule
            if (empty($text) && isset($grid[$date][$hour])) {
                $s = $grid[$date][$hour];
                $text = htmlspecialchars($s['section']);
                $finalClass = $isPast ? "past" : "filled";
            }

            if(empty($text) && $isPast){
                $finalClass = "past";
            }

            $safeDate = htmlspecialchars($date);
            $safeHour = htmlspecialchars($hour);
        ?>
        <td class="<?php echo $finalClass; ?>"
            <?php 
                if(!$isPast && $finalClass=="approved") {
                    echo "onclick=\"openDetailsModal('$safeDate','$safeHour')\" style='cursor:pointer;'";
                } elseif(!$isPast && $finalClass=="pending") {
                    echo "onclick=\"openDetailsModal('$safeDate','$safeHour')\" style='cursor:pointer;'";
                } elseif(!$isPast && $finalClass=="empty") {
                    echo "onclick=\"openReservationForm('$safeDate','$safeHour')\" style='cursor:pointer;'";
                } elseif(!$isPast && $finalClass=="filled") {
                    echo "onclick=\"openAdminScheduleModal('$safeDate','$safeHour')\" style='cursor:pointer;'";
                }
            ?>
            title="<?php 
                if($finalClass==='past') echo 'This slot is in the past.'; 
                elseif($finalClass==='filled') echo 'Already scheduled by admin.'; 
                elseif($finalClass==='approved') echo 'Approved reservation. Click for details.'; 
                else echo 'Click to reserve.'; 
            ?>">
            <?php echo $text; ?>
        </td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>
</section>



<!-- Reservation Modal -->
<div id="reservationForm">
    <h3>Make Reservation</h3>
    <input type="hidden" id="resDate">
    <input type="hidden" id="resHour">
    <label for="resType">Reservation Type:</label>
    <select id="resType" required>
        <option value="">Select Type</option>
        <option value="Solo">Solo</option>
        <option value="Group">Group</option>
        <option value="Class">Class</option>
    </select>
    <label for="resReason">Reason:</label>
    <textarea id="resReason" rows="4" placeholder="Enter reason" required></textarea>
    <div class="modal-actions">
        <button class="modal-btn" onclick="submitReservation()">Reserve</button>
        <button class="modal-btn cancel" onclick="closeReservationForm()">Cancel</button>
    </div>
</div>
<div id="modalBackdrop" onclick="closeReservationForm()"></div>
<!-- Admin Schedule Modal (Mobile Responsive) -->
<div id="adminScheduleModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
    z-index:10002; width:90%; max-width:500px; background:#fffdf6; border:3px solid #003366; border-radius:14px; padding:14px; box-shadow:0 30px 80px rgba(0,0,0,0.4);">
    <h3 style="text-align:center; color:#003366;">Admin Schedule Details</h3>

    <!-- Info Time Display -->
    <div id="infoTimeContainer" style="text-align:center; margin-top:6px; font-size:14px; color:#003366; font-weight:bold;">
        Time: <span id="infoTimeValue">--:--</span>
    </div>

    <div style="overflow-x:auto; margin-top:10px;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr style="background:#003366; color:#fff; text-align:center;">
                    <th>Date</th>
                    <th>Year and Section</th>
                </tr>
            </thead>
            <tbody id="adminScheduleBody"></tbody>
        </table>
    </div>
    <div style="text-align:center; margin-top:12px;">
        <button onclick="closeAdminScheduleModal()" style="padding:8px 12px; border-radius:8px; border:2px solid #003366; background:#ffcc00; color:#003366; cursor:pointer;">Close</button>
    </div>
</div>
<div id="adminScheduleBackdrop" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
    background:rgba(0,0,0,0.5); z-index:10001;" onclick="closeAdminScheduleModal()"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {

  function openAdminScheduleModal(date, hour) {
    // Display selected time in modal
    document.getElementById('infoTimeValue').textContent = hour ? `${hour}:00` : '--:--';

    fetch(`get_admin_schedule.php?date=${encodeURIComponent(date)}&hour=${encodeURIComponent(hour)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message);
                return;
            }

            const schedules = data.adminSchedules || [];
            const tbody = document.getElementById('adminScheduleBody');
            tbody.innerHTML = '';

            if (schedules.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;">No schedule found</td></tr>';
                return;
            }

            schedules.forEach(s => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${s.day_date}</td><td>${s.section}</td>`;
                tbody.appendChild(tr);
            });

            document.getElementById('adminScheduleModal').style.display = 'block';
            document.getElementById('adminScheduleBackdrop').style.display = 'block';
        })
        .catch(err => alert('Error fetching admin schedule: ' + err));
  }

  function closeAdminScheduleModal() {
      document.getElementById('adminScheduleModal').style.display = 'none';
      document.getElementById('adminScheduleBackdrop').style.display = 'none';
  }

  window.openAdminScheduleModal = openAdminScheduleModal;
  window.closeAdminScheduleModal = closeAdminScheduleModal;

  document.addEventListener('keydown', e => {
      if(e.key === "Escape") closeAdminScheduleModal();
  });

});
</script>


<style>
@media (max-width: 600px) {
    #adminScheduleModal table { font-size:11px; }
    #adminScheduleModal table th, #adminScheduleModal table td { padding:4px; }
}
</style>


<!-- Details Modal (Mobile Responsive) -->
<div id="detailsModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
    z-index:10001; width:90%; max-width:500px; background:#fffdf6; border:3px solid #003366; border-radius:14px; padding:14px; box-shadow:0 30px 80px rgba(0,0,0,0.4);">
    <h3 style="text-align:center; color:#003366;">Slot Reservation Details</h3>
    <div style="overflow-x:auto; margin-top:10px;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr style="background:#003366; color:#fff; text-align:center;">
                    <th>Name</th>
                    <th>Section</th>
                    <th>Classification</th>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="detailsBody"></tbody>
        </table>
    </div>
    <div style="text-align:center; margin-top:12px;">
        <button onclick="closeDetailsModal()" style="padding:8px 12px; border-radius:8px; border:2px solid #003366; background:#ffcc00; color:#003366; cursor:pointer;">Close</button>
    </div>
</div>
<div id="detailsBackdrop" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
    background:rgba(0,0,0,0.5); z-index:10000;" onclick="closeDetailsModal()"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Open reservation details modal
    function openDetailsModal(date, hour) {
        if (!date || !hour) { alert('Error: Missing date or hour'); return; }
        fetch(`get_slot_reservations.php?date=${encodeURIComponent(date)}&hour=${encodeURIComponent(hour)}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) { alert(data.message); return; }
                const tbody = document.getElementById('detailsBody');
                tbody.innerHTML = '';
                data.reservations.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${r.name}</td><td>${r.section}</td><td>${r.classification}</td><td>${r.reservation_type}</td><td>${r.reason}</td><td>${r.status}</td>`;
                    tbody.appendChild(tr);
                });
                document.getElementById('detailsModal').style.display = 'block';
                document.getElementById('detailsBackdrop').style.display = 'block';
            })
            .catch(err => alert('Error fetching reservation details: ' + err));
    }

    // Close details modal
    function closeDetailsModal() {
        const modal = document.getElementById('detailsModal');
        const backdrop = document.getElementById('detailsBackdrop');
        if(modal) modal.style.display = 'none';
        if(backdrop) backdrop.style.display = 'none';
    }

    // Open reservation form modal
    function openReservationForm(date,hour){
        document.getElementById('resDate').value=date;
        document.getElementById('resHour').value=hour;
        document.getElementById('reservationForm').style.display='block';
        document.getElementById('modalBackdrop').style.display='block';
    }

    function closeReservationForm(){
        document.getElementById('reservationForm').style.display='none';
        document.getElementById('modalBackdrop').style.display='none';
    }

    function submitReservation(){
        const date=document.getElementById('resDate').value;
        const hour=document.getElementById('resHour').value;
        const type=document.getElementById('resType').value;
        const reason=document.getElementById('resReason').value;
        if(!date||!hour||!type||!reason){ alert('Fill all fields'); return; }
        fetch('save_reservation.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`date=${encodeURIComponent(date)}&hour=${encodeURIComponent(hour)}&reservation_type=${encodeURIComponent(type)}&reason=${encodeURIComponent(reason)}`
        })
        .then(r=>r.json())
        .then(data=>{
            if(data.success){ alert('Reservation saved!'); location.reload(); }
            else alert('Error: '+data.message);
        }).catch(err=>alert('Request failed: '+err));
    }

    // Assign functions to global scope so HTML onclick can use them
    window.openDetailsModal = openDetailsModal;
    window.closeDetailsModal = closeDetailsModal;
    window.openReservationForm = openReservationForm;
    window.closeReservationForm = closeReservationForm;
    window.submitReservation = submitReservation;

    // Close modals on Escape
    document.addEventListener('keydown', e=>{
        if(e.key==="Escape") { 
            closeReservationForm(); 
            closeDetailsModal(); 
        }
    });

});
</script>

<style>
@media (max-width: 600px) {
    #detailsModal table { font-size:11px; }
    #detailsModal table th, #detailsModal table td { padding:4px; }
}
</style>
</body>
</html>
