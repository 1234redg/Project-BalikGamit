<?php
require 'includes/db.php';
include 'includes/nav_master.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $_POST['identifier'];
    $pass_input = $_POST['pass'];

    $sql = "SELECT * FROM User_Table WHERE Username = ? OR Email_Address = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($pass_input, $user['Password'])) {
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['username'] = $user['Username'];

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
        $message = "<p style='color:red;'>Invalid email/username or password.</p>";
    }
}
?>
<h2>Login to BalikGamit</h2>
<p>Please log in to your account to continue.</p>
<?php echo $message; ?>
<form action="login.php" method="POST">
    <label>EMAIL / USERNAME</label><br>
    <input type="text" name="identifier" placeholder="Enter your email or username" required><br><br>
    <label>PASSWORD</label>
    <a href="forgot-password.php" style="float:right;">Forgot Password?</a><br>
    <input type="password" name="pass" placeholder="Enter your password" required><br><br>
    <input type="checkbox" name="remember"> Remember me <br><br>
    <button type="submit">Login</button>
</form>
<p>Don't have an Account? <a href="signup.php">Sign up here</a></p>
</body>
</html>