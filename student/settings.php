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
            $message = "<div class='report-alert report-alert--success'><i class='fa-solid fa-circle-check'></i> Profile updated successfully!</div>";
            $user_data['First_Name'] = $first_name;
            $user_data['Last_Name'] = $last_name;
            $user_data['Email_Address'] = $email;
            $user_data['Contact_Number'] = $phone;
        }
    } catch (Exception $e) {
        $message = "<div class='report-alert report-alert--error'><i class='fa-solid fa-circle-exclamation'></i> Error: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - BalikGamit</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .report-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-top: 20px;
        }

        .report-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .report-form-group {
            margin-bottom: 20px;
        }

        .report-form-group label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            color: #435ebe;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .report-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .report-input-wrap i {
            position: absolute;
            left: 15px;
            color: #adb5bd;
            font-size: 14px;
        }

        .report-input-wrap input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #dce7f1;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
        }

        .report-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            border-top: 1px solid #f1f1f1;
            padding-top: 20px;
        }

        .report-btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
            transition: 0.3s;
        }

        .report-btn--cancel {
            background: #fff;
            border: 1px solid #dce7f1;
            color: #666;
        }

        .report-btn--submit {
            background: #1e293b;
            color: #fff;
        }

        .report-alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .report-alert--success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .report-alert--error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .section-title {
            font-size: 16px;
            color: #1e293b;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        hr { border: 0; border-top: 1px solid #f1f1f1; margin: 30px 0; }

        @media (max-width: 768px) {
            .report-form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="dashboard-header">
                <div class="dashboard-header-left">
                    <span class="dashboard-section-label">Account Management</span>
                    <h1>Settings</h1>
                    <p>Update your personal information and keep your account secure.</p>
                </div>
                <div class="dashboard-user-card">
                    <div class="user-avatar-circle">
                        <?php echo strtoupper(substr($user_data['First_Name'], 0, 1)); ?>
                    </div>
                    <div class="user-card-info">
                        <span class="user-card-name"><?php echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']); ?></span>
                        <span class="user-card-status">● Online</span>
                    </div>
                </div>
            </div>

            <?php echo $message; ?>

            <div class="report-card">
                <form action="settings.php" method="POST">
                    
                    <h2 class="section-title"><i class="fa-solid fa-user-gear"></i> Personal Profile</h2>
                    <div class="report-form-grid">
                        <div class="report-form-group">
                            <label for="firstName">First Name</label>
                            <div class="report-input-wrap">
                                <i class="fa-solid fa-address-card"></i>
                                <input type="text" id="firstName" name="first_name" value="<?php echo htmlspecialchars($user_data['First_Name']); ?>" required>
                            </div>
                        </div>

                        <div class="report-form-group">
                            <label for="lastName">Last Name</label>
                            <div class="report-input-wrap">
                                <i class="fa-solid fa-address-card"></i>
                                <input type="text" id="lastName" name="last_name" value="<?php echo htmlspecialchars($user_data['Last_Name']); ?>" required>
                            </div>
                        </div>

                        <div class="report-form-group">
                            <label for="email">Email Address</label>
                            <div class="report-input-wrap">
                                <i class="fa-solid fa-envelope"></i>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['Email_Address']); ?>" required>
                            </div>
                        </div>

                        <div class="report-form-group">
                            <label for="phone">Contact Number</label>
                            <div class="report-input-wrap">
                                <i class="fa-solid fa-phone"></i>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['Contact_Number']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h2 class="section-title"><i class="fa-solid fa-shield-halved"></i> Security</h2>
                    <div class="report-form-grid">
                        <div class="report-form-group">
                            <label for="currentPass">Current Password</label>
                            <div class="report-input-wrap">
                                <i class="fa-solid fa-lock-open"></i>
                                <input type="password" id="currentPass" name="current_pass" placeholder="Required for password change">
                            </div>
                        </div>

                        <div class="report-form-group">
                            <label for="newPass">New Password</label>
                            <div class="report-input-wrap">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" id="newPass" name="new_pass" placeholder="Leave blank to keep current">
                            </div>
                        </div>
                    </div>

                    <div class="report-form-actions">
                        <a href="home.php" class="report-btn report-btn--cancel">Discard Changes</a>
                        <button type="submit" class="report-btn report-btn--submit">
                            <i class="fa-solid fa-floppy-disk"></i> Save Settings
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</body>
</html>