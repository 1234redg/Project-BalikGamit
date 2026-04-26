<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../includes/db.php';
include '../includes/nav_master.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists in User_Table
    $sql = "SELECT * FROM User_Table WHERE Email_Address = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Store the email in the session so verify-email.php knows who we are
        $_SESSION['reset_identifier'] = $email;

        // In a real app, you'd trigger the email sending logic here

        header("Location: verify-email.php");
        exit(); // Always exit after a header redirect
    } else {
        $message = "<p style='color:red;'>No account found with that email address.</p>";
    }
}
?>

<h2>Forgot Password?</h2>
<p>Enter your registered email address</p>

<?php echo $message; ?>

<form action="forgot-password.php" method="POST">
    <label>EMAIL ADDRESS</label><br>
    <input type="email" name="email" placeholder="Enter your email address" required><br><br>

    <button type="submit">Send Reset Code</button>
</form>

<p>Remember your password? <a href="login.php">Log in</a></p>

</body>

</html>