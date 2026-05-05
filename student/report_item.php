<?php
require '../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// --- START OF FIX: Fetch consistent display name ---
$displayName = 'Guest';
$initial = 'G';
$userId = $_SESSION['user_id'];

$query = 'SELECT First_Name, Last_Name, Username FROM User_Table WHERE User_ID = ?';
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    $firstName = htmlspecialchars($user['First_Name'] ?? '');
    $lastName = htmlspecialchars($user['Last_Name'] ?? '');
    $displayName = trim($firstName . ' ' . $lastName);

    if (empty($displayName)) {
        $displayName = htmlspecialchars($user['Username']);
    }
    $initial = strtoupper(substr($displayName, 0, 1));
}
// --- END OF FIX ---

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

    <style>
        /* Within-file CSS to match image_561b82.jpg */

        .report-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        /* Status Toggle (Radio Buttons to Chips) */
        .report-status-toggle {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .report-status-option input[type="radio"] {
            display: none;
        }

        .report-status-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 50px;
            border: 1px solid #e0e0e0;
            background: #fff;
            cursor: pointer;
            font-size: 14px;
            color: #666;
            transition: 0.3s;
        }

        .report-status-option input[type="radio"]:checked+.report-status-chip--lost {
            background: #fee2e2;
            color: #ef4444;
            border-color: #fca5a5;
        }

        .report-status-option input[type="radio"]:checked+.report-status-chip--found {
            background: #f3f4f6;
            color: #374151;
            border-color: #d1d5db;
        }

        /* Form Grid Layout */
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
            /* Blue accent from labels */
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        /* Input Styling */
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

        .report-input-wrap input,
        .report-input-wrap select,
        .report-form-group textarea {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #dce7f1;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
        }

        .report-form-group textarea {
            padding: 15px;
            height: 150px;
            resize: none;
        }

        /* File Upload Box */
        .report-file-drop {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 40px;
            border: 2px dashed #435ebe;
            background: #f0f3ff;
            border-radius: 12px;
            cursor: pointer;
            color: #435ebe;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
        }

        .report-file-drop i {
            font-size: 24px;
        }

        .report-file-drop input {
            display: none;
        }

        .report-file-drop__hint {
            color: #8a94ad;
            font-weight: 400;
        }

        .report-file-name {
            display: block;
            margin-top: 10px;
            font-size: 12px;
            color: #999;
            font-style: italic;
        }

        /* Action Buttons */
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

        .report-btn:hover {
            opacity: 0.9;
        }

        /* Validation Error Message */
        .report-validation-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 20px;
            color: #b91c1c;
            font-size: 14px;
            display: none;
            align-items: center;
            gap: 10px;
        }

        .report-validation-error.show { display: flex; }

        .report-validation-error i { font-size: 16px; flex-shrink: 0; }

        @media (max-width: 768px) {
            .report-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                    <!-- Updated to use $initial -->
                    <div class="user-avatar-circle"><?php echo $initial; ?></div>
                    <div class="user-card-info">
                        <!-- Updated to use $displayName -->
                        <span class="user-card-name"><?php echo $displayName; ?></span>
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
                    <!-- Validation Error Alert -->
                    <div id="validationError" class="report-validation-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Please fill in all required fields.</span>
                    </div>

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

<<<<<<< HEAD
                    <form action="../actions/report/add_report.php" method="POST" enctype="multipart/form-data" class="report-form" id="reportForm">
=======
                    <form action="../actions/report/add_report.php" method="POST" enctype="multipart/form-data"
                        class="report-form">
>>>>>>> ab44e7e9c95c125440b007bcdcde8b32efe2a107
                        <!-- Hidden status field synced with toggle above -->
                        <input type="hidden" name="status" id="statusInput" value="Lost">

                        <div class="report-form-grid">
                            <!-- Left Column -->
                            <div class="report-form-col">

                                <div class="report-form-group">
                                    <label for="itemName">Item Name</label>
                                    <div class="report-input-wrap">
                                        <i class="fa-solid fa-tag"></i>
                                        <input type="text" id="itemName" name="name" placeholder="e.g. Black Wallet"
                                            required>
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
                                        <input type="text" id="itemLoc" name="loc" placeholder="Specific building or room"
                                            required>
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
                                    <textarea id="itemDesc" name="desc"
                                        placeholder="Describe the item in as much detail as possible — color, brand, markings…"></textarea>
                                </div>

                                <div class="report-form-group report-form-group--full">
                                    <label>Upload Photo <span class="report-label-optional">(Optional)</span></label>
                                    <label for="itemPhoto" class="report-file-drop">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>Click to upload or drag & drop</span>
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
        
    const reportForm = document.getElementById('reportForm');
    const validationError = document.getElementById('validationError');
    const validationMsg = validationError.querySelector('span');
    const photoInput = document.getElementById('itemPhoto');
    const statusInputHidden = document.getElementById('statusInput');

    const requiredFields = [
        { id: 'itemName', name: 'Item Name' },
        { id: 'catId',    name: 'Category' },
        { id: 'itemLoc',  name: 'Location Last Seen' },
        { id: 'itemDate', name: 'Date' },
        { id: 'itemDesc', name: 'Description' }
    ];

    function validateForm() {
        // Check all required text fields first
        for (const field of requiredFields) {
            const element = document.getElementById(field.id);
            const value = element?.value?.trim();
            if (!value) {
                showValidationError(`Please fill in the <strong>${field.name}</strong> field.`);
                return false;
            }
        }

        // Check photo upload — required for both Lost and Found
        if (!photoInput.files || photoInput.files.length === 0) {
            const currentStatus = statusInputHidden.value;

            if (currentStatus === 'Found') {
                showValidationError(
                    'A photo is required for <strong>Found</strong> items. Please upload a clear photo of the item you found.'
                );
            } else {
                showValidationError(
                    'Please upload a photo for your <strong>Lost</strong> item. A similar or actual photo helps others identify it faster.'
                );
            }
            return false;
        }

        // Check file size max 5MB
        const file = photoInput.files[0];
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            showValidationError('The uploaded file is too large. Maximum allowed size is <strong>5 MB</strong>.');
            return false;
        }

        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showValidationError('Invalid file type. Please upload a <strong>JPG, PNG, or WEBP</strong> image.');
            return false;
        }

        return true;
    }

    function showValidationError(message) {
        validationMsg.innerHTML = message;
        validationError.classList.add('show');
        validationError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideValidationError() {
        validationError.classList.remove('show');
    }

    reportForm?.addEventListener('submit', function (e) {
        if (!validateForm()) {
            e.preventDefault();
        } else {
            hideValidationError();
        }
    });

    // Hide error when user starts typing or changes any field
    requiredFields.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            element.addEventListener('input', hideValidationError);
            element.addEventListener('change', hideValidationError);
        }
    });

    // Hide error when photo is selected
    photoInput?.addEventListener('change', function () {
        const name = this.files[0] ? this.files[0].name : 'No file chosen';
        document.getElementById('fileName').textContent = name;
        hideValidationError();
    });

    // Sync the visual toggle with the hidden input
    document.querySelectorAll('input[name="status_preview"]').forEach(radio => {
        radio.addEventListener('change', function () {
            statusInputHidden.value = this.value;
        });
    });
</script>
</body>

</html>