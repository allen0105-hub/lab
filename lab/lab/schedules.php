<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "includes/config.php";

// Ensure correct timezone
date_default_timezone_set('Asia/Manila');

// Fetch schedules from database
$stmt = $pdo->query("SELECT * FROM schedule");
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize schedules by date and hour
$grid = [];
foreach ($schedules as $s) {
    $grid[$s['day_date']][$s['hour']] = $s;
}

// Generate two weeks: current and next (Monday to Sunday)
$today = new DateTime();
$today->modify('Monday this week');

$weeks = ['current' => [], 'next' => []];

// Current week
for ($i = 0; $i < 7; $i++) {
    $date = clone $today;
    $date->modify("+$i day");
    $weeks['current'][$date->format('Y-m-d')] = $date;
}

// Next week
for ($i = 7; $i < 14; $i++) {
    $date = clone $today;
    $date->modify("+$i day");
    $weeks['next'][$date->format('Y-m-d')] = $date;
}

// Hours 7 AM to 9 PM
$hours = range(7, 21);

// Options for dropdowns
$departments = ['IT', 'BSHM', 'EDUC'];
$year_levels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TCC — Manage Schedules</title>
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
<style>
/* ============================
   TALISAY CITY COLLEGE THEME
   ============================ */
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: 'Roboto', sans-serif; overflow-x:hidden; overflow-y:auto; }

body {
    background: url('images/tcc.jpg') no-repeat center center fixed;
    background-size: cover;
    color:#003366; padding: 24px;
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


/* Container */
.container {
  width:100%;
  max-width:1240px;
  margin:0 auto 20px;
  background:rgba(255, 255, 255, 0.3) /* semi-transparent for bg visibility */
  border-radius: 12px;
  padding:20px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

/* Top controls */
.top-controls {
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom: 16px;
}
.controls-left {
  display:flex;
  align-items:center;
  gap:12px;
}
.wanted {
  display:flex;
  gap:10px;
  align-items:center;
  background:#f2f7ff;
  padding:10px 14px;
  border-radius:10px;
  border:2px solid #003366;
  box-shadow:0 4px 10px rgba(0,0,0,0.15);
}
.portrait {
  width:54px;
  height:54px;
  border-radius:50%;
  background:#003366;
  color:#fff;
  display:flex;
  align-items:center;
  justify-content:center;
  font-weight:700;
  font-size:20px;
}

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

/* Section title */
.section-title {
   background: #0059b3;
   border:none;
  font-family:'Poppins', sans-serif;
  font-size:20px;
  color:white;
  padding:8px 14px;
  border-radius:8px;
   cursor:pointer;
  margin:14px 0;
  text-align:center;
  font-weight:500;
  transition: background 0.2s;
}

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
td.filled {
  background:#cce5ff;
  color:#003366;
}
td.past {
  background:#607d8b; /* slightly darker gray */
  color:#607d8b;
  cursor:not-allowed;
}


/* Modal */
#scheduleModal {
  display: none;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 9999;
  width: 90%;
  max-width: 500px; /* bigger modal */
  background: #f9f9ff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
  font-family: 'Poppins', sans-serif;
}

/* Modal title */
#scheduleModal h3 {
  color: #003366;
  margin-bottom: 16px;
  font-size: 20px;
  text-align: center;
  font-weight: 700;
}

/* Labels & Inputs */
#scheduleModal label {
  display: block;
  margin-top: 12px;
  color: #003366;
  font-weight: 600;
  font-size: 14px;
}
#scheduleModal select, 
#scheduleModal textarea, 
#scheduleModal input {
  width: 100%;
  padding: 8px 10px;
  margin-top: 6px;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 14px;
  outline: none;
  transition: border 0.2s, box-shadow 0.2s;
}
#scheduleModal select:focus, 
#scheduleModal textarea:focus, 
#scheduleModal input:focus {
  border-color: #0059b3;
  box-shadow: 0 0 6px rgba(0,89,179,0.3);
}

/* Modal actions / buttons */
#scheduleModal .modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
  justify-content: flex-end;
  flex-wrap: wrap;
}

