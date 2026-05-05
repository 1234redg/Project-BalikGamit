<?php
session_start();

// Handle URL parameters for feedback
$error = $_GET['error'] ?? "";
$message = "";

if ($error == "invalid") {
    $message = '<p style="color: #dc2626; font-weight: 600; margin-bottom: 16px;">Invalid email/username or password.</p>';
} elseif ($error == "empty") {
    $message = '<p style="color: #dc2626; font-weight: 600; margin-bottom: 16px;">Please fill in all fields.</p>';
} elseif ($error == "recaptcha") {
    $message = '<p style="color: #dc2626; font-weight: 600; margin-bottom: 16px;">reCAPTCHA verification failed. Please try again.</p>';
}

// CARDINAL RULE: Dynamic Path Logic
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$isSubfolder = ($currentDir === 'student' || $currentDir === 'admin');

// Adjust paths based on folder depth
$prefix = $isSubfolder ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BalikGamit</title>
    
    <!-- Google Identity Services -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <!-- Dynamic Assets -->
    <link rel="stylesheet" href="<?= $prefix ?>assets/css/style.css">
    
    <!-- reCAPTCHA API -->
    <script src="https://www.google.com/recaptcha/api.js?onload=renderRecaptcha&render=explicit" async defer></script>
    <script src="<?= $prefix ?>assets/js/login.js" defer></script>
</head>
<body>
    <div class="container">
        <!-- Left Panel: Branded Hero (Matches Signup) -->
        <div class="left-panel">
            <div class="logo">
                <img src="<?= $prefix ?>assets/images/BalikGamitLogo1.png" alt="BalikGamit Logo" width="40" height="40">
                <div class="logo-text">
                    <div class="title">BalikGamit</div>
                    <div class="subtitle">BY ASYNC V.1.0</div>
                </div>
            </div>
            <div class="hero">
                <h1>Reuniting lost items with their owners.</h1>
                <p>A centralized lost and found platform for Bukidnon State University.</p>
            </div>
        </div>

        <!-- Right Panel: Login Action -->
        <div class="right-panel">
            <form class="login-form" id="loginForm" action="<?= $prefix ?>actions/auth/login_action.php" method="post" autocomplete="off">
                <h1>Login to BalikGamit</h1>
                <p>Please log in to your account to continue.</p>

                <!-- Status Message -->
                <?= $message ?>

                <div class="form-group">
                    <label for="identifier">EMAIL / USERNAME</label>
                    <input type="text" id="identifier" name="identifier" 
                           placeholder="Enter your email or username" 
                           autocomplete="off" required>
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
                    <a href="<?= $prefix ?>forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <!-- Invisible reCAPTCHA container -->
                <div id="recaptcha-container"></div>
                
                <button id="submitBtn" type="button" class="login-btn" onclick="handleSubmit()">Login</button>

                <!-- OR Divider with Spacing Fix -->
                <div style="display: flex; align-items: center; margin: 15px 0;">
                    <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
                    <span style="margin: 0 10px; color: #94a3b8; font-size: 0.8rem; font-weight: 500; font-family: 'Poppins', sans-serif;">OR</span>
                    <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
                </div>

                <!-- Google Identity Config -->
                <div id="g_id_onload"
                     data-client_id="YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com"
                     data-context="signin"
                     data-ux_mode="popup"
                     data-login_uri="<?= $prefix ?>actions/auth/google_auth.php"
                     data-auto_prompt="false">
                </div>

                <!-- Google Button centered via Flex -->
                <div style="display: flex; justify-content: center; width: 100%; margin-bottom: 15px;">
                    <div class="g_id_signin" 
                         data-type="standard" 
                         data-shape="rectangular" 
                         data-theme="outline" 
                         data-text="signin_with" 
                         data-size="large" 
                         data-width="395">
                    </div>
                </div>

                <p class="signup-link">Don't have an Account? <a href="<?= $prefix ?>signup.php">Sign up here</a></p>
            </form>
        </div>
    </div>
</body>
</html>