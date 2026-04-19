<?php 
require 'db.php'; 
include 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_user = $_SESSION['user_id']; 
    $new_pass = $_POST['pass'];
    $conf_pass = $_POST['conf_pass'];

    try {
        if (!empty($new_pass)) {
            if ($new_pass !== $conf_pass) {
                throw new Exception("Passwords do not match!");
            }
            // Update profile AND password
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $sql = "UPDATE User_Table SET Email_Address = ?, Contact_Number = ?, Password = ? WHERE User_ID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['email'], $_POST['phone'], $hashed_password, $current_user]);
        } else {
            // Update profile only
            $sql = "UPDATE User_Table SET Email_Address = ?, Contact_Number = ? WHERE User_ID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['email'], $_POST['phone'], $current_user]);
        }
        $message = "<p style='color:green;'>Profile updated successfully!</p>";
    } catch (Exception $e) {
        $message = "<p style='color:red;'>Update failed: " . $e->getMessage() . "</p>";
    }
}
?>

<h2>Account Settings</h2>
<?php echo $message; ?>
<form action="settings.php" method="POST">
    <fieldset>
        <legend>Contact Information</legend>
        <label>New Email Address:</label><br>
        <input type="email" name="email" placeholder="email@example.com" required><br><br>
        
        <label>New Contact Number:</label><br>
        <input type="text" name="phone" placeholder="09123456789" required>
    </fieldset>

    <fieldset style="margin-top:15px;">
        <legend>Security (Leave blank to keep current password)</legend>
        <label>New Password:</label><br>
        <input type="password" name="pass"><br><br>
        
        <label>Confirm New Password:</label><br>
        <input type="password" name="conf_pass">
    </fieldset>
    
    <button type="submit" style="margin-top:15px;">Save Changes</button>
</form>
</body>
</html>