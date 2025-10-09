<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "includes/config.php";
require_once "includes/functions.php";
date_default_timezone_set('Asia/Manila');

/* ==== Build current week (Mon–Sun) ==== */
$today = new DateTime('today');
$today->modify('Monday this week');

$weekDates = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $today;
    $d->modify("+$i day");
    $weekDates[$d->format('Y-m-d')] = $d;
}
$weekStart = array_key_first($weekDates);
$weekEnd   = array_key_last($weekDates);

/* ==== Hours 07:00–21:00 ==== */
$hours = range(7, 21);

/* ==== Fetch reservations ==== */
$stmt = $pdo->prepare("
    SELECT r.*, u.name AS user_name, u.department, u.classification AS user_class
    FROM reservations r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.day_date BETWEEN ? AND ?
    ORDER BY r.day_date, r.hour, r.id
");
$stmt->execute([$weekStart, $weekEnd]);
$reservationsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==== Fetch admin schedules ==== */
$stmt = $pdo->prepare("
    SELECT *
    FROM schedule
    WHERE day_date BETWEEN ? AND ?
    ORDER BY day_date, hour
");
$stmt->execute([$weekStart, $weekEnd]);
$adminSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==== Prepare grid ==== */
$grid = [];
foreach ($weekDates as $dateStr => $_) {
    foreach ($hours as $hour) {
        $grid[$dateStr][$hour] = [
            'admin_schedule' => null,
            'reservations'   => [],    // only non-denied
        ];
    }
}

/* ==== Fill admin schedules ==== */
foreach ($adminSchedules as $s) {
    if (isset($grid[$s['day_date']][$s['hour']])) {
        $grid[$s['day_date']][$s['hour']]['admin_schedule'] = [
            'department' => $s['department'],
            'year_level' => $s['year_level'],
            'section'    => $s['section'],
        ];
    }
}

/* ==== Fill reservations (exclude denied) ==== */
foreach ($reservationsRaw as $r) {
    $st = strtolower(trim($r['status']));
    if ($st === 'denied') continue;
    if (isset($grid[$r['day_date']][$r['hour']])) {
        $grid[$r['day_date']][$r['hour']]['reservations'][] = [
            'id'               => (int)$r['id'],
            'user_name'        => $r['user_name'],
            'department'       => $r['department'],
            'user_class'       => $r['user_class'],
            'reservation_type' => $r['reservation_type'],
            'reason'           => $r['reason'],
            'status'           => $st,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>TCC Manage Reservations</title>
<link rel="stylesheet" href="include/mobile.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{
  font-family:'Roboto',sans-serif;
  background:url('images/tcc.jpg') center/cover fixed no-repeat;
  color:#002855; /* deep blue */
  padding:24px;
  overflow-x:hidden;
  overflow-y:auto;
}

/* header */
.header{display:flex;flex-direction:column;align-items:center;margin-bottom:18px;}
.header img{width:90px;margin-bottom:10px;}
.header .title{
  font-size:28px;
  color:#FFD700; /* gold */
  text-shadow:2px 2px 4px rgba(0,0,0,0.4);
  font-weight:700;
  text-align:center;
}

/* container */
.container{
  width:100%;max-width:1240px;margin:0 auto 20px;
  background:tranparent-white; /* semi-transparent for bg visibility */
  border:none;
  border-radius:16px;
  padding:16px;
  box-shadow:0 12px 40px rgba(0,0,0,0.3);
  position:relative;z-index:20;
}

/* controls */
.top-controls{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px;}
.controls-left{display:flex;align-items:center;gap:12px;}
.control-btn{
  background:linear-gradient(180deg,#1E90FF,#004080);
  border:none;color:#fff;
  padding:8px 12px;border-radius:10px;cursor:pointer;
  box-shadow:0 4px 8px rgba(0,0,0,0.3);font-size:13px;font-weight:bold;
}
.control-btn:hover{transform:translateY(-2px);background:linear-gradient(180deg,#004080,#1E90FF);}

/* profile card */
.wanted{
  display:flex;gap:10px;align-items:center;
  background:#f4faff;
  padding:8px 10px;border-radius:12px;
  border:2px solid #1E90FF;box-shadow:0 6px 12px rgba(0,0,0,.2);
}
.portrait{
  width:64px;height:64px;border-radius:50%;
  background:#FFD700;
  display:flex;align-items:center;justify-content:center;
  font-weight:700;color:#002855;font-size:20px;
}

/* schedule table */
.schedule-wrap { margin-top:6px;width:100%;overflow-x:auto; }
.schedule-wrap table { width:100%; border-collapse:separate; border-spacing:4px; table-layout:fixed; }
.schedule-wrap th, .schedule-wrap td {
  text-align:center; vertical-align:middle;
  border-radius:10px; font-weight:600;
  word-wrap:break-word; overflow-wrap:break-word; white-space:normal;
  padding:8px; height:60px; font-size:12px;
}
.schedule-wrap th {
  background:#002855; color:#FFD700;
  position:sticky;top:0;z-index:3; font-size:13px;
}
.schedule-wrap td {
  background:#ffffff;
  border:1px solid #ccc;
  color:#002855;
  transition:transform .12s ease, box-shadow .12s ease;
}
.schedule-wrap td.reservation.pending { background:#f28b82;color:#fff; }
.schedule-wrap td.reservation.approved { background:#7bd389;color:#fff; }
.schedule-wrap td.past { background:#d9d9d9;color:#555;cursor:not-allowed; }
.schedule-wrap td.clickable:hover {
  transform:translateY(-3px);
  box-shadow:0 6px 12px rgba(0,0,0,0.18);
  background:#f0f8ff;
  cursor:pointer;
}
   /* Footer */
    footer{
      text-align:center;
      padding:12px;
      color:#FFD700;
      font-family:'Merriweather', serif;
      background:rgba(30,144,255,0.95);
      border-top:4px solid #FFD700;
      font-size:.9rem;
      letter-spacing:1px;
    }
/* responsive */
@media (max-width:600px){
  body{padding:10px;}
  .header .title{font-size:22px;}
  .top-controls{flex-direction:column;align-items:stretch;gap:8px;}
  .wanted{flex-direction:column;align-items:flex-start;}
  .portrait{width:52px;height:52px;font-size:16px;}
  .schedule-wrap th, .schedule-wrap td{padding:4px 3px;font-size:10px;height:48px;}
}
@media (max-width:480px){
  .control-btn{font-size:10px;padding:4px 6px;}
  .schedule-wrap th, .schedule-wrap td{font-size:9px;padding:3px 2px;}
}
</style>
</head>
<body>
<header class="header">
  <img src="images/logo.png" alt="TCC Logo">
  <div class="title">Talisay City College — Reservations Manager</div>
</header>

  <main class="container">
    <div class="top-controls">
      <div class="controls-left">
        <div class="wanted">
          <div class="portrait"><?php echo strtoupper(substr(htmlspecialchars($_SESSION['admin_name']),0,1)); ?></div>
          <div>
        <?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong><br>
            <small style="color:#6b4b37;"> Computer Laboratory Reservations — Talisay City College</small>
          </div>
        </div>
      </div>
      <div>
        <button class="control-btn" onclick="location.href='admin_dashboard.php'">Dashboard</button>
        <button class="control-btn" onclick="location.href='logout.php'">Logout</button>
      </div>
    </div>

    <div class="schedule-wrap">
      <table>
        <thead>
          <tr>
            <th>Time</th>
            <?php foreach ($weekDates as $date => $dt): ?>
              <th><?php echo $dt->format('D'); ?><br><?php echo $dt->format('M j'); ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($hours as $hour): ?>
          <tr>
            <td class="time-col"><strong><?php echo sprintf("%02d:00", $hour); ?></strong></td>
            <?php foreach ($weekDates as $date => $dt):
              $slot     = $grid[$date][$hour];
              $now      = new DateTime();
              $slotTime = new DateTime("$date $hour:00");
              $isPast   = $slotTime < $now;

              $resCount        = count($slot['reservations']);       // all are non-denied
              $nonDeniedCount  = $resCount;                           // same as above

              // resolve class + content
              $classes = [];
              $clickable = false;
              $contentHtml = '';

              if ($slot['admin_schedule'] !== null) {
                  $classes[] = 'admin-schedule';
                  if ($isPast) $classes[] = 'past';
                  $clickable = false;
                  $ad = $slot['admin_schedule'];
                  

  $contentHtml =
      htmlspecialchars($ad['department']) . "<br>" .
      "<span class='small'>" . ordinal((int)$ad['year_level']) .
      " Year • Sec " . htmlspecialchars($ad['section']) . "</span>";


              } elseif ($isPast) {
                  $classes[] = 'past';
                  $clickable = false;
                  $contentHtml = ''; // past empty stays empty
              } elseif ($resCount > 0) {
                  // find highest status (approved > pending)
                  $statusPriority = ['approved'=>2,'pending'=>1];
                  $highest = 'pending';
                  foreach ($slot['reservations'] as $r) {
                      if ($statusPriority[$r['status']] > $statusPriority[$highest]) $highest = $r['status'];
                  }
                  $classes[] = 'reservation '.$highest;
                  if ($nonDeniedCount >= 3) $classes[] = 'fully-booked';
                  $clickable = true;

                  // Display either first name or count
                  if ($resCount === 1) {
                      $contentHtml = htmlspecialchars($slot['reservations'][0]['user_name']);
                  } else {
                      $contentHtml = $resCount . " reservations";
                  }
              } else {
                  // future empty slot
                  $contentHtml = '';
                  $clickable = false;
              }
            ?>
            <td class="<?php echo implode(' ', $classes).($clickable?' clickable':''); ?>"
                data-date="<?php echo $date; ?>"
                data-hour="<?php echo $hour; ?>"
                data-clickable="<?php echo $clickable ? '1' : '0'; ?>"
                data-reservations='<?php echo json_encode($slot["reservations"], JSON_HEX_APOS|JSON_HEX_QUOT); ?>'>
                <?php echo $contentHtml; ?>
            </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

 <!-- Reservation Modal with Overlay -->
<style>
/* ================= OVERLAY ================= */
#reservationOverlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0, 51, 102, 0.7); /* TCC blue overlay */
  z-index: 1000;
  display: none;             
  justify-content: center;   
  align-items: center;       
  padding: 10px;
}

/* ================= MODAL BOX ================= */
#reservationForm {
  background: linear-gradient(180deg, #fff4c2, #ffe27a); /* TCC yellow gradient */
  width: 100%;
  max-width: 420px;
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 16px;
  padding: 22px;
  position: relative;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
  border: 3px solid #003366; /* TCC dark blue border */
  font-family: 'Pirata One', cursive; /* TCC themed font */
  color: #003366;
}

/* ================= CLOSE BUTTON ================= */
#reservationForm .modal-close {
  position: absolute;
  top: 8px;
  right: 12px;
  background: none;
  border: none;
  font-size: 26px;
  cursor: pointer;
  color: #cc9900; /* TCC gold */
}

/* ================= HEADING ================= */
#reservationForm h3 {
  margin-top: 0;
  text-align: center;
  font-size: 22px;
  color: #003366;
  text-shadow: 1px 1px 2px #cc9900;
}

/* ================= FORM ELEMENTS ================= */
#reservationForm label {
  display: block;
  margin-top: 10px;
  font-weight: bold;
  font-size: 14px;
  color: #003366;
}

#reservationForm input,
#reservationForm select,
#reservationForm textarea {
  width: 100%;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid #003366;
  background: #fff9e6; /* light TCC cream */
  box-sizing: border-box;
  font-size: 14px;
  color: #003366;
  font-family: 'Arial', sans-serif;
}

/* ================= ACTION BUTTONS ================= */
#reservationForm .modal-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 15px;
  gap: 10px;
  flex-wrap: wrap;
}

.modal-btn {
  flex: 1;
  padding: 10px;
  border-radius: 8px;
  border: 2px solid #003366;
  background: #ffcc00; /* TCC gold */
  color: #003366;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  font-size: 14px;
}

.modal-btn:hover {
  background: #003366;
  color: #ffcc00;
}

.modal-btn.cancel {
  background: #ccc;
  border-color: #999;
  color: #222;
}

/* ================= FULLY BOOKED MESSAGE ================= */
#fullyBookedMessage {
  display: none;
  color: #cc0000; /* TCC warning red */
  font-weight: 700;
  text-align: center;
  margin-top: 10px;
  font-size: 14px;
}

/* ================= NAVIGATION BUTTONS ================= */
#navButtons {
  display: none;
  gap: 8px;
  justify-content: center;
  margin-top: 10px;
}

#navButtons button {
  padding: 6px 10px;
  font-size: 14px;
  border-radius: 6px;
  border: 2px solid #003366;
  background: #ffcc00;
  color: #003366;
  cursor: pointer;
}

#navButtons button:hover {
  background: #003366;
  color: #ffcc00;
}

