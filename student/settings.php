<?php 
require '../config/db.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $sql = "UPDATE User_Table SET Email_Address = ?, Contact_Number = ?, Password = ? WHERE User_ID = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", $_POST['email'], $_POST['phone'], $hashed_password, $current_user);
            mysqli_stmt_execute($stmt);
        } else {
            $sql = "UPDATE User_Table SET Email_Address = ?, Contact_Number = ? WHERE User_ID = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $_POST['email'], $_POST['phone'], $current_user);
            mysqli_stmt_execute($stmt);
        }
        $message = "<p style='color: #28a745; background: rgba(40, 167, 69, 0.1); padding: 10px; border-radius: 4px;'>Profile updated successfully!</p>";
    } catch (Exception $e) {
        $message = "<p style='color: #dc3545; background: rgba(220, 53, 69, 0.1); padding: 10px; border-radius: 4px;'>Update failed: " . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - BalikGamit</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0a0a0a; color: white;">
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>
        
        <div class="main-content" style="display: flex; flex-direction: column;">

            <div class="content-body" style="padding-top: 20px;">
                <h2>Account Settings</h2>
                <p style="color: #888;">Update your contact information and security credentials.</p>
                <hr style="border: 0; border-top: 1px solid #333; margin: 20px 0;">

                <?php echo $message; ?>

                <form action="settings.php" method="POST" style="max-width: 500px;">
                    <fieldset style="border: 1px solid #333; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <legend style="padding: 0 10px; font-weight: bold; color: #007bff;">Contact Information</legend>
                        
                        <div style="margin-bottom: 15px;">
                            <label>New Email Address:</label><br>
                            <input type="email" name="email" placeholder="email@example.com" required 
                                   style="width: 100%; padding: 10px; background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 4px; margin-top: 5px;">
                        </div>

                        <div style="margin-bottom: 5px;">
                            <label>New Contact Number:</label><br>
                            <input type="text" name="phone" placeholder="09123456789" required 
                                   style="width: 100%; padding: 10px; background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 4px; margin-top: 5px;">
                        </div>
                    </fieldset>

                    <fieldset style="border: 1px solid #333; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <legend style="padding: 0 10px; font-weight: bold; color: #007bff;">Security</legend>
                        <p style="font-size: 12px; color: #888; margin-bottom: 15px;">Leave blank to keep your current password.</p>
                        
                        <div style="margin-bottom: 15px;">
                            <label>New Password:</label><br>
                            <input type="password" name="pass" 
                                   style="width: 100%; padding: 10px; background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 4px; margin-top: 5px;">
                        </div>

                        <div style="margin-bottom: 5px;">
                            <label>Confirm New Password:</label><br>
                            <input type="password" name="conf_pass" 
                                   style="width: 100%; padding: 10px; background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 4px; margin-top: 5px;">
                        </div>
                    </fieldset>

                    <button type="submit" 
                            style="background: #007bff; color: white; border: none; padding: 12px 25px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%;">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>