<?php
// login.php - Final Integrated Version
require 'includes/db.php';
session_start(); // Ensure session is started for user tracking

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $_POST['identifier'];
    $pass_input = $_POST['pass'];

    // Querying the User_Table based on the provided project requirements
    $sql = "SELECT * FROM User_Table WHERE Username = ? OR Email_Address = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($pass_input, $user['Password'])) {
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['username'] = $user['Username'];

        // Logic for "Remember Me" cookies
        if (isset($_POST['remember'])) {
            $token = bin2hex(random_bytes(16));
            $token_hash = password_hash($token, PASSWORD_DEFAULT);
            $expiry = date('Y-m-d H:i:s', time() + (86400 * 30));
            $token_sql = "INSERT INTO User_Tokens (User_ID, Token_Hash, Expiry) VALUES (?, ?, ?)";
            $t_stmt = mysqli_prepare($conn, $token_sql);
            if ($t_stmt) {
                mysqli_stmt_bind_param($t_stmt, "iss", $user['User_ID'], $token_hash, $expiry);
                mysqli_stmt_execute($t_stmt);
                setcookie('remember_me', $user['User_ID'] . ':' . $token, time() + (86400 * 30), "/", "", false, true);
            }
        }
        header("Location: index.php");
        exit();
    } else {
        $message = "<p style='color: #ff4d4d; font-weight: bold; margin-bottom: 15px;'>Invalid email/username or password.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BalikGamit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="logo">
                <img src="images/BalikGamitLogo1.png" alt="BalikGamit Logo" width="40" height="40">
                <div class="logo-text">
                    <div class="title">BalikGamit</div>
                    <div class="subtitle">BY ASYNC V.1.0</div>
                </div>
            </div>
            <div class="hero">
                <h1>Reuniting lost items with their owners.</h1>
                <p>A centralized lost and found platform for Bukidnon State University — College of Technologies.</p>
            </div>
        </div>

        <div class="right-panel">
            <form class="login-form" action="login.php" method="post">
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
                
                <div class="divider">
                    <hr>
                    <span>or</span>
                    <hr>
                </div>
                
                <button type="button" class="google-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Sign in with Google
                </button>
            </form>
        </div>
    </div>
</body>
</html>