/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 600px) {
  #reservationOverlay {
    padding: 0;
    justify-content: center;
    align-items: center;
  }

  #reservationForm {
    width: 90%;
    max-width: 320px;
    padding: 14px;
    border-radius: 12px;
  }

  #reservationForm h3 {
    font-size: 18px;
  }

  #reservationForm label {
    font-size: 12px;
  }

  #reservationForm input,
  #reservationForm select,
  #reservationForm textarea {
    font-size: 12px;
    padding: 6px;
  }

  .modal-btn {
    font-size: 12px;
    padding: 6px;
  }
}

@media (max-width: 400px) {
  #reservationForm {
    width: 85%;
    max-width: 280px;
    padding: 10px;
  }

  #reservationForm h3 {
    font-size: 16px;
  }

  .modal-actions {
    flex-direction: column;
    gap: 6px;
  }

  .modal-btn {
    width: 100%;
    font-size: 12px;
    padding: 6px;
  }
}
</style>


  <div id="reservationOverlay">
    <div id="reservationForm" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <button class="modal-close" onclick="closeReservationModal()">×</button>
      <h3 id="modalTitle">Reservation Details</h3>
      <form id="formReservation" method="POST" action="update_reservation.php" style="display: flex; flex-direction: column; gap: 10px;">
        <input type="hidden" name="reservation_id" id="reservation_id" />
        
        <label for="user_name">Name</label>
        <input type="text" id="user_name" readonly />

        <label for="department">Department</label>
        <input type="text" id="department" readonly />

        <label for="user_class">Classification</label>
        <input type="text" id="user_class" readonly />

        <label for="reservation_type">Type</label>
        <input type="text" id="reservation_type" readonly />

        <label for="reason">Reason</label>
        <textarea id="reason" rows="3" readonly></textarea>

        <label for="status">Status</label>
        <select name="status" id="status" required>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="denied">Denied</option>
        </select>

        <div class="modal-actions">
          <button type="submit" class="modal-btn">Save</button>
          <button type="button" class="modal-btn cancel" onclick="closeReservationModal()">Cancel</button>
        </div>

        <div id="fullyBookedMessage">
          This slot is fully booked. No new bookings allowed.
        </div>
      </form>

      <div id="navButtons">
        <button id="btnPrev">&laquo; Prev</button>
        <button id="btnNext">Next &raquo;</button>
      </div>
    </div>
  </div>  
