<?php
session_start();
require '../includes/db.php';
include '../includes/nav_master.php';
$message = "";

// Security Check: Ensure they verified their email first
if (!isset($_SESSION['reset_identifier']) || !isset($_SESSION['code_verified'])) {
    header("Location: /balikgamit/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if ($new_pass === $confirm_pass) {
        // Hash the new password before saving
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        $identifier = $_SESSION['reset_identifier'];

        // Update the database
        $sql = "UPDATE User_Table SET Password = ? WHERE Email_Address = ? OR Username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $hashed_password, $identifier, $identifier);
        
        if (mysqli_stmt_execute($stmt)) {
            // Success! Clear session and redirect
            session_destroy();
            echo "<script>alert('Password updated successfully!'); window.location.href='login.php';</script>";
            exit();
        } else {
            $message = "<p style='color:red;'>Database error. Please try again.</p>";
        }
    } else {
        $message = "<p style='color:red;'>Passwords do not match.</p>";
    }
}
?>

<h2>Set New Password</h2>
<p>Create a strong password for your account.</p>

<?php echo $message; ?>

<form action="change-password.php" method="POST">
    <label>NEW PASSWORD</label><br>
    <input type="password" name="new_pass" placeholder="Enter new password" required><br><br>
    
    <label>CONFIRM PASSWORD</label><br>
    <input type="password" name="confirm_pass" placeholder="Confirm new password" required><br><br>
    
    <button type="submit">Update Password</button>
</form>

</body>
</html>