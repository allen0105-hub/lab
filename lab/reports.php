<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "includes/config.php";
date_default_timezone_set('Asia/Manila');

$activeTab = $_GET['tab'] ?? 'reservations';

// Filters
$res_date_from = $_GET['res_date_from'] ?? '';
$res_date_to   = $_GET['res_date_to'] ?? '';
$res_status    = $_GET['res_status'] ?? '';

$user_department     = $_GET['user_department'] ?? '';
$user_classification = $_GET['user_classification'] ?? '';

// --- Fetch Reservations ---
$reservations = [];
if ($activeTab === 'reservations') {
    $res_query = "
        SELECT r.id, r.day_date, r.hour, r.reservation_type, r.reason, r.status, r.created_at,
               u.name AS user_name, u.department, u.classification
        FROM reservations r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE 1
    ";
    $params = [];
    if (!empty($res_date_from)) { $res_query .= " AND r.day_date >= :date_from"; $params[':date_from'] = $res_date_from; }
    if (!empty($res_date_to))   { $res_query .= " AND r.day_date <= :date_to";   $params[':date_to'] = $res_date_to; }
    if (!empty($res_status))    { $res_query .= " AND r.status = :status";       $params[':status'] = $res_status; }

    $res_query .= " ORDER BY r.day_date DESC, r.hour ASC";
    $stmt = $pdo->prepare($res_query);
    $stmt->execute($params);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Fetch Users ---
$users = [];
if ($activeTab === 'users') {
    $user_query = "SELECT id, name, department, classification, created_at FROM users WHERE 1";
    $user_params = [];
    if (!empty($user_department)) {
        $user_query .= " AND department = :dept";
        $user_params[':dept'] = $user_department;
    }
    if (!empty($user_classification)) {
        $user_query .= " AND classification = :class";
        $user_params[':class'] = $user_classification;
    }
    $user_query .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($user_query);
    $stmt->execute($user_params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — Reports | Talisay City College</title>
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

<style>
 { box-sizing: border-box; margin: 0; padding: 0; }
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
.header .nav-buttons {
  margin-left: auto;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.header .nav-buttons a {
  color: #f1f4f7ff;
  text-decoration: none;
   background: #0059b3;
  padding: 8px 14px;
  border-radius: 6px;
  font-weight: bold;
  transition: 0.3s;
  font-size: 14px;
}
.header .nav-buttons a:hover {
  background: #ffd633;
  color: #003366;
}

/* Container */
.container {
  background: linear-gradient(180deg, transparent-white, transparent-white); /* semi-transparent for bg visibility */);
  border:none;
  border-radius: 18px;
  padding: 20px;
  max-width: 1240px;
  margin: 24px auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

/* Toggle */
.top-toggle {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 20px;
  gap: 12px;
}
.top-toggle a {
  text-decoration: none;
  padding: 10px 18px;
  border-radius: 8px;
  background: #003366;
  color: #ffcc00;
  font-weight: bold;
  transition: 0.3s;
  font-size: 14px;
}
.top-toggle a.active {
  background: #ffcc00;
  color: #003366;
}

/* Filters */
.filter-box {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 16px;
  justify-content: center;
}
.filter-box label {
  text-decoration: none;
  padding: 10px 18px;
  border-radius: 8px;
  background: #0f559bff;
  color: #ffcc00;
  font-weight:bold;
  transition: 0.3s;
  font-size: 10px;
}
.top-toggle a.active {
  background: #ffcc00;
  color: #003366;
}
.filter-box input, .filter-box select, .filter-box button {
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #003366;
  font-size: 14px;
}
.filter-box button {
  background: #ffcc00;
  color: #003366;
  font-weight: bold;
  border: 1px solid #003366;
  cursor: pointer;
  transition: 0.3s;
}
.filter-box button:hover {
  background: #ffd633;
}

/* Card-style rows */
.card {
  background: #fff9e6;
  margin-bottom: 15px;
  border: 1px solid #003366;
  border-radius: 12px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  padding: 14px 16px;
}
.card p {
  margin: 6px 0;
  font-size: 14px;
}
.card p strong {
  color: #003366;
  font-weight: bold;
  margin-right: 6px;
}
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
  .container { padding: 14px; }
  .header h1 { font-size: 20px; }
  .top-toggle a { font-size: 12px; padding: 8px 12px; }
  .filter-box input, .filter-box select, .filter-box button { font-size: 12px; padding: 5px 8px; }
  .card p { font-size: 13px; }
}
</style>
</head>
<body>

<div class="header">
  <img src="images/logo.png" alt="TCC Logo" class="logo">
  <div class="title ">Admin — Reports about Reservation and Users Talisay City College</div>
  <div class="nav-buttons">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<div class="container">
  <div class="top-toggle">
    <a href="?tab=reservations" class="<?= ($activeTab=='reservations')?'active':'' ?>">Reservation Reports</a>
    <a href="?tab=users" class="<?= ($activeTab=='users')?'active':'' ?>">User Reports</a>
  </div>

  <?php if ($activeTab === 'reservations'): ?>
    <form method="GET" class="filter-box">
      <input type="hidden" name="tab" value="reservations">
      <label>Date From:
        <input type="date" name="res_date_from" value="<?= htmlspecialchars($res_date_from) ?>">
      </label>
      <label>Date To:
        <input type="date" name="res_date_to" value="<?= htmlspecialchars($res_date_to) ?>">
      </label>
      <label>Status:
        <select name="res_status">
          <option value="">All</option>
          <option value="Pending" <?= $res_status=='Pending'?'selected':'' ?>>Pending</option>
          <option value="Approved" <?= $res_status=='Approved'?'selected':'' ?>>Approved</option>
          <option value="Denied" <?= $res_status=='Denied'?'selected':'' ?>>Denied</option>
        </select>
      </label>
      <button type="submit">Filter</button>
    </form>

    <?php if (empty($reservations)): ?>
      <p style="text-align:center;">No reservations found.</p>
    <?php else: ?>
      <?php foreach ($reservations as $r): ?>
        <div class="card">
          <p><strong>ID</strong>: <?= htmlspecialchars($r['id']) ?></p>
          <p><strong>User</strong>: <?= htmlspecialchars($r['user_name']) ?></p>
          <p><strong>Department</strong>: <?= htmlspecialchars($r['department']) ?></p>
          <p><strong>Classification</strong>: <?= htmlspecialchars($r['classification']) ?></p>
          <p><strong>Date</strong>: <?= htmlspecialchars($r['day_date']) ?></p>
          <p><strong>Hour</strong>: <?= date("g A", strtotime($r['hour'].":00")) ?></p>
          <p><strong>Type</strong>: <?= htmlspecialchars($r['reservation_type']) ?></p>
          <p><strong>Reason</strong>: <?= htmlspecialchars($r['reason']) ?></p>
          <p><strong>Status</strong>: <?= htmlspecialchars($r['status']) ?></p>
          <p><strong>Created</strong>: <?= htmlspecialchars($r['created_at']) ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  <?php else: ?>
    <form method="GET" class="filter-box">
      <input type="hidden" name="tab" value="users">
      <label>Department:
        <select name="user_department">
          <option value="">All</option>
          <option value="IT" <?= $user_department=='IT'?'selected':'' ?>>IT</option>
          <option value="BSHM" <?= $user_department=='BSHM'?'selected':'' ?>>BSHM</option>
          <option value="EDUC" <?= $user_department=='EDUC'?'selected':'' ?>>EDUC</option>
        </select>
      </label>
      <label>Classification:
        <select name="user_classification">
          <option value="">All</option>
          <option value="1st Year" <?= $user_classification=='1st Year'?'selected':'' ?>>1st Year</option>
          <option value="2nd Year" <?= $user_classification=='2nd Year'?'selected':'' ?>>2nd Year</option>
          <option value="3rd Year" <?= $user_classification=='3rd Year'?'selected':'' ?>>3rd Year</option>
          <option value="4th Year" <?= $user_classification=='4th Year'?'selected':'' ?>>4th Year</option>
        </select>
      </label>
      <button type="submit">Filter</button>
    </form>

    <?php if (empty($users)): ?>
      <p style="text-align:center;">No users found.</p>
    <?php else: ?>
      <?php foreach ($users as $u): ?>
        <div class="card">
          <p><strong>ID</strong>: <?= htmlspecialchars($u['id']) ?></p>
          <p><strong>Name</strong>: <?= htmlspecialchars($u['name']) ?></p>
          <p><strong>Department</strong>: <?= htmlspecialchars($u['department']) ?></p>
          <p><strong>Classification</strong>: <?= htmlspecialchars($u['classification']) ?></p>
          <p><strong>Created At</strong>: <?= htmlspecialchars($u['created_at']) ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  <?php endif; ?>
</div>
 <footer>
    &copy; <?= date('Y') ?> Talisay City College - Computer Lab Reservation and User Reports
  </footer>
</body>
</html>
