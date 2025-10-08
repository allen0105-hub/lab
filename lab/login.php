<?php
session_start();
require_once "includes/config.php";

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (empty($recaptchaResponse)) {
        $error = "Please complete the reCAPTCHA.";
    } elseif (!$name || !$password) {
        $error = "Please enter both name and password";
    } else {
        $recaptchaSecret = "6LdJ7NsrAAAAAMGDroyusXryKgq6qWtDi-RuckgO";
        $verifyResponse = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=" 
            . $recaptchaSecret . "&response=" . $recaptchaResponse
        );
        $responseData = json_decode($verifyResponse, true);

        if (!$responseData['success']) {
            $error = "reCAPTCHA verification failed. Please try again.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE name=?");
                $stmt->execute([$name]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && (password_verify($password, $admin['password']) || $password === $admin['password'])) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    header("Location: admin_dashboard.php");
                    exit;
                } else {
                    $error = "Invalid name or password";
                }
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TCC Admin Login</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

<!-- Google reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Roboto', sans-serif; }
body, html { 
    height:100%; 
    background:url('images/tcc.jpg') no-repeat center center fixed; 
    background-size:cover; 
    display:flex; 
    justify-content:center; 
    align-items:center; 
}
body::before{
    content:"";
    position:absolute;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.55);
    z-index:-1;
}

.login-container { 
    width:100%; max-width:350px; 
    background:rgba(255,255,255,0.95); 
    padding:2rem; 
    border-radius:15px; 
    border:3px solid #1E90FF; 
    box-shadow:0 8px 20px rgba(0,0,0,0.4); 
    text-align:center; 
}
.login-container img { width:90px; margin-bottom:15px; border-radius:50%; border:3px solid #FFD700; }
h2 { font-family:'Merriweather', serif; color:#1E90FF; font-size:1.6rem; margin-bottom:18px; text-transform:uppercase; }
input[type=text], input[type=password] { 
    width:100%; padding:0.7rem; margin-bottom:1rem; border-radius:8px; 
    border:2px solid #1E90FF; background:#f8faff; font-size:1rem; outline:none; transition:0.3s; 
}
input[type=text]:focus, input[type=password]:focus { border-color:#FFD700; background:#fff; }

.password-wrapper { position:relative; }
.toggle-btn { position:absolute; top:50%; right:10px; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:18px; }

button[type=submit] { 
    width:100%; padding:0.8rem; border:none; border-radius:10px; font-size:1rem; font-weight:bold; 
    background:linear-gradient(180deg,#1E90FF,#005bbb); color:#FFD700; cursor:pointer; 
    box-shadow:0 5px 12px rgba(0,0,0,0.3); transition:0.3s; 
}
button[type=submit]:hover { transform:scale(1.05); background:linear-gradient(180deg,#FF8C00,#FFD700); color:#000; }

.error { color:red; margin-bottom:12px; font-size:0.9rem; font-weight:500; }

footer { text-align:center; margin-top:15px; font-size:0.85rem; color:#FFD700; font-family:'Merriweather', serif; }

/* reCAPTCHA wrapper for centering */
.recaptcha-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
    transform: scale(0.95);
    transform-origin: 0 0;
}
@media(max-width:480px){ 
    .login-container{padding:1.2rem;} 
    input, button{font-size:14px;padding:0.6rem;} 
    .recaptcha-wrapper { transform: scale(0.85); }
}
</style>
</head>
<body>
<div class="login-container">
    <img src="images/logo.png" alt="TCC Logo">
    <h2>Admin Login</h2>

    <?php if($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="name" placeholder="Admin Name" required>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <button type="button" class="toggle-btn" id="togglePassword">👁️</button>
        </div>

        <!-- reCAPTCHA -->
        <div class="recaptcha-wrapper">
            <div class="g-recaptcha" data-sitekey="6LdJ7NsrAAAAALuBdOxf-fQYinLEQ7V3I0kEPe18"></div>
        </div>

        <button type="submit">Login</button>
    </form>
    <footer>&copy; <?= date('Y') ?> Talisay City College - Lab Reservation System</footer>
</div>

<script>
const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');
togglePassword.addEventListener('click',()=>{
    if(password.type==="password"){ password.type="text"; togglePassword.textContent="🙈"; }
    else{ password.type="password"; togglePassword.textContent="👁️"; }
});
</script>
</body>
</html>
