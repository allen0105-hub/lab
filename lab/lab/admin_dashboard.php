<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - TCC Theme</title>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <style>
    * {margin:0;padding:0;box-sizing:border-box;}

    body{
      font-family:'Roboto', sans-serif;
      background:url('images/tcc.jpg') no-repeat center center fixed;
      background-size:cover;
      display:flex;
      flex-direction:column;
      min-height:100vh;
      color:#000;
      position:relative;
    }

    /* Dark overlay to ensure text clarity */
    body::before{
      content:"";
      position:absolute;
      top:0;left:0;width:100%;height:100%;
      background:rgba(0,0,0,0.55); /* adjustable for clarity */
      z-index:-1;
    }

    a{text-decoration:none;}

    /* Navbar */
    .navbar{
      background:transparent;
      padding:12px 18px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      border-bottom:4px solid #FFD700;
      box-shadow:0 3px 8px rgba(0,0,0,0.3);
    }
    .navbar h1{
      font-family:'Merriweather', serif;
      font-size:1.6rem;
      color:#FFD700;
      letter-spacing:1px;
      display:flex;align-items:center;
      text-transform:uppercase;
    }
    .navbar img.logo{width:45px;margin-right:10px;border-radius:50%;}
    .navbar div{font-size:.95rem;color:white;font-weight:500;}
    .navbar a{color:#FFD700;margin-left:12px;font-weight:bold;}
    .navbar a:hover{color:#FF8C00;}

    /* Container */
    .container{
      flex:1;
      display:flex;
      flex-direction:column;
      justify-content:center;
      align-items:center;
      padding:20px;
      gap:20px;
      text-align:center;
    }

    /* Cards */
    .card{
      flex:1;
      max-width:350px;
      width:100%;
      background: transparent-white; /* semi-transparent for bg visibility */
      border:3px transparent-white;
      border-radius:20px;
      box-shadow:0 0 15px rgba(0,0,0,0.25);
      padding:22px;
      transition:transform .3s ease, box-shadow .3s ease;
    }
    .card:hover{
      transform:translateY(-6px);
      box-shadow:0 0 25px rgba(30,144,255,0.6);
    }
    .card h2{
      font-family:'Merriweather', serif;
      font-size:1.3rem;
      color:#FFD700;
      margin-bottom:10px;
      text-transform:uppercase;
    }
    .card p{
      color:#FFD700;
      margin-bottom:14px;
      font-size:.95rem;
    }

    /* Buttons */
    a.button{
      display:inline-block;
      padding:9px 18px;
      background:#1E90FF;
      color:#FFD700;
      font-weight:bold;
      border-radius:10px;
      transition:.3s;
      font-size:.9rem;
    }
    a.button:hover{
      background:linear-gradient(45deg,#FF8C00,#FFD700);
      color:#000;
      transform:scale(1.05);
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

    @media(min-width:600px){
      .container{flex-direction:row;flex-wrap:wrap;gap:25px;}
      .card{max-width:300px;}
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <div class="navbar">
    <h1><img src="images/logo.png" alt="TCC Logo" class="logo">Admin Dashboard</h1>
    <div>
      <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
      | <a href="logout.php">Logout</a>
    </div>
  </div>

  <!-- Container -->
  <div class="container">
    <div class="card">
      <h2>Manage Schedules</h2>
      <p>Create and edit schedules for all users.</p>
      <a class="button" href="schedules.php">Go to Schedules</a>
    </div>

    <div class="card">
      <h2>Manage Reservations</h2>
      <p>Approve or deny reservation requests.</p>
      <a class="button" href="fetch_reservation.php">Go to Reservations</a>
    </div>

    <div class="card">
      <h2>View Reports</h2>
      <p>See system stats and activity logs.</p>
      <a class="button" href="reports.php">Go to Reports</a>
    </div>
  </div>

  <footer>
    &copy; <?= date('Y') ?> Talisay City College -Computer Lab Scheduling and Reservation System
  </footer>
</body>
</html>
