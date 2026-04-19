<?php 
// 1. Error Reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Database Connection
require 'db.php'; 
include 'includes/header.php'; 

$message = "";

// 3. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST['user'];
    $pass = $_POST['pass'];
    $conf_pass = $_POST['conf_pass'];

    // Validation: Check if passwords match
    if ($pass !== $conf_pass) {
        $message = "<p style='color:red;'>Passwords do not match!</p>";
    } else {
        try {
            // Check if Username already exists to avoid SQL errors
            $checkUser = $pdo->prepare("SELECT Username FROM User_Table WHERE Username = ?");
            $checkUser->execute([$user_name]);
            
            if ($checkUser->rowCount() > 0) {
                $message = "<p style='color:red;'>Username already taken. Please choose another.</p>";
            } else {
                // Securely hash the password
                $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

                // Prepare SQL - User_ID is omitted because it's AUTO_INCREMENT
                $sql = "INSERT INTO User_Table (Username, Password, First_Name, Last_Name, Role, Email_Address, Contact_Number) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $user_name, 
                    $hashed_password,
                    $_POST['fname'], 
                    $_POST['lname'], 
                    $_POST['role'], 
                    $_POST['email'], 
                    $_POST['phone']
                ]);

                // Retrieve the new numeric ID assigned by the system
                $new_id = $pdo->lastInsertId();
                
                $message = "<p style='color:green;'>Account created successfully! Your System ID is <strong>" . htmlspecialchars($new_id) . "</strong>.<br><a href='login.php'>Click here to Login</a></p>";
            }
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
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