/* Button styles */
.modal-btn {
  padding: 10px 16px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #0059b3, #003366);
  color: #fff;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}
.modal-btn:hover {
  background: linear-gradient(135deg, #003366, #001f4d);
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

.modal-btn.cancel {
  background: #ccc;
  color: #222;
}
.modal-btn.cancel:hover {
  background: #bbb;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Mobile responsive modal */
@media (max-width: 600px) {
  #scheduleModal {
    width: 95%;
    padding: 16px;
  }
  #scheduleModal h3 {
    font-size: 18px;
  }
  #scheduleModal label {
    font-size: 13px;
  }
  #scheduleModal select, 
  #scheduleModal textarea, 
  #scheduleModal input {
    font-size: 13px;
    padding: 6px 8px;
  }
  .modal-btn {
    flex: 1 1 100%;
    text-align: center;
  }
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

/* Responsive */
@media (max-width: 600px) {
  .title { font-size:22px; }
  .top-controls { flex-direction:column; align-items:stretch; gap:8px; }
  .wanted { flex-direction:column; align-items:flex-start; }
  .portrait { width:46px; height:46px; font-size:16px; }
  table { font-size:10px; }
  th, td { padding:4px; height:48px; font-size:10px; }
}
</style>
</head>
<body>

<!-- header -->
<header class="header">
  <img src="images/logo.png" alt="TCC Logo">
  <div class="title">Talisay City College — Schedule Manager</div>
</header>

<!-- main container -->
<main class="container">
  <div class="top-controls">
    <div class="controls-left">
      <div class="wanted">
        <div class="portrait"><?php echo strtoupper(substr(htmlspecialchars($_SESSION['admin_name']),0,1)); ?></div>
        <div>
          <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong><br>
          <small style="color:#003366;">Admin — Talisay City College</small>
        </div>
      </div>
    </div>
    <div>
      <button class="control-btn" onclick="location.href='admin_dashboard.php'">Dashboard</button>
      <button class="control-btn" onclick="location.href='logout.php'">Logout</button>
    </div>
  </div>

  <!-- schedule grid -->
  <section class="schedule-wrap">
    <div class="section-title">Current and Next Week Schedules</div>

    <?php foreach (['current'=>'','next'=>''] as $key=>$title): ?>
      <h3 style="color:#003366; margin:12px 0;"><?php echo $title; ?></h3>
   <table>
  <thead>
      <tr>
          <th>Hour</th>
          <?php foreach ($weeks[$key] as $date => $dt): ?>
              <th><?php echo $dt->format('D M d'); ?></th>
          <?php endforeach; ?>
      </tr>
  </thead>
  <tbody>
      <?php foreach ($hours as $hour): ?>
          <tr>
              <th><?php echo date("g A", strtotime("$hour:00")); ?></th>
              <?php foreach ($weeks[$key] as $date => $dt): ?>
                  <?php
                      // Determine if slot is past
                      $slotTime = new DateTime($dt->format('Y-m-d') . ' ' . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00:00');
                      $isPast = $slotTime < new DateTime();

                      // Determine cell class and text
                      if (isset($grid[$date][$hour])) {
                          $class = $isPast ? 'past' : 'filled';
                          $text = htmlspecialchars($grid[$date][$hour]['department']); // only dept
                      } else {
                          $class = $isPast ? 'past' : 'empty';
                          $text = '';
                      }

                      // Onclick only for future cells
                      $onclick = '';
                      if (!$isPast) {
                          $onclick = isset($grid[$date][$hour])
                              ? "onclick=\"openScheduleModal('{$date}', {$hour}, true)\""
                              : "onclick=\"openScheduleModal('{$date}', {$hour}, false)\"";
                      }
                  ?>
                 <td class="<?php echo $class; ?>"
    data-date="<?php echo $date; ?>"
    data-hour="<?php echo $hour; ?>"
    data-filled="<?php echo isset($grid[$date][$hour]) ? '1':'0'; ?>"
    data-dept="<?php echo isset($grid[$date][$hour]) ? htmlspecialchars($grid[$date][$hour]['department']) : ''; ?>"
    data-year="<?php echo isset($grid[$date][$hour]) ? htmlspecialchars($grid[$date][$hour]['year_level']) : ''; ?>"
    data-section="<?php echo isset($grid[$date][$hour]) ? htmlspecialchars($grid[$date][$hour]['section']) : ''; ?>"
>
    <div class="schedule-info"><?php echo $text; ?></div>
</td>

              <?php endforeach; ?>
          </tr>
      <?php endforeach; ?>
  </tbody>
</table>

    <?php endforeach; ?>
  </section>
</main>

<!-- Schedule Modal -->
<div id="scheduleModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3 id="modalTitle">Schedule</h3>

    <!-- View Details -->
    <div id="viewDetails" style="display:none;">
      <p><strong>Department:</strong> <span id="viewDept"></span></p>
      <p><strong>Year Level:</strong> <span id="viewYear"></span></p>
      <p><strong>Section:</strong> <span id="viewSection"></span></p>
      <div style="margin-top:10px;">
        <button class="modal-btn" onclick="showEditForm()">Edit</button>
        <button class="modal-btn cancel" onclick="deleteSchedule()">Delete</button>
        <button class="modal-btn cancel" onclick="closeModal()">Close</button>
      </div>
    </div>

    <!-- Add/Edit Form -->
    <div id="formContainer" style="display:none;">
      <input type="hidden" id="modalFormDate">
      <input type="hidden" id="modalFormHour">

      <label>Department:</label>
      <select id="modalFormDept">
        <?php foreach($departments as $d): ?>
          <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
        <?php endforeach; ?>
      </select>

      <label>Year Level:</label>
      <select id="modalFormYear">
        <?php foreach($year_levels as $y): ?>
          <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
        <?php endforeach; ?>
      </select>

      <label>Section:</label>
      <input type="text" id="modalFormSection" placeholder="Enter section" required>

      <div class="modal-actions">
        <button class="modal-btn" onclick="saveSchedule(true)">Save</button>
        <button class="modal-btn cancel" onclick="closeModal()">Cancel</button>
      </div>
    </div>

  </div>
</div>

<script>
let currentDate = '';
let currentHour = '';
let isFilled = false;

function openScheduleModal(date, hour, filled) {
  currentDate = date;
  currentHour = hour;
  isFilled = filled;

  const modal = document.getElementById('scheduleModal');
  const viewDetails = document.getElementById('viewDetails');
  const formContainer = document.getElementById('formContainer');
  const modalTitle = document.getElementById('modalTitle');

  if(filled) {
    // Filled: show View Details first
    const cell = document.querySelector(`td[data-date="${date}"][data-hour="${hour}"]`);
const dept = cell.dataset.dept || '';
const year = cell.dataset.year || '';
const section = cell.dataset.section || '';


    modalTitle.innerText = 'Schedule Details';
    viewDetails.style.display = 'block';
    formContainer.style.display = 'none';

    document.getElementById('viewDept').innerText = dept;
    document.getElementById('viewYear').innerText = year;
    document.getElementById('viewSection').innerText = section;

  } else {
    // Empty: show Add Form
    modalTitle.innerText = 'Add Schedule';
    viewDetails.style.display = 'none';
    formContainer.style.display = 'block';

    document.getElementById('modalFormDate').value = date;
    document.getElementById('modalFormHour').value = hour;
    document.getElementById('modalFormDept').selectedIndex = 0;
    document.getElementById('modalFormYear').selectedIndex = 0;
    document.getElementById('modalFormSection').value = '';
  }

  modal.style.display = 'block';
}

function closeModal() {
  document.getElementById('scheduleModal').style.display = 'none';
}

function showEditForm() {
  document.getElementById('viewDetails').style.display = 'none';
  document.getElementById('formContainer').style.display = 'block';

  document.getElementById('modalFormDate').value = currentDate;
  document.getElementById('modalFormHour').value = currentHour;

  document.getElementById('modalFormDept').value = document.getElementById('viewDept').innerText;
  document.getElementById('modalFormYear').value = document.getElementById('viewYear').innerText;
  document.getElementById('modalFormSection').value = document.getElementById('viewSection').innerText;
}

function deleteSchedule() {
  if(!confirm('Are you sure you want to delete this schedule?')) return;

  fetch('delete_schedule.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`date=${encodeURIComponent(currentDate)}&hour=${encodeURIComponent(currentHour)}`
  }).then(res=>res.json())
    .then(data=>{
      if(!data.success) throw new Error(data.message || 'Failed');
      alert('Deleted successfully');
      closeModal();
      location.reload();
    }).catch(err=>alert('Error: '+err.message));
}

function saveSchedule(isEdit=false) {
  const date = document.getElementById('modalFormDate').value;
  const hour = document.getElementById('modalFormHour').value;
  const department = document.getElementById('modalFormDept').value;
  const year_level = document.getElementById('modalFormYear').value;
  const section = document.getElementById('modalFormSection').value;

  if (!date || !hour || !department || !year_level || !section) {
    alert('Please fill out all fields.');
    return;
  }

  fetch('save_schedule.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `date=${encodeURIComponent(date)}&hour=${encodeURIComponent(hour)}&department=${encodeURIComponent(department)}&year_level=${encodeURIComponent(year_level)}&section=${encodeURIComponent(section)}&edit=${isEdit}`
  })
  .then(res => res.json())
  .then(data => {
    if(!data.success) throw new Error(data.message || 'Failed');
    alert(isEdit ? 'Schedule updated!' : 'Schedule saved!');
    closeModal();
    location.reload();
  })
  .catch(err=>alert('Error saving schedule: ' + (err.message||'network error')));
}
// Make all future cells clickable
document.querySelectorAll('td[data-date]').forEach(td => {
    const isPast = td.classList.contains('past');
    if (!isPast) {
        td.addEventListener('click', () => {
            const date = td.dataset.date;
            const hour = td.dataset.hour;
            const filled = td.dataset.filled === '1';

            openScheduleModal(date, hour, filled);
        });
    }
});

</script>


  <footer>
    &copy; <?= date('Y') ?> Talisay City College - Computer Lab SchedulingSystem
  </footer>
</body>
</html>
