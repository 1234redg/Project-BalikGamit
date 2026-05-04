<<<<<<< Updated upstream
<?php 
=======
<?php
// 1. Core Logic & Database Connection
>>>>>>> Stashed changes
require '../config/db.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
<<<<<<< Updated upstream
=======

// Security Check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch User Info using the new user_table structure
$displayName = 'Guest';
$initial = 'G';
$userId = $_SESSION['user_id'];

$userQuery = 'SELECT First_Name, Last_Name, Username FROM user_table WHERE User_ID = ?';
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($userResult)) {
    $displayName = htmlspecialchars($user['First_Name'] ?: $user['Username']);
    $initial = strtoupper(substr($displayName, 0, 1));
}

// 2. Handle Filters
$search      = trim($_GET['search']      ?? '');
$status      = trim($_GET['status']      ?? 'all');
$category_id = (int)($_GET['category_id'] ?? 0);

// Fetch Categories for dropdown
$categories = [];
$catResult = mysqli_query($conn, 'SELECT Category_ID, Category FROM category_table ORDER BY Category ASC');
while ($cat = mysqli_fetch_assoc($catResult)) {
    $categories[] = $cat;
}

// 3. Build Main Query - CHANGED Publication_ID to Report_ID
$conditions = [];
if ($status !== 'all') { $conditions[] = "i.Item_Status = '$status'"; }
if ($category_id > 0)  { $conditions[] = "i.Category_ID = $category_id"; }
if ($search !== '')    { 
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    $conditions[] = "(i.Item_Name LIKE '%$searchEscaped%' OR r.Location LIKE '%$searchEscaped%')"; 
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Finalized Query structure matching your system's Report_ID requirement
$mainQuery = "
    SELECT r.Report_ID, r.Date_Filed, r.Location, i.Item_Name, i.Item_Status, i.Item_Image, c.Category
    FROM reports_table r
    INNER JOIN item_table i ON i.Item_ID = r.Item_ID
    INNER JOIN category_table c ON c.Category_ID = i.Category_ID
    $where
    ORDER BY r.Date_Filed DESC
";

$itemsResult = mysqli_query($conn, $mainQuery);
if (!$itemsResult) {
    die("Query Failed: " . mysqli_error($conn));
}
$itemCount = mysqli_num_rows($itemsResult);
>>>>>>> Stashed changes
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BalikGamit</title>
<<<<<<< Updated upstream
</head>
<body style="margin: 0; padding: 0; background-color: #0a0a0a; color: white;">
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>
        
        <div class="main-content" style="display: flex; flex-direction: column;">
            <div class="content-body" style="padding-top: 20px;">
                <h1>Welcome to BalikGamit</h1>
                <p style="color: #888;">Select an option from the sidebar to manage lost and found items.</p>
                <hr style="border: 0; border-top: 1px solid #333; margin: 20px 0;">
                
                <div style="border: 2px dashed #333; padding: 40px; text-align: center; border-radius: 8px;">
                    <p style="color: #555;">Dashboard content coming soon...</p>
                </div>
            </div>
        </div>
    </div>
=======
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .result-count { font-size: 12px; color: var(--text-muted); margin-bottom: 10px; }
        .result-count strong { color: var(--text-primary); font-weight: 600; }
        .item-image img { width: 100%; height: 200px; object-fit: cover; border-radius: 12px 12px 0 0; }
    </style>
</head>
<body>
<div class="app-container">

    <?php include_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- HEADER -->
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <h1>Welcome back, <?php echo $displayName; ?></h1>
                <p>Track lost and found items in real time.</p>
            </div>
            <div class="dashboard-user-card">
                <div class="user-avatar-circle"><?php echo $initial; ?></div>
                <div class="user-card-info">
                    <span class="user-card-name"><?php echo $displayName; ?></span>
                    <span class="user-card-status">● Online</span>
                </div>
            </div>
        </div>

        <!-- CONTROLS -->
        <form method="GET" action="dashboard.php" class="dashboard-controls">
            <div class="search-panel">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="status-filters">
                    <a href="?status=all&category_id=<?php echo $category_id; ?>" class="status-btn <?php echo $status == 'all' ? 'active' : ''; ?>">All</a>
                    <a href="?status=Lost&category_id=<?php echo $category_id; ?>" class="status-btn <?php echo $status == 'Lost' ? 'active' : ''; ?>">Lost</a>
                    <a href="?status=Found&category_id=<?php echo $category_id; ?>" class="status-btn <?php echo $status == 'Found' ? 'active' : ''; ?>">Found</a>
                </div>
            </div>
            <div class="category-panel">
                <label>Category</label>
                <select name="category_id" onchange="this.form.submit()">
                    <option value="0">All categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['Category_ID']; ?>" <?php echo $category_id == $cat['Category_ID'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['Category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
            </div>
        </form>

        <p class="result-count">Showing <strong><?php echo $itemCount; ?></strong> item(s)</p>

        <!-- CARDS GRID -->
        <div class="cards-grid">
            <?php if ($itemCount > 0): ?>
                <?php while($item = mysqli_fetch_assoc($itemsResult)): ?>
                    <?php 
                        $statusCls = ($item['Item_Status'] === 'Lost') ? 'status-lost' : 'status-found';
                        $imagePath = !empty($item['Item_Image']) 
                                     ? "../" . $item['Item_Image'] 
                                     : "../assets/images/placeholder.jpg"; 
                    ?>
                    <!-- UPDATED link to use id=Report_ID[cite: 8] -->
                    <div class="item-card" onclick="location.href='item_details.php?id=<?php echo $item['Report_ID']; ?>'">
                        <div class="item-image">
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($item['Item_Name']); ?>" onerror="this.src='../assets/images/placeholder.jpg';">
                        </div>
                        <div class="item-card-header">
                            <span class="item-status <?php echo $statusCls; ?>"><?php echo htmlspecialchars($item['Item_Status']); ?></span>
                            <span class="item-category"><?php echo htmlspecialchars($item['Category']); ?></span>
                        </div>
                        <div class="item-card-body">
                            <h3 class="item-title"><?php echo htmlspecialchars($item['Item_Name']); ?></h3>
                            <p class="item-meta">Location: <?php echo htmlspecialchars($item['Location']); ?></p>
                        </div>
                        <div class="item-card-footer">
                            <span class="item-date"><?php echo date('M d, Y', strtotime($item['Date_Filed'])); ?></span>
                            <span class="item-action">View Details →</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="dashboard-empty">
                    <i class="fa-solid fa-box-open" style="font-size:32px;color:#9ca3af;"></i>
                    <p>No items found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
>>>>>>> Stashed changes
</body>
</html>