<?php 
require '../config/db.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user = $_SESSION['user_id'];
$message = "";

// Fetch current user data to pre-fill fields
$fetch_sql = "SELECT First_Name, Last_Name, Email_Address, Contact_Number, Password FROM User_Table WHERE User_ID = ?";
$fetch_stmt = mysqli_prepare($conn, $fetch_sql);
mysqli_stmt_bind_param($fetch_stmt, "i", $current_user);
mysqli_stmt_execute($fetch_stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($fetch_stmt));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $current_pass = $_POST['current_pass'];
    $new_pass = $_POST['new_pass'];

    try {
        // Verification: If changing password, verify the current one
        if (!empty($new_pass)) {
            if (empty($current_pass) || !password_verify($current_pass, $user_data['Password'])) {
                throw new Exception("Current password incorrect or missing.");
            }
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $sql = "UPDATE User_Table SET First_Name = ?, Last_Name = ?, Email_Address = ?, Contact_Number = ?, Password = ? WHERE User_ID = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssi", $first_name, $last_name, $email, $phone, $hashed_password, $current_user);
        } else {
            $sql = "UPDATE User_Table SET First_Name = ?, Last_Name = ?, Email_Address = ?, Contact_Number = ? WHERE User_ID = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $first_name, $last_name, $email, $phone, $current_user);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "<p style='color: #2ecc71; background: rgba(46, 204, 113, 0.1); padding: 10px; border-radius: 4px;'>Profile updated successfully!</p>";
            // Refresh local data
            $user_data['First_Name'] = $first_name;
            $user_data['Last_Name'] = $last_name;
            $user_data['Email_Address'] = $email;
            $user_data['Contact_Number'] = $phone;
        }
    } catch (Exception $e) {
        $message = "<p style='color: #e74c3c; background: rgba(231, 76, 60, 0.1); padding: 10px; border-radius: 4px;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - BalikGamit</title>
    <style>
        body { margin: 0; padding: 0; background-color: #0a0a0a; color: white; font-family: 'Segoe UI', sans-serif; }
        .app-container { display: flex; }
        .main-content { flex: 1; padding: 40px; }
        .settings-container { max-width: 800px; }
        
        h2 { font-size: 20px; margin-bottom: 25px; font-weight: 500; }
        .section-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block; }
        
        .input-group { margin-bottom: 25px; }
        .row { display: flex; gap: 20px; margin-bottom: 25px; }
        .col { flex: 1; }

        input {
            width: 100%;
            padding: 12px;
            background: #111;
            border: 1px solid #222;
            color: white;
            border-radius: 6px;
            box-sizing: border-box;
            margin-top: 5px;
        }

        input:focus { outline: none; border-color: #3498db; }

        .btn-save {
            background: #0d1b2a; /* Dark blue button styling */
            color: white;
            border: 1px solid #1f3a5f;
            padding: 14px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn-save:hover { background: #162a44; }
        
        hr { border: 0; border-top: 1px solid #222; margin: 30px 0; }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="settings-container">
                <h2>Profile Settings</h2>
                <?php echo $message; ?>

                <form action="settings.php" method="POST">
                    <!-- Name Row -->
                    <div class="row">
                        <div class="col">
                            <span class="section-label">First Name</span>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user_data['First_Name']); ?>" required>
                        </div>
                        <div class="col">
                            <span class="section-label">Last Name</span>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user_data['Last_Name']); ?>" required>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="input-group">
                        <span class="section-label">Email</span>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['Email_Address']); ?>" required>
                    </div>

                    <!-- Contact Number -->
                    <div class="input-group">
                        <span class="section-label">Contact Number</span>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user_data['Contact_Number']); ?>" required>
                    </div>

                    <hr>

                    <h2>Change Password</h2>
                    <div class="row">
                        <div class="col">
                            <span class="section-label">Current Password</span>
                            <input type="password" name="current_pass" placeholder="Current password">
                        </div>
                        <div class="col">
                            <span class="section-label">New Password</span>
                            <input type="password" name="new_pass" placeholder="New password">
                        </div>
                    </div>

                    <button type="submit" class="btn-save">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>