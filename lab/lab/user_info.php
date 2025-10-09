<?php
session_start();
require_once "includes/config.php"; // DB connection

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $department = trim($_POST['department']);
    $classification = trim($_POST['classification']);
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    if (empty($name) || empty($department) || empty($classification)) {
        $error = "All fields are required.";
    } elseif (empty($recaptchaResponse)) {
        $error = "Please complete the reCAPTCHA.";
    } else {
        // Verify reCAPTCHA
        $recaptchaSecret = "6LdJ7NsrAAAAAMGDroyusXryKgq6qWtDi-RuckgO";
        $verifyResponse = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=" 
            . $recaptchaSecret . "&response=" . $recaptchaResponse
        );
        $responseData = json_decode($verifyResponse, true);

        if (!$responseData["success"]) {
            $error = "reCAPTCHA verification failed. Please try again.";
        } else {
            // Save or fetch user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE name = ?");
            $stmt->execute([$name]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $userId = $user['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, department, classification) VALUES (?, ?, ?)");
                $stmt->execute([$name, $department, $classification]);
                $userId = $pdo->lastInsertId();
            }

            $_SESSION['user_id'] = $userId;
            $_SESSION['user'] = [
                'name' => $name,
                'department' => $department,
                'classification' => $classification
            ];

            header("Location: user_dashboard.php");
            exit();
        }
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

<!-- Google reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

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

/* Overlay for readability */
.overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.25);
    z-index: -1;
}

/* Form box */
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

/* Logo */
.form-container img {
    height: 70px;
    margin-bottom: 12px;
}

/* Heading */
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

/* reCAPTCHA centering */
.recaptcha-wrapper {
    display: flex;
    justify-content: center;
    margin: 15px 0;
    transform: scale(0.92);
    transform-origin: 0 0;
}
@media (max-width: 400px) {
    .recaptcha-wrapper { transform: scale(0.85); }
}
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
      <label>Department:</label>
      <select name="department" required>
        <option value="">Select Department</option>
        <option value="BIT">BIT</option>
        <option value="BSHM">BSHM</option>
        <option value="BSED">BSED</option>
        <option value="BEED">BEED</option>
      </select>
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

    <!-- reCAPTCHA -->
    <div class="recaptcha-wrapper">
      <div class="g-recaptcha" data-sitekey="6LdJ7NsrAAAAALuBdOxf-fQYinLEQ7V3I0kEPe18"></div>
    </div>

    <button type="submit">Proceed</button>
  </form>
</div>
</body>
</html>
