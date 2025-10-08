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
.schedule-wrap { margin-top:10px; width:100%; }
table {
  border-collapse: separate;
  border-spacing:4px;
  width:100%;
  table-layout: fixed;
}
th, td {
  padding:8px;
  text-align:center;
  vertical-align:middle;
  border-radius:8px;
  font-size:12px;
  font-weight:600;
}
th {
  background:#003366;
  color:#fff;
  position:sticky;
  top:0;
  z-index:2;
  font-size:13px;
}
td {
  background:#f9fbff;
  border:1px solid #e0e6f0;
  height:60px;
  color:#222;
}
td.empty:hover {
  background:#e6f0ff;
  cursor:pointer;
}

/* Reservation statuses */
td.filled { 
  background:#3399ff; /* blue */
  color:#fff; 
}
td.pending { 
  background:#ffeb3b; /* yellow */
  color:#222; 
}
td.approved { 
  background:#4caf50; /* green */
  color:#fff; 
}
td.denied { 
  background:#f44336; /* red */
  color:#fff; 
}
td.past {
  background: #607d8b;   /* black */
  color:#fff;
  cursor:not-allowed; 
  pointer-events:none;
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
                    <p><?php echo htmlspecialchars($user['department']); ?> — <?php echo htmlspecialchars($user['classification']); ?></p>
                </div>
            </div>
        </div>

        <div>
            <button class="control-btn" onclick="location.href='user_info.php'">Edit Info</button>
            <button class="control-btn" onclick="document.getElementById('reservationSection').scrollIntoView({behavior:'smooth'})">My Reservations</button>
            <button class="control-btn" onclick="location.href='logout.php'">Sign Out</button>
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

                // check user activities
                foreach($activities as $act){
                    if($act['day_date']==$date && (int)$act['hour']==(int)$hour){
                        $text = htmlspecialchars($user['name']) . " | " . htmlspecialchars($act['status']);
                        $finalClass = strtolower($act['status']);
                        if($isPast) $finalClass = "past";
                        break;
                    }
                }

                // check admin grid
                if(empty($text) && isset($grid[$date][$hour])){
                    $s = $grid[$date][$hour];
                    $text = htmlspecialchars($s['department'])." | ".htmlspecialchars($s['year_level'])." | ".htmlspecialchars($s['section']);
                    $finalClass = $isPast ? "past" : "filled";
                }

                // FIX: mark past empty slots as past
                if(empty($text) && $isPast){
                    $finalClass = "past";
                }
            ?>
            <td class="<?php echo $finalClass; ?>"
                <?php if(!$isPast && $finalClass=="empty") echo "onclick=\"openReservationForm('$date','$hour')\""; ?>
                title="<?php echo $finalClass==='past' ? 'This slot is in the past.' : ($finalClass==='filled' ? 'Already scheduled by admin.' : 'Click to reserve.'); ?>">
                <?php echo $text; ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    </section>

    <!-- User Reservations -->
    <section class="reservations" id="reservationSection">
        <h2 style="font-family:'Merriweather', serif; color:#003366; margin-top:22px;">My Reservations</h2>
        <table>
            <thead><tr><th>Date</th><th>Hour</th><th>Type</th><th>Reason</th><th>Status</th></tr></thead>
            <tbody>
            <?php if(empty($activities)): ?>
                <tr><td colspan="5" style="padding:14px;text-align:center;">No reservations yet</td></tr>
            <?php else: foreach($activities as $act):
                $slotTime = new DateTime($act['day_date'].' '.str_pad($act['hour'],2,'0',STR_PAD_LEFT).':00:00');
                $displayStatus = $slotTime<new DateTime()?'Past':$act['status'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($act['day_date']); ?></td>
                    <td><?php echo date("g A", strtotime($act['hour'].":00")); ?></td>
                    <td><?php echo htmlspecialchars($act['reservation_type']); ?></td>
                    <td><?php echo htmlspecialchars($act['reason']); ?></td>
                    <td><?php echo htmlspecialchars($displayStatus); ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </section>
</main>

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

<script>
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
document.addEventListener('keydown', e=>{ if(e.key==="Escape") closeReservationForm(); });
</script>
</body>
</html>
