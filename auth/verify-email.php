<?php
session_start();
require '../includes/db.php';
include '../includes/nav_master.php';
$message = "";

// Ensure the user actually came from the forgot-password flow
if (!isset($_SESSION['reset_identifier'])) {
    header("Location: /balikgamit/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_code = $_POST['verification_code'];

    // For barebones logic, we'll assume '123456' is the valid code 
    // or simply check if the field is not empty.
    if ($input_code === '123456') { 
        $_SESSION['code_verified'] = true;
        header("Location: change-password.php");
        exit();
    } else {
        $message = "<p style='color:red;'>Invalid verification code. Try '123456'.</p>";
    }
}
?>

<h2>Verify Email</h2>
<p>Enter the 6-digit code sent to: <strong><?php echo htmlspecialchars($_SESSION['reset_identifier']); ?></strong></p>

<?php echo $message; ?>

<form action="verify-email.php" method="POST">
    <label>VERIFICATION CODE</label><br>
    <input type="text" name="verification_code" placeholder="Enter code" required><br><br>
    <button type="submit">Verify Code</button>
</form>

<p><a href="forgot-password.php">Resend code</a></p>
</body>
</html>