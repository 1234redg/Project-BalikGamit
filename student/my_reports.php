<?php
require '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$search        = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'All';
$cat_filter    = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;

// Fetch user display name
$user_stmt = mysqli_prepare($conn, "SELECT First_Name, Last_Name FROM user_table WHERE User_ID = ?");
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_data   = mysqli_fetch_assoc(mysqli_stmt_get_result($user_stmt));
$displayName = trim(($user_data['First_Name'] ?? '') . ' ' . ($user_data['Last_Name'] ?? ''));
if (empty($displayName)) $displayName = 'Guest';

// Flash messages[cite: 1]
$flash = '';
if (isset($_SESSION['msg'])) {
    if ($_SESSION['msg'] === 'updated') {
        $flash = "<div class='myreports-alert myreports-alert--success'><i class='fa-solid fa-circle-check'></i> Report updated successfully!</div>";
    } elseif ($_SESSION['msg'] === 'deleted') {
        $flash = "<div class='myreports-alert myreports-alert--success'><i class='fa-solid fa-trash-can'></i> Report deleted successfully.</div>";
    } elseif ($_SESSION['msg'] === 'error') {
        $details = $_SESSION['error_details'] ?? 'Unknown error';
        $flash = "<div class='myreports-alert myreports-alert--error'><i class='fa-solid fa-circle-exclamation'></i> Error: " . htmlspecialchars($details) . "</div>";
    }
    unset($_SESSION['msg'], $_SESSION['error_details']);
}

// Build query[cite: 1]
$where  = "WHERE r.User_ID = ?";
$params = [$user_id];
$types  = 'i';

if ($search !== '') {
    $where   .= " AND (i.Item_Name LIKE ? OR r.Location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types   .= 'ss';
}
if ($status_filter !== 'All') {
    $where   .= " AND i.Item_Status = ?";
    $params[] = $status_filter;
    $types   .= 's';
}
if ($cat_filter > 0) {
    $where   .= " AND i.Category_ID = ?";
    $params[] = $cat_filter;
    $types   .= 'i';
}

$sql = "SELECT r.Report_ID, r.Date_filed, r.Location,
               i.Item_ID, i.Item_Name, i.Item_Status, i.Item_Description, i.Item_Image, i.Category_ID,
               c.Category
        FROM reports_table r
        JOIN item_table i ON r.Item_ID = i.Item_ID
        LEFT JOIN category_table c ON i.Category_ID = c.Category_ID
        $where
        ORDER BY r.Date_filed DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$reports = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
$total   = count($reports);

