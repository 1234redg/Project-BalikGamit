<?php
require '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// Fetch user data for the header[cite: 4]
$user_sql = "SELECT First_Name, Last_Name FROM user_table WHERE User_ID = ?";
$user_stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($user_stmt));

// Corrected Query using actual column names: Item_Name and Item_Status
$query = "SELECT r.*, i.Item_Name, i.Item_Status 
          FROM reports_table r
          JOIN item_table i ON r.Item_ID = i.Item_ID
          WHERE r.User_ID = '$user_id' 
          AND (i.Item_Name LIKE '%$search%' OR i.Item_Status LIKE '%$search%')
          ORDER BY r.Date_filed DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - BalikGamit</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #121417;
            --card-bg: #1a1d21;
            --border-color: #2d3238;
            --text-main: #ffffff;
            --text-dim: #a0a6ac;
            --status-lost: #422020;
            --status-pending: #3d2e1e;
            --status-resolved: #1e3326;
        }

        body { background-color: var(--bg-dark); color: var(--text-main); font-family: 'Inter', sans-serif; margin: 0; }
        .main-content { padding: 40px; }

        .search-container { position: relative; margin-bottom: 25px; }
        .search-input {
            width: 100%;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            padding: 12px 12px 12px 45px;
            border-radius: 10px;
            color: white;
            font-size: 14px;
        }
        .search-container i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-dim); }

        .reports-table-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
        }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: var(--text-dim); font-weight: 500; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); font-size: 13px; }
        td { padding: 15px 0; border-bottom: 1px solid var(--border-color); font-size: 14px; }

        .item-link { color: #8ab4f8; text-decoration: none; font-weight: 500; }
        .badge { padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        
        /* Status styles matching the UI reference */
        .status-lost { background: var(--status-lost); color: #ff8080; }
        .status-found { background: var(--status-resolved); color: #80ffaa; }
        .status-pending { background: var(--status-pending); color: #ffb366; }

        .action-edit { color: #8ab4f8; text-decoration: none; margin-right: 15px; }
        .action-delete { color: #ff4d4d; text-decoration: none; }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>My Reports</h1>
                <div class="user-avatar-circle" style="background: #1a1d21; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                    <span style="font-size: 14px; color: white;"><?= strtoupper(substr($user_data['First_Name'], 0, 1)); ?></span>
                </div>
            </div>

            <form method="GET" class="search-container">
                <i class="fa fa-search"></i>
                <input type="text" name="search" class="search-input" placeholder="Search my reports..." value="<?= htmlspecialchars($search) ?>">
            </form>

            <div class="reports-table-container">
                <h2 style="margin-top: 0; font-size: 18px; margin-bottom: 20px;">My Reports</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            // Determine status class based on the 'Item_Status' enum value
                            $status_class = "status-" . strtolower($row['Item_Status']);
                        ?>
                        <tr>
                            <td><a href="view_report.php?id=<?= $row['Report_ID'] ?>" class="item-link"><?= htmlspecialchars($row['Item_Name']) ?></a></td>
                            <td style="color: var(--text-dim);"><?= htmlspecialchars($row['Item_Status']) ?></td>
                            <td><span class="badge <?= $status_class ?>"><?= htmlspecialchars($row['Item_Status']) ?></span></td>
                            <td style="color: var(--text-dim);"><?= date("M d", strtotime($row['Date_filed'])) ?></td>
                            <td>
                                <a href="edit_report.php?id=<?= $row['Report_ID'] ?>" class="action-edit">Edit</a>
                                <a href="delete_report.php?id=<?= $row['Report_ID'] ?>" class="action-delete" onclick="return confirm('Delete this report?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if(mysqli_num_rows($result) == 0): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 30px;">No reports found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>