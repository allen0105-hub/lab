<?php
session_start();
require_once "includes/config.php"; // DB connection

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $section = trim($_POST['section']); // Combined Year & Section
    $classification = trim($_POST['classification']);

    if (empty($name) || empty($section) || empty($classification)) {
        $error = "All fields are required.";
    } else {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE name = ?");
        $stmt->execute([$name]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['id'];
        } else {
            // Insert new user (table has section instead of department)
            $stmt = $pdo->prepare("INSERT INTO users (name, section, classification, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$name, $section, $classification]);
            $userId = $pdo->lastInsertId();
        }

        // Save session
        $_SESSION['user_id'] = $userId;
        $_SESSION['user'] = [
            'name' => $name,
            'section' => $section,
            'classification' => $classification
        ];

        header("Location: user_dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TCC — User Info</title>
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

<style>
body, html {
    height: 100%;
    margin: 0;
    font-family: 'Roboto', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    background: url('images/tcc.jpg') no-repeat center center fixed;
    background-size: cover;
}

.overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.25);
    z-index: -1;
}

.form-container {
    width: 100%;
    max-width: 340px;
    background: rgba(255, 255, 255, 0.9);
    padding: 1.5rem;
    border-radius: 14px;
    border: 3px solid #003366;
    box-shadow: 0 10px 30px rgba(0,0,0,0.35);
    text-align: center;
}

.form-container img {
    height: 70px;
    margin-bottom: 12px;
}

h2 {
    font-family: 'Merriweather', serif;
    font-size: 1.6rem;
    color: #003366;
    margin-bottom: 1rem;
}

.error { color:red; margin-bottom:1rem; font-weight:600; }

.form-group { margin-bottom: 1rem; text-align:left; }
label { font-weight:bold; color:#003366; display:block; margin-bottom:0.3rem; }
input, select {
    width:100%; padding:0.55rem;
    border-radius:8px; border:2px solid #003366;
    background:#fff; font-size:1rem;
    outline:none; transition: all 0.3s;
}
input:focus, select:focus {
    border-color:#ffcc00; background:#fffef5;
}

button {
    width: 100%;
    padding: 0.75rem;
    background: #003366;
    color:#fff;
    font-size:1.05rem;
    font-weight:bold;
    border:none;
    border-radius:8px;
    cursor:pointer;
    transition: background 0.3s, transform 0.2s;
}
button:hover { background:#002244; transform: scale(1.05); }
</style>
</head>
<body>
<div class="overlay"></div>

<div class="form-container">
  <img src="images/logo.png" alt="TCC Logo">
  <h2>User Information</h2>

  <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

  <form method="POST">
    <div class="form-group">
      <label>Name:</label>
      <input type="text" name="name" required>
    </div>

    <div class="form-group">
      <label>Year and Section:</label>
      <input type="text" name="section" placeholder="e.g., 4-HOPE" required>
    </div>

    <div class="form-group">
      <label>Classification:</label>
      <select name="classification" required>
        <option value="">Select Classification</option>
        <option value="Faculty">Faculty</option>
        <option value="Student">Student</option>
        <option value="Teacher">Teacher</option>
      </select>
    </div>

    <button type="submit">Proceed</button>
  </form>
</div>
</body>
</html>
