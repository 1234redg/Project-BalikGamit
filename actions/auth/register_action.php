<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'includes/db.php'; 
include 'includes/nav_master.php'; 
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST['user'];
    $pass = $_POST['pass'];
    $conf_pass = $_POST['conf_pass'];

    if ($pass !== $conf_pass) {
        $message = "<p style='color:red;'>Passwords do not match!</p>";
    } else {
        $checkSql = "SELECT Username FROM User_Table WHERE Username = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "s", $user_name);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        
        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $message = "<p style='color:red;'>Username already taken. Please choose another.</p>";
        } else {
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO User_Table (Username, Password, First_Name, Last_Name, Role, Email_Address, Contact_Number) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssss", $user_name, $hashed_password, $_POST['fname'], $_POST['lname'], $_POST['role'], $_POST['email'], $_POST['phone']);
            
            if (mysqli_stmt_execute($stmt)) {
                $new_id = mysqli_insert_id($conn);
                $message = "<p style='color:green;'>Account created successfully! Your System ID is <strong>" . htmlspecialchars($new_id) . "</strong>.<br><a href='login.php'>Click here to Login</a></p>";
            } else {
                $message = "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
            }
        }
    }
}
?>
<h2>Create User Account</h2>
<?php echo $message; ?>
<form action="signup.php" method="POST">
    <p style="color: #666; font-size: 0.9em;"><em>Note: Your unique User ID will be generated automatically.</em></p>
    <label>Username:</label><br>
    <input type="text" name="user" required><br><br>
    <label>Password:</label><br>
    <input type="password" name="pass" required><br><br>
    <label>Confirm Password:</label><br>
    <input type="password" name="conf_pass" required><br><br>
    <label>First Name:</label><br>
    <input type="text" name="fname"><br><br>
    <label>Last Name:</label><br>
    <input type="text" name="lname"><br><br>
    <label>Role:</label><br>
    <select name="role">
        <option value="Student">Student</option>
        <option value="Admin">Admin</option>
    </select><br><br>
    <label>Email:</label><br>
    <input type="email" name="email"><br><br>
    <label>Phone:</label><br>
    <input type="text" name="phone"><br><br>
    <button type="submit">Create Account</button>
</form>
</body>
</html>