<script>
(function() {
  const overlay = document.getElementById('reservationOverlay');
  const modal = document.getElementById('reservationForm');
  const form = document.getElementById('formReservation');
  const cancelBtn = modal.querySelector('.cancel');
  const prevBtn = document.getElementById('btnPrev');
  const nextBtn = document.getElementById('btnNext');
  const fullyBookedMsg = document.getElementById('fullyBookedMessage');
  const navButtons = document.getElementById('navButtons');

  const inputReservationId = document.getElementById('reservation_id');
  const inputUserName = document.getElementById('user_name');
  const inputDepartment = document.getElementById('department');
  const inputUserClass = document.getElementById('user_class');
  const inputReservationType = document.getElementById('reservation_type');
  const inputReason = document.getElementById('reason');
  const selectStatus = document.getElementById('status');

  let currentCell = null;
  let currentSlotReservations = [];
  let currentIndex = 0;

  window.closeReservationModal = function() {
    overlay.style.display = 'none';
    form.reset();
    currentCell = null;
    currentSlotReservations = [];
    currentIndex = 0;
  };

  function renderCell(cell, reservations) {
    cell.classList.remove('reservation','pending','approved','fully-booked','clickable');
    if (!reservations || reservations.length === 0) {
      cell.innerText = '';
      cell.setAttribute('data-reservations', JSON.stringify([]));
      return;
    }
    let highest = reservations.some(r=>r.status==='approved') ? 'approved' : 'pending';
    cell.classList.add('reservation', highest, 'clickable');
    if (reservations.length >= 3) cell.classList.add('fully-booked');
    cell.innerText = reservations.length === 1 ? reservations[0].user_name : reservations.length + ' reservations';
    cell.setAttribute('data-reservations', JSON.stringify(reservations));
  }

  function showReservation(index) {
    if (index < 0 || index >= currentSlotReservations.length) return;
    currentIndex = index;
    const res = currentSlotReservations[index];

    inputReservationId.value = res.id;
    inputUserName.value = res.user_name || '';
    inputDepartment.value = res.department || '';
    inputUserClass.value = res.user_class || '';
    inputReservationType.value = res.reservation_type || '';
    inputReason.value = res.reason || '';
    selectStatus.value = res.status || 'pending';

    fullyBookedMsg.style.display = currentSlotReservations.length >= 3 ? 'block' : 'none';

    if (currentSlotReservations.length > 1) {
      navButtons.style.display = 'flex';
      prevBtn.disabled = (currentIndex === 0);
      nextBtn.disabled = (currentIndex === currentSlotReservations.length - 1);
    } else {
      navButtons.style.display = 'none';
    }
  }

  // Clickable reservation cells
  document.querySelectorAll('td[data-clickable="1"]').forEach(td => {
    td.addEventListener('click', () => {
      try {
        currentSlotReservations = JSON.parse(td.getAttribute('data-reservations')) || [];
      } catch {
        currentSlotReservations = [];
      }
      if (currentSlotReservations.length === 0) return;

      currentCell = td;
      showReservation(0);
      overlay.style.display = 'flex';
      selectStatus.focus();
    });
  });

  // Prev/Next navigation
  prevBtn.addEventListener('click', () => { if (currentIndex > 0) showReservation(currentIndex - 1); });
  nextBtn.addEventListener('click', () => { if (currentIndex < currentSlotReservations.length - 1) showReservation(currentIndex + 1); });
  cancelBtn.addEventListener('click', e => { e.preventDefault(); closeReservationModal(); });

  // Save form via AJAX (closes modal automatically after success)
  form.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(form);

    fetch(form.action, { method:'POST', body:formData, credentials:'same-origin', headers:{'Accept':'application/json'} })
      .then(resp => resp.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Update failed');

        const updatedId = parseInt(inputReservationId.value, 10);
        const newStatus = selectStatus.value.toLowerCase();

        const idx = currentSlotReservations.findIndex(r => r.id === updatedId);
        if (idx !== -1) {
          if (newStatus === 'denied') currentSlotReservations.splice(idx, 1);
          else currentSlotReservations[idx].status = newStatus;
        }

        if (currentCell) renderCell(currentCell, currentSlotReservations);

        if (currentSlotReservations.length === 0) closeReservationModal();
        else if (currentIndex >= currentSlotReservations.length) currentIndex = currentSlotReservations.length - 1;

        if (currentSlotReservations.length > 0) showReservation(currentIndex);

        // ✅ close modal automatically
        closeReservationModal();
        alert('Reservation status updated!');
      })
      .catch(err => alert('Failed to update reservation: ' + (err.message || 'network error')));
  });

  // ESC closes modal
  window.addEventListener('keydown', e => {
    if (e.key === 'Escape' && overlay.style.display === 'flex') closeReservationModal();
  });
})();
</script>

   <footer>
    &copy; <?= date('Y') ?> Talisay City College - Computer Lab Reservation System
  </footer>
  </body>
</html>
