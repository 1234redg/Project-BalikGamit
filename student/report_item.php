<?php 
require '../config/db.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

            <!-- Page Header -->
            <div class="dashboard-header">
                <div class="dashboard-header-left">
                    <span class="dashboard-section-label">Submit a Report</span>
                    <h1>Report an Item</h1>
                    <p>Fill in the details below to list a lost or found item on the BalikGamit board.</p>
                </div>
                <div class="dashboard-user-card">
                    <div class="user-avatar-circle">
                        <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'U'; ?>
                    </div>
                    <div class="user-card-info">
                        <span class="user-card-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></span>
                        <span class="user-card-status">● Online</span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="report-login-notice">
                    <i class="fa-solid fa-lock"></i>
                    <p>Please <a href="login.php">log in</a> to report an item.</p>
                </div>
            <?php else: ?>

                <?php echo $message; ?>

                <div class="report-card">
                    <!-- Status Toggle -->
                    <div class="report-status-toggle">
                        <label class="report-status-option">
                            <input type="radio" name="status_preview" value="Lost" checked>
                            <span class="report-status-chip report-status-chip--lost">
                                <i class="fa-solid fa-magnifying-glass"></i> Lost Item
                            </span>
                        </label>
                        <label class="report-status-option">
                            <input type="radio" name="status_preview" value="Found">
                            <span class="report-status-chip report-status-chip--found">
                                <i class="fa-solid fa-hand-holding-heart"></i> Found Item
                            </span>
                        </label>
                    </div>

                    <form action="../actions/report/add_report.php" method="POST" enctype="multipart/form-data" class="report-form">
                        <!-- Hidden status field synced with toggle above -->
                        <input type="hidden" name="status" id="statusInput" value="Lost">

                        <div class="report-form-grid">
                            <!-- Left Column -->
                            <div class="report-form-col">

                                <div class="report-form-group">
                                    <label for="itemName">Item Name</label>
                                    <div class="report-input-wrap">
                                        <i class="fa-solid fa-tag"></i>
                                        <input type="text" id="itemName" name="name" placeholder="e.g. Black Wallet" required>
                                    </div>
                                </div>

                                <div class="report-form-group">
                                    <label for="catId">Category</label>
                                    <div class="report-input-wrap">
                                        <i class="fa-solid fa-layer-group"></i>
                                        <select id="catId" name="cat_id" required>
                                            <option value="">— Select Category —</option>
                                            <?php
                                            $catResult = mysqli_query($conn, "SELECT * FROM category_table");
                                            while ($row = mysqli_fetch_assoc($catResult)) {
                                                echo "<option value='{$row['Category_ID']}'>{$row['Category']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="report-form-group">
                                    <label for="itemLoc">Location Last Seen</label>
                                    <div class="report-input-wrap">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <input type="text" id="itemLoc" name="loc" placeholder="Specific building or room" required>
                                    </div>
                                </div>

                                <div class="report-form-group">
                                    <label for="itemDate">Date</label>
                                    <div class="report-input-wrap">
                                        <i class="fa-regular fa-calendar"></i>
                                        <input type="date" id="itemDate" name="date" required>
                                    </div>
                                </div>

                            </div>

                            <!-- Right Column -->
                            <div class="report-form-col">

                                <div class="report-form-group report-form-group--full">
                                    <label for="itemDesc">Description</label>
                                    <textarea id="itemDesc" name="desc" placeholder="Describe the item in as much detail as possible — color, brand, markings…"></textarea>
                                </div>

                                <div class="report-form-group report-form-group--full">
                                    <label>Upload Photo <span class="report-label-optional">(Optional)</span></label>
                                    <label for="itemPhoto" class="report-file-drop">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span class="report-file-drop__text">Click to upload or drag & drop</span>
                                        <span class="report-file-drop__hint">PNG, JPG, WEBP — max 5 MB</span>
                                        <input type="file" id="itemPhoto" name="item_photo" accept="image/*">
                                    </label>
                                    <span class="report-file-name" id="fileName">No file chosen</span>
                                </div>

                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="report-form-actions">
                            <a href="home.php" class="report-btn report-btn--cancel">Cancel</a>
                            <button type="submit" class="report-btn report-btn--submit">
                                <i class="fa-solid fa-paper-plane"></i> Submit Report
                            </button>
                        </div>

                    </form>
                </div>

            <?php endif; ?>

        </div>
    </div>

    <script>
        // Sync the visual toggle with the hidden input
        document.querySelectorAll('input[name="status_preview"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.getElementById('statusInput').value = this.value;
            });
        });

        // Show selected filename
        document.getElementById('itemPhoto')?.addEventListener('change', function () {
            const name = this.files[0] ? this.files[0].name : 'No file chosen';
            document.getElementById('fileName').textContent = name;
        });
    </script>
</body>
</html>