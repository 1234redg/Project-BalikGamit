<?php
require '../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin session check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$displayName = 'Admin';
$initial = 'A';
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    // Matches 'user_table' from your database schema[cite: 3]
    $query = 'SELECT First_Name, Last_Name, Username FROM user_table WHERE User_ID = ?';
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
}

/* ── FETCH DASHBOARD STATISTICS ── */
// 1. Total Reports from 'reports_table'[cite: 3]
$totalReportsQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM reports_table");
$totalReports = mysqli_fetch_assoc($totalReportsQuery)['total'] ?? 0;

// 2. Pending Claims from 'claims_table' using column 'Claim_Status'[cite: 3]
$pendingClaimsQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM claims_table WHERE Claim_Status = 'pending'");
$pendingClaims = mysqli_fetch_assoc($pendingClaimsQuery)['total'] ?? 0;

// 3. Total Users from 'user_table'[cite: 3]
$totalUsersQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM user_table");
$totalUsers = mysqli_fetch_assoc($totalUsersQuery)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BalikGamit</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 24px;
        }

        .stat-card {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.2s;
        }

        .stat-card:hover { transform: translateY(-5px); }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .icon-reports { background: #eef2ff; color: #4f46e5; }
        .icon-claims  { background: #fff7ed; color: #ea580c; }
        .icon-users   { background: #f0fdf4; color: #16a34a; }

        .stat-details h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .stat-details p {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 4px 0 0 0;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 32px;
            border-radius: 20px;
            color: #fff;
            margin-bottom: 30px;
        }

        .welcome-banner h1 { margin: 0; font-size: 24px; }
        .welcome-banner p { opacity: 0.8; margin: 8px 0 0 0; }
        
        .quick-actions { margin-top: 40px; }
        .action-btns { display: flex; gap: 15px; margin-top: 20px; }
    </style>
</head>
<body>
<div class="app-container">

    <?php include_once '../includes/sidebar_admin.php'; ?>

    <div class="main-content">

        <!-- HEADER -->
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <h1>Admin Portal</h1>
                <p>Overview of system activity and user reports.</p>
            </div>
            <div class="dashboard-user-card">
                <div class="user-avatar-circle"><?php echo $initial; ?></div>
                <div class="user-card-info">
                    <span class="user-card-name"><?php echo $displayName; ?></span>
                    <span class="user-card-status">● Admin Online</span>
                </div>
            </div>
        </div>

        <div class="welcome-banner">
            <h1>Welcome Back, <?php echo $displayName; ?>!</h1>
            <p>You have <strong><?php echo $pendingClaims; ?></strong> claim requests waiting for validation.</p>
        </div>

        <!-- STATISTICS CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-reports">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $totalReports; ?></h3>
                    <p>Total Reports</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-claims">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $pendingClaims; ?></h3>
                    <p>Pending Claims</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-users">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="quick-actions">
            <h2 style="font-size: 18px;">Administrative Tools</h2>
            <div class="action-btns">
                <!-- Replaced previous buttons with Request Approval[cite: 3] -->
                <a href="request_approval.php" class="myreports-new-btn" style="background: #ea580c;">
                    <i class="fa-solid fa-check-to-slot"></i> Request Approval
                </a>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="dashboard-footer" style="margin-top: 60px;">
            <span>© 2026 BalikGamit — System Administration</span>
        </div>

    </div>
</div>
</body>
</html>