// Categories for dropdowns[cite: 1]
$catResult  = mysqli_query($conn, "SELECT * FROM category_table ORDER BY Category ASC");
$categories = mysqli_fetch_all($catResult, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - BalikGamit</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app-container">
    <?php include_once '../includes/sidebar.php'; ?>

    <div class="main-content">

        <!-- Header -->
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <span class="dashboard-section-label">Your Activity</span>
                <h1>My Reports</h1>
                <p>View and manage all the items you've reported.</p>
            </div>
            <div class="dashboard-user-card">
                <div class="user-avatar-circle"><?= strtoupper(substr($displayName, 0, 1)) ?></div>
                <div class="user-card-info">
                    <span class="user-card-name"><?= htmlspecialchars($displayName) ?></span>
                    <span class="user-card-status">● Online</span>
                </div>
            </div>
        </div>

        <?= $flash ?>

        <!-- Controls -->
        <form method="GET" action="" class="dashboard-controls">
            <div class="search-panel">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search"
                           placeholder="Search by item name or location…"
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="status-filters">
                    <?php foreach (['All', 'Lost', 'Found'] as $s): ?>
                        <button type="submit" name="status" value="<?= $s ?>"
                                class="status-btn <?= $status_filter === $s ? 'active' : '' ?>">
                            <?= $s ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="category-panel">
                <label>Category</label>
                <select name="cat_id" onchange="this.form.submit()">
                    <option value="0">All categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['Category_ID'] ?>"
                            <?= $cat_filter === (int)$cat['Category_ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['Category']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <a href="report_item.php" class="myreports-new-btn">
                <i class="fa-solid fa-plus"></i> New Report
            </a>
        </form>

        <!-- Count -->
        <p class="myreports-count">
            Showing <strong><?= $total ?></strong> report<?= $total !== 1 ? 's' : '' ?>
            <?= $search !== '' ? ' for "<strong>' . htmlspecialchars($search) . '</strong>"' : '' ?>
        </p>

        <!-- Cards -->
        <?php if ($total === 0): ?>
            <div class="dashboard-empty">
                <i class="fa-solid fa-box-open"></i>
                <p>No reports found. <a href="report_item.php" class="myreports-link">File one now →</a></p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($reports as $row):
                    $statusClass = 'status-' . strtolower($row['Item_Status']);
                    
                    // FIXED IMAGE PATH LOGIC[cite: 3]
                    $imgPath = !empty($row['Item_Image'])
                        ? '../' . htmlspecialchars($row['Item_Image'])
                        : '../assets/images/placeholder.png';

                    $date = date('M d, Y', strtotime($row['Date_filed']));
                ?>
                <div class="item-card">
                    <div class="item-image">
                        <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($row['Item_Name']) ?>"
                             style="width:100%; height:100%; object-fit:cover;"
                             onerror="this.src='../assets/images/placeholder.png'">
                    </div>
                    <div class="item-card-header">
                        <span class="item-status <?= $statusClass ?>"><?= htmlspecialchars($row['Item_Status']) ?></span>
                        <?php if (!empty($row['Category'])): ?>
                            <span class="item-category"><?= htmlspecialchars($row['Category']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="item-card-body">
                        <h3 class="item-title"><?= htmlspecialchars($row['Item_Name']) ?></h3>
                        <p class="item-meta">
                            <i class="fa-solid fa-location-dot"></i>
                            <?= htmlspecialchars($row['Location']) ?>
                        </p>
                    </div>
                    <div class="item-card-footer">
                        <span class="item-date">
                            <i class="fa-regular fa-calendar"></i> <?= $date ?>
                        </span>
                        <div class="myreports-card-actions">
                            <button class="myreports-icon-btn myreports-icon-btn--edit"
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <button class="myreports-icon-btn myreports-icon-btn--delete"
                                    onclick="openDeleteModal(<?= $row['Report_ID'] ?>, '<?= htmlspecialchars(addslashes($row['Item_Name'])) ?>')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="dashboard-footer">
            <span>© 2026 BalikGamit — Async V.1.0</span>
            <a href="#">About Us</a>
        </div>

    </div>
</div>

<!-- ============ EDIT MODAL ============ -->
<div class="myreports-overlay" id="editOverlay">
    <div class="myreports-modal">
        <div class="myreports-modal-header">
            <h2><i class="fa-solid fa-pen"></i> Edit Report</h2>
            <button class="myreports-modal-close" onclick="closeEditModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="../actions/report/update_report.php" method="POST"
              enctype="multipart/form-data" class="myreports-modal-body">

            <input type="hidden" name="report_id" id="edit_report_id">
            <input type="hidden" name="item_id"   id="edit_item_id">

            <div class="myreports-modal-grid">

                <div class="myreports-modal-group myreports-modal-group--full">
                    <label>Status</label>
                    <div class="myreports-radio-row">
                        <label class="myreports-radio-opt">
                            <input type="radio" name="status" value="Lost" id="editLost"> Lost
                        </label>
                        <label class="myreports-radio-opt">
                            <input type="radio" name="status" value="Found" id="editFound"> Found
                        </label>
                    </div>
                </div>

                <div class="myreports-modal-group">
                    <label for="edit_name">Item Name</label>
                    <div class="report-input-wrap">
                        <i class="fa-solid fa-tag"></i>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                </div>

                <div class="myreports-modal-group">
                    <label for="edit_cat">Category</label>
                    <div class="report-input-wrap">
                        <i class="fa-solid fa-layer-group"></i>
                        <select id="edit_cat" name="cat_id" required>
                            <option value="">— Select —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['Category_ID'] ?>">
                                    <?= htmlspecialchars($cat['Category']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="myreports-modal-group">
                    <label for="edit_loc">Location</label>
                    <div class="report-input-wrap">
                        <i class="fa-solid fa-location-dot"></i>
                        <input type="text" id="edit_loc" name="loc" required>
                    </div>
                </div>

                <div class="myreports-modal-group">
                    <label for="edit_date">Date</label>
                    <div class="report-input-wrap">
                        <i class="fa-regular fa-calendar"></i>
                        <input type="date" id="edit_date" name="date" required>
                    </div>
                </div>

                <div class="myreports-modal-group myreports-modal-group--full">
                    <label for="edit_desc">Description</label>
                    <textarea id="edit_desc" name="desc" placeholder="Describe the item…"></textarea>
                </div>

                <div class="myreports-modal-group myreports-modal-group--full">
                    <label>Replace Photo <span class="report-label-optional">(Optional)</span></label>
                    <label for="edit_photo" class="report-file-drop">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span class="report-file-drop__text">Click to upload a new photo</span>
                        <span class="report-file-drop__hint">PNG, JPG, WEBP — max 5 MB</span>
                        <input type="file" id="edit_photo" name="item_photo" accept="image/*">
                    </label>
                    <span class="report-file-name" id="editFileName">No file chosen</span>
                </div>

            </div>

            <div class="myreports-modal-footer">
                <button type="button" class="report-btn report-btn--cancel" onclick="closeEditModal()">
                    Cancel
                </button>
                <button type="submit" class="report-btn report-btn--submit">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============ DELETE MODAL ============ -->
<div class="myreports-overlay" id="deleteOverlay">
    <div class="myreports-modal myreports-modal--sm">
        <div class="myreports-modal-header">
            <h2><i class="fa-solid fa-trash-can"></i> Delete Report</h2>
            <button class="myreports-modal-close" onclick="closeDeleteModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="myreports-delete-body">
            <div class="myreports-delete-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
            <p class="myreports-delete-sub">This action cannot be undone.</p>
        </div>
        <form action="../actions/report/delete_report.php" method="POST" class="myreports-modal-footer">
            <input type="hidden" name="report_id" id="delete_report_id">
            <button type="button" class="report-btn report-btn--cancel" onclick="closeDeleteModal()">
                Cancel
            </button>
            <button type="submit" class="report-btn report-btn--danger">
                <i class="fa-solid fa-trash-can"></i> Delete
            </button>
        </form>
    </div>
</div>

<script>
function openEditModal(row) {
    document.getElementById('edit_report_id').value = row.Report_ID;
    document.getElementById('edit_item_id').value   = row.Item_ID;
    document.getElementById('edit_name').value      = row.Item_Name;
    document.getElementById('edit_loc').value       = row.Location;
    document.getElementById('edit_date').value      = row.Date_filed;
    document.getElementById('edit_desc').value      = row.Item_Description ?? '';
    document.getElementById('edit_cat').value       = row.Category_ID;
    document.getElementById('editFileName').textContent = 'No file chosen';

    document.getElementById(row.Item_Status === 'Lost' ? 'editLost' : 'editFound').checked = true;

    document.getElementById('editOverlay').classList.add('active');
    document.body.classList.add('modal-open');
}
function closeEditModal() {
    document.getElementById('editOverlay').classList.remove('active');
    document.body.classList.remove('modal-open');
}

function openDeleteModal(id, name) {
    document.getElementById('delete_report_id').value     = id;
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('deleteOverlay').classList.add('active');
    document.body.classList.add('modal-open');
}
function closeDeleteModal() {
    document.getElementById('deleteOverlay').classList.remove('active');
    document.body.classList.remove('modal-open');
}

['editOverlay','deleteOverlay'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.id === 'editOverlay' ? closeEditModal() : closeDeleteModal();
    });
});

document.getElementById('edit_photo').addEventListener('change', function () {
    document.getElementById('editFileName').textContent = this.files[0] ? this.files[0].name : 'No file chosen';
});
</script>

<?php include_once '../logout-modal.php'; ?>
</body>
</html>