<?php
session_start();

$error = isset($_GET['error']) ? $_GET['error'] : "";
$message = "";

if ($error == "invalid") {
    $message = "<p class='error-msg'>Invalid email/username or password.</p>";
} elseif ($error == "empty") {
    $message = "<p class='error-msg'>Please fill in all fields.</p>";
} elseif ($error == "recaptcha") {
    $message = "<p class='error-msg'>reCAPTCHA verification failed. Please try again.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BalikGamit</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <!--
        FIX: reCAPTCHA loads first, THEN calls renderRecaptcha()
        which is defined in login.js — async defer prevents render blocking
    -->
    <script src="https://www.google.com/recaptcha/api.js?onload=renderRecaptcha&render=explicit" async defer></script>
    <script src="assets/js/login.js" defer></script>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="logo">
                <img src="assets/images/BalikGamitLogo1.png" alt="BalikGamit Logo" width="40" height="40">
                <div class="logo-text">
                    <div class="title">BalikGamit</div>
                    <div class="subtitle">BY ASYNC V.1.0</div>
                </div>
            </div>
            <div class="hero">
                <h1>Reuniting lost items with their owners.</h1>
                <p>A centralized lost and found platform for Bukidnon State University.
                Submit reports, track item status, and claim your belongings
                seamlessly through our digital system.</p>
            </div>
        </div>

        <div class="right-panel">
            <form class="login-form" id="loginForm" action="actions/auth/login_action.php" method="post">
                <h1>Login to BalikGamit</h1>
                <p>Please log in to your account to continue.</p>

                <?php echo $message; ?>

                <div class="form-group">
                    <label for="identifier">EMAIL / USERNAME</label>
                    <input type="text" id="identifier" name="identifier"
                           placeholder="Enter your email or username" required>
                </div>

                <div class="form-group">
                    <label for="pass">PASSWORD</label>
                    <input type="password" id="pass" name="pass"
                           placeholder="Enter your password" required>
                </div>

                <div class="form-row">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button id="submitBtn" type="button" class="login-btn" onclick="handleSubmit()">
                    Login
                </button>

                <!--
                    This div is where the INVISIBLE reCAPTCHA widget mounts.
                    It has zero visible size — no checkbox, no green tick ever shows here.
                    The spinner feedback is handled entirely by the button CSS.
                -->
                <div id="recaptcha-container"></div>

                <p class="signup-link">Don't have an Account? <a href="signup.php">Sign up here</a></p>
            </form>
        </div>
    </div>
</body>
</html>