<?php 
require '../config/db.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$displayName = 'User';
$initial = 'U';

// Fetch User Info for the dashboard header
$userQuery = 'SELECT First_Name, Username FROM user_table WHERE User_ID = ?';
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($userResult)) {
    $displayName = htmlspecialchars($user['First_Name'] ?: $user['Username']);
    $initial = strtoupper(substr($displayName, 0, 1));
}

$message = "";
if (isset($_SESSION['msg'])) {
    if ($_SESSION['msg'] == "success") {
        $message = "<div class='report-alert report-alert--success'><i class='fa-solid fa-circle-check'></i> Report submitted successfully!</div>";
    } elseif ($_SESSION['msg'] == "error") {
        $details = $_SESSION['error_details'] ?? 'Unknown error';
        $message = "<div class='report-alert report-alert--error'><i class='fa-solid fa-circle-exclamation'></i> Failed: $details</div>";
    }
    unset($_SESSION['msg']);
    unset($_SESSION['error_details']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Item - BalikGamit</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>

        <div class="main-content">
            <!-- Header consistent with dashboard.php -->
            <div class="dashboard-header">
                <div class="dashboard-header-left">
                    <span class="dashboard-section-label">Main</span>
                    <h1>Report an Item</h1>
                    <p>Fill in the details to list a lost or found item on the board.</p>
                </div>
                <div class="dashboard-user-card">
                    <div class="user-avatar-circle"><?php echo $initial; ?></div>
                    <div class="user-card-info">
                        <span class="user-card-name"><?php echo $displayName; ?></span>
                        <span class="user-card-status">● Online</span>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <?php echo $message; ?>

                <div class="report-card">
                    <form action="../actions/report/add_report.php" method="POST" enctype="multipart/form-data">
                        
                        <!-- Status Toggle (uses .report-status-toggle classes) -->
                        <div class="report-status-toggle">
                            <label class="report-status-option">
                                <input type="radio" name="status" value="Lost" checked>
                                <span class="report-status-chip report-status-chip--lost">
                                    <i class="fa-solid fa-magnifying-glass"></i> Lost Item
                                </span>
                            </label>
                            <label class="report-status-option">
                                <input type="radio" name="status" value="Found">
                                <span class="report-status-chip report-status-chip--found">
                                    <i class="fa-solid fa-hand-holding-heart"></i> Found Item
                                </span>
                            </label>
                        </div>

                        <!-- Form Grid (uses .report-form-grid classes) -->
                        <div class="report-form-grid">
                            <!-- Left Column -->
                            <div class="report-form-col">
                                <div class="report-form-group">
                                    <label>Item Name</label>
                                    <div class="report-input-wrap">
                                        <i class="fa-solid fa-tag"></i>
                                        <input type="text" name="name" placeholder="e.g. Black Wallet" required>
                                    </div>
                                </div>

                                <div class="report-form-group">
                                    <label>Category</label>
                                    <div class="report-input-wrap">
                                        <i class="fa-solid fa-list"></i>
                                        <select name="cat_id" required>
                                            <option value="">Select Category</option>
                                            <?php
                                            $catResult = mysqli_query($conn, "SELECT * FROM category_table ORDER BY Category ASC");
                                            while ($row = mysqli_fetch_assoc($catResult)) {
                                                echo "<option value='{$row['Category_ID']}'>{$row['Category']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="report-form-group">
                                    <label>Location Last Seen</label>
                                    <div class="report-input-wrap">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <input type="text" name="loc" placeholder="Specific building or room" required>
                                    </div>
                                </div>

                                <div class="report-form-group">
                                    <label>Date</label>
                                    <div class="report-input-wrap">
                                        <i class="fa-solid fa-calendar-days"></i>
                                        <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="report-form-col">
                                <div class="report-form-group report-form-group--full">
                                    <label>Description</label>
                                    <textarea name="desc" placeholder="Describe markings, color, brand..."></textarea>
                                </div>

                                <div class="report-form-group report-form-group--full">
                                    <label>Upload Photo <span class="report-label-optional">(Optional)</span></label>
                                    <label for="itemPhoto" class="report-file-drop">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span class="report-file-drop__text">Click to upload photo</span>
                                        <span class="report-file-drop__hint">JPG, PNG or WEBP</span>
                                        <input type="file" id="itemPhoto" name="item_photo" accept="image/*">
                                    </label>
                                    <span class="report-file-name" id="fileName">No file chosen</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions (uses .report-form-actions classes) -->
                        <div class="report-form-actions">
                            <a href="dashboard.php" class="report-btn report-btn--cancel">Cancel</a>
                            <button type="submit" class="report-btn report-btn--submit">
                                <i class="fa-solid fa-paper-plane"></i> Submit Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update file name display
        document.getElementById('itemPhoto').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : "No file chosen";
            document.getElementById('fileName').textContent = fileName;
        });
    </script>
</body>
</html>