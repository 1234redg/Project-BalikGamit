<?php
require '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Query only reports belonging to the current user
$query = "SELECT 
            p.Publication_ID, 
            i.Item_Name, 
            i.Item_Status, 
            p.Date_Filed,
            c.Category
          FROM publication_table p
          LEFT JOIN item_table i ON p.Item_ID = i.Item_ID
          LEFT JOIN category_table c ON i.Category_ID = c.Category_ID
          WHERE p.User_ID = ?
          ORDER BY p.Date_Filed DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - BalikGamit</title>
    <style>
        body { margin: 0; padding: 0; background-color: #0a0a0a; color: white; font-family: sans-serif; }
        .app-container { display: flex; }
        .main-content { flex: 1; padding: 40px; min-height: 100vh; background-color: #0a0a0a; }

        h2 { margin-bottom: 30px; font-weight: 500; font-size: 24px; }

        /* Table Styling to match your image */
        .reports-table {
            width: 100%;
            border-collapse: collapse;
            color: #e0e0e0;
        }

        .reports-table th {
            text-align: left;
            color: #888;
            font-weight: normal;
            padding: 12px 15px;
            border-bottom: 1px solid #222;
            font-size: 14px;
        }

        .reports-table td {
            padding: 15px;
            border-bottom: 1px solid #1a1a1a;
            font-size: 15px;
        }

        /* Status Badges */
        .status-pill {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 13px;
            display: inline-block;
        }
        .status-Lost { background: rgba(220, 53, 69, 0.15); color: #ff4d4d; }
        .status-Found { background: rgba(40, 167, 69, 0.15); color: #2ecc71; }
        .status-Pending { background: rgba(255, 193, 7, 0.1); color: #f39c12; }
        .status-Resolved { background: rgba(40, 167, 69, 0.2); color: #27ae60; }

        /* Action Links */
        .action-links a {
            text-decoration: none;
            margin-right: 10px;
            font-size: 14px;
        }
        .btn-edit { color: #3498db; }
        .btn-delete { color: #e74c3c; }
        .action-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>

        <div class="main-content">
            <h2>My Reports</h2>

            <table class="reports-table">
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
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Item_Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Category'] ?? 'Uncategorized'); ?></td>
                                <td>
                                    <span class="status-pill status-<?php echo htmlspecialchars($row['Item_Status']); ?>">
                                        <?php echo htmlspecialchars($row['Item_Status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date("M d", strtotime($row['Date_Filed'])); ?></td>
                                <td class="action-links">
                                    <a href="edit-report.php?id=<?php echo $row['Publication_ID']; ?>" class="btn-edit">Edit</a>
                                    <a href="delete-report.php?id=<?php echo $row['Publication_ID']; ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this report?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #555; padding: 40px;">
                                You haven't filed any reports yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>