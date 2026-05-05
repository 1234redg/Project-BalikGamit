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
$search  = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch user display name
$user_stmt = mysqli_prepare($conn, "SELECT First_Name, Last_Name FROM user_table WHERE User_ID = ?");
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_data   = mysqli_fetch_assoc(mysqli_stmt_get_result($user_stmt));
$displayName = trim(($user_data['First_Name'] ?? '') . ' ' . ($user_data['Last_Name'] ?? ''));
if (empty($displayName)) $displayName = 'Guest';

// Build query[cite: 1]
$where  = "WHERE c.User_ID = ?";
$params = [$user_id];
$types  = 'i';

if ($search !== '') {
    $where   .= " AND i.Item_Name LIKE ?";
    $params[] = "%$search%";
    $types   .= 's';
}

// JOIN logic: claims_table -> reports_table -> item_table[cite: 1]
$sql = "SELECT c.Claim_Request_ID, c.Claim_Status, c.Claim_Note,
               i.Item_Name, i.Item_Image, i.Item_Status as Original_Status,
               cat.Category,
               r.Date_filed
        FROM claims_table c
        JOIN reports_table r ON c.Report_ID = r.Report_ID
        JOIN item_table i ON r.Item_ID = i.Item_ID
        LEFT JOIN category_table cat ON i.Category_ID = cat.Category_ID
        $where
        ORDER BY r.Date_filed DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$claims = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
$total  = count($claims);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Claims - BalikGamit</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Table Specific Styling */
        .claims-table-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-top: 20px;
            overflow-x: auto;
        }
        .claims-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .claims-table th {
            background-color: #f8f9fa;
            padding: 15px;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #eee;
        }
        .claims-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .item-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .table-img {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            object-fit: cover;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .note-text {
            color: #666;
            font-size: 0.9rem;
            max-width: 250px;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
<div class="app-container">
    <?php include_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <span class="dashboard-section-label">Your Activity</span>
                <h1>My Claims</h1>
                <p>Track the status of items you have claimed.</p>
            </div>
            <div class="dashboard-user-card">
                <div class="user-avatar-circle"><?= strtoupper(substr($displayName, 0, 1)) ?></div>
                <div class="user-card-info">
                    <span class="user-card-name"><?= htmlspecialchars($displayName) ?></span>
                    <span class="user-card-status">● Online</span>
                </div>
            </div>
        </div>

        <!-- Search -->
        <form method="GET" action="" class="dashboard-controls">
            <div class="search-panel" style="flex: 1;">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" 
                           placeholder="Search your claims..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <button type="submit" class="status-btn active">Search</button>
        </form>

        <!-- Claims Table Section -->
        <?php if ($total === 0): ?>
            <div class="dashboard-empty">
                <i class="fa-solid fa-file-circle-question"></i>
                <p>You haven't made any claims yet.</p>
            </div>
        <?php else: ?>
            <div class="claims-table-container">
                <table class="claims-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Claim Note</th>
                            <th>Status</th>
                            <th>Date Filed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claims as $row): 
                            $status = strtolower($row['Claim_Status']);
                            $statusClass = 'status-' . $status;
                            
                            // Image Path Logic
                            $imgPath = !empty($row['Item_Image']) 
                                ? '../' . htmlspecialchars($row['Item_Image']) 
                                : '../assets/images/placeholder.png';
                        ?>
                        <tr>
                            <td>
                                <div class="item-cell">
                                    <img src="<?= $imgPath ?>" 
                                         alt="" 
                                         class="table-img"
                                         onerror="this.src='../assets/images/placeholder.png'">
                                    <strong><?= htmlspecialchars($row['Item_Name']) ?></strong>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['Category'] ?? 'Uncategorized') ?></td>
                            <td>
                                <span class="note-text">
                                    <?= !empty($row['Claim_Note']) ? '"' . htmlspecialchars($row['Claim_Note']) . '"' : '—' ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= htmlspecialchars(ucfirst($row['Claim_Status'])) ?>
                                </span>
                            </td>
                            <td>
                                <small style="color: #888;">
                                    <?= date('M d, Y', strtotime($row['Date_filed'])) ?>
                                </small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="dashboard-footer">
            <span>© 2026 BalikGamit — Async V.1.0</span>
        </div>
    </div>
</div>
</body>
</html>