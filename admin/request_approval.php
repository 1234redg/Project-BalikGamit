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

// Handle Approval or Rejection
if (isset($_POST['action']) && isset($_POST['claim_request_id'])) {
    $claimRequestId = $_POST['claim_request_id'];
    // Status 'claimed' matches the ENUM in your claims_table screenshot
    $status = ($_POST['action'] === 'approve') ? 'claimed' : 'rejected';
    
    $updateQuery = "UPDATE claims_table SET Claim_Status = ? WHERE Claim_Request_ID = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, 'si', $status, $claimRequestId);
    mysqli_stmt_execute($stmt);
}

/* ── FETCH ADMIN INFO ── */
$displayName = 'Admin';
$initial = 'A';
$userId = $_SESSION['user_id'];
$query = 'SELECT First_Name, Last_Name, Username FROM user_table WHERE User_ID = ?';
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($user = mysqli_fetch_assoc($result)) {
    $firstName = htmlspecialchars($user['First_Name'] ?? '');
    $lastName = htmlspecialchars($user['Last_Name'] ?? '');
    $displayName = trim($firstName . ' ' . $lastName);
    if (empty($displayName)) $displayName = htmlspecialchars($user['Username']);
    $initial = strtoupper(substr($displayName, 0, 1));
}

/* ── FETCH PENDING CLAIMS ── */
// Corrected: Using 'item_table' (singular) to match your structure
$pendingQuery = "SELECT 
                    c.Claim_Request_ID, 
                    c.Claim_Note, 
                    i.Item_Name, 
                    u.First_Name, 
                    u.Last_Name 
                 FROM claims_table c
                 JOIN reports_table r ON c.Report_ID = r.Report_ID
                 JOIN item_table i ON r.Item_ID = i.Item_ID
                 JOIN user_table u ON c.User_ID = u.User_ID
                 WHERE c.Claim_Status = 'pending'
                 ORDER BY c.Claim_Request_ID DESC";
$pendingResult = mysqli_query($conn, $pendingQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Approval - BalikGamit</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .approval-container {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-top: 24px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table th {
            text-align: left;
            padding: 12px 16px;
            background: #f8fafc;
            color: var(--text-secondary);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #edf2f7;
            font-size: 15px;
            color: var(--text-primary);
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .btn-approve { background: #dcfce7; color: #16a34a; }
        .btn-approve:hover { background: #16a34a; color: #fff; }

        .btn-reject { background: #fee2e2; color: #dc2626; }
        .btn-reject:hover { background: #dc2626; color: #fff; }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
<div class="app-container">

    <?php include_once '../includes/sidebar_admin.php'; ?>

    <div class="main-content">

        <!-- HEADER -->
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <h1>Request Approval</h1>
                <p>Review and validate pending item claims.</p>
            </div>
            <div class="dashboard-user-card">
                <div class="user-avatar-circle"><?php echo $initial; ?></div>
                <div class="user-card-info">
                    <span class="user-card-name"><?php echo $displayName; ?></span>
                    <span class="user-card-status">● Admin Online</span>
                </div>
            </div>
        </div>

        <div class="approval-container">
            <h2 style="font-size: 18px; margin-bottom: 10px;">Pending Requests</h2>
            
            <?php if ($pendingResult && mysqli_num_rows($pendingResult) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Claimant</th>
                            <th>Item Requested</th>
                            <th>Claim Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($pendingResult)): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['Item_Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Claim_Note'] ?? 'No note provided'); ?></td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 10px;">
                                        <input type="hidden" name="claim_request_id" value="<?php echo $row['Claim_Request_ID']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn-action btn-approve">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn-action btn-reject">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-circle-check" style="font-size: 48px; color: #16a34a; margin-bottom: 15px;"></i>
                    <p>All caught up! No pending claim requests at the moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- FOOTER -->
        <div class="dashboard-footer" style="margin-top: 60px;">
            <span>© 2026 BalikGamit — System Administration</span>
        </div>

    </div>
</div>
</body>
</html>