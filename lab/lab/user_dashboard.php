<?php
session_start();
require_once "includes/config.php";

date_default_timezone_set('Asia/Manila');

// Redirect if user is not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    header("Location: user_info.php");
    exit();
}

$userId = $_SESSION['user_id'];
$user   = $_SESSION['user'];

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

// Fetch schedules (admin-set)
$stmt = $pdo->query("SELECT * FROM schedule");
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize schedules by slot
$grid = [];
foreach ($schedules as $s) {
    $grid[$s['day_date']][$s['hour']] = $s;
}

// Fetch all approved reservations for this week
$startDate = array_key_first($weeks);
$endDate   = array_key_last($weeks);

$stmt = $pdo->prepare("
  SELECT r.id, r.day_date, r.hour, r.reservation_type, r.reason, r.status,
       u.name, u.department, u.classification
FROM reservations r
JOIN users u ON r.user_id = u.id
WHERE r.status = 'Approved'
AND r.day_date BETWEEN ? AND ?

");
$stmt->execute([$startDate, $endDate]);
$approvedReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize approved reservations by slot
$approvedBySlot = [];
foreach ($approvedReservations as $ar) {
    $approvedBySlot[$ar['day_date']][$ar['hour']][] = $ar;
}

// Fetch current user's reservations (future only)
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
.cell-content {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    justify-content: center;
    align-items: center;
    font-size: 12px;
    padding: 2px;
}

.cell-content span {
    background: #e6f0ff;
    border: 1px solid #99bbff;
    border-radius: 6px;
    padding: 2px 6px;
    white-space: nowrap;
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
            <button class="control-btn" onclick="location.href='my_reservations.php'">My Reservations</button>
            <button class="control-btn" onclick="location.href='user_info'">Sign Out</button>
        </div>
    </section>
    

    <!-- Weekly Schedule Table -->
    <section class="schedule-wrap">
        <h2 style="background: #0059b3; border:none; font-family:'Poppins', sans-serif; font-size:20px; color:white; padding:8px 14px; border-radius:8px; margin:14px 0; text-align:center;">
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
            $isPast = $slotTime < $now; // real-time past check

            $texts = [];
            $finalClass = "empty";

            // 1. Check if current user reserved this slot
            $userRes = null;
            foreach($activities as $act){
                if($act['day_date']==$date && (int)$act['hour']==(int)$hour){
                    $userRes = $act;
                    break;
                }
            }
            if($userRes){
                $texts[] = htmlspecialchars($user['name']);
                $finalClass = strtolower($userRes['status']);
            }

            // 2. Add other approved reservations (exclude current user)
            $slotReservations = $approvedBySlot[$date][$hour] ?? [];
            foreach($slotReservations as $res){
                if($res['name'] !== $user['name']){
                    $texts[] = htmlspecialchars($res['name']);
                    $finalClass = "approved";
                }
            }

            // 3. Admin schedule (if empty)
            if(empty($texts) && isset($grid[$date][$hour])){
                $s = $grid[$date][$hour];
                $texts[] = htmlspecialchars($s['department']);
                $finalClass = "filled";
            }

            // 4. Mark as past if slot is in the past (overrides other statuses)
            if($isPast){
                $finalClass = "past";
            }

            $text = implode("<br>", $texts);
        ?>
        <td class="<?php echo $finalClass; ?>"
            <?php if(!$isPast): ?>
                onclick="<?php echo $finalClass=='empty'
                    ? "openReservationForm('$date','$hour')"
                    : "openDetails('$date','$hour')"; ?>"
            <?php endif; ?>
        >
            <div class="cell-content">
                <?php echo $text; ?>
            </div>
        </td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
</tbody>

        </table>
    </section>
</main>
<!-- Reservation Modal (Professional Design) -->
<div id="reservationForm" class="modal">
    <div class="modal-header">
        <h3 id="modalTitle">Make Reservation</h3>
        <span class="close-btn" onclick="closeReservationForm()">&times;</span>
    </div>
    <div class="modal-body">
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
    </div>
    <div class="modal-actions">
        <button class="modal-btn save" onclick="submitReservation()">Reserve</button>
        <button class="modal-btn cancel" onclick="closeReservationForm()">Cancel</button>
    </div>
</div>
<div id="modalBackdrop" onclick="closeReservationForm()"></div>

<style>
/* Modal Base */
.modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 400px;
    background: #fffdf6;
    border: 3px solid #003366;
    border-radius: 16px;
    box-shadow: 0 30px 80px rgba(0,0,0,0.35);
    z-index: 10000;
    font-family: 'Roboto', sans-serif;
    overflow: hidden;
    animation: fadeIn 0.3s ease;
}

/* Backdrop */
#modalBackdrop {
    display: none;
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.45);
    z-index: 9999;
}

/* Header */
.modal-header {
    background: #003366;
    color: #FFD700;
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h3 {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-size: 18px;
}
.close-btn {
    font-size: 22px;
    cursor: pointer;
    transition: color 0.2s;
}
.close-btn:hover { color: #ffcc00; }

/* Body */
.modal-body {
    padding: 16px 18px;
}
.modal-body label {
    display:block;
    margin-top: 10px;
    font-weight: 700;
    color: #003366;
    font-size: 14px;
}
.modal-body input, 
.modal-body select, 
.modal-body textarea {
    width:100%;
    margin-top: 6px;
    padding:8px 10px;
    border-radius:8px;
    border:1px solid #003366;
    background:#fffefb;
    font-size:14px;
    resize: vertical;
}

/* Buttons */
.modal-actions {
    display:flex;
    justify-content:flex-end;
    gap:10px;
    padding: 14px 16px;
    border-top: 1px solid #e0e6f0;
    background: #f5f7fb;
}
.modal-btn {
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 600;
    border: 2px solid #003366;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
}
.modal-btn.save {
    background: #FFD700;
    color: #003366;
}
.modal-btn.save:hover {
    background: #e6c200;
}
.modal-btn.cancel {
    background: #ddd;
    border-color: #999;
    color: #333;
}
.modal-btn.cancel:hover {
    background: #ccc;
}

/* Fade in animation */
@keyframes fadeIn {
    from {opacity: 0; transform: translate(-50%, -45%);}
    to {opacity: 1; transform: translate(-50%, -50%);}
}
</style>

<script>
    function openDetails(date,hour){
    fetch('get_slot_details.php?date='+encodeURIComponent(date)+'&hour='+encodeURIComponent(hour))
    .then(r=>r.text())
    .then(html=>{
        const div=document.createElement('div');
        div.id="detailsModal";
        div.innerHTML=html;
        document.body.appendChild(div);
    })
    .catch(err=>alert("Failed to load details: "+err));
}

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
