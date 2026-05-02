<?php
// login.php
session_start();

// Check if there is an error message passed from login_action.php
$error = isset($_GET['error']) ? $_GET['error'] : "";
$message = "";

if ($error == "invalid") {
    $message = "<p style='color: #ff4d4d; font-weight: bold; margin-bottom: 15px;'>Invalid email/username or password.</p>";
} elseif ($error == "empty") {
    $message = "<p style='color: #ff4d4d; font-weight: bold; margin-bottom: 15px;'>Please fill in all fields.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BalikGamit</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Brand/Hero Section remains the same -->
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

<!--------------------------------------------------------------------------------------------------------------------------------------->
            <!-- ACTION points to login_action.php -->
            <form class="login-form" action="actions/auth/login_action.php" method="post">
                <h1>Login to BalikGamit</h1>
                <p>Please log in to your account to continue.</p>
                
                <?php echo $message; ?>

                <div class="form-group">
                    <label for="identifier">EMAIL / USERNAME</label>
                    <input type="text" id="identifier" name="identifier" placeholder="Enter your email or username" required>
                </div>
                <div class="form-group">
                    <label for="pass">PASSWORD</label>
                    <input type="password" id="pass" name="pass" placeholder="Enter your password" required>
                </div>
                <div class="form-row">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="login-btn">Login</button>
                <p class="signup-link">Don't have an Account? <a href="signup.php">Sign up here</a></p>
            </form>
        </div>
    </div>
</body>
</html>