<?php
require '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$query = "SELECT 
            p.Publication_ID, 
            i.Item_Name, 
            i.Item_Status, 
            i.Item_Description, 
            i.Item_Image,
            c.Category, 
            p.Location, 
            p.Date_Filed, 
            u.Username AS Reporter,
            u.First_Name,
            u.Last_Name
          FROM publication_table p
          LEFT JOIN item_table i ON p.Item_ID = i.Item_ID
          LEFT JOIN category_table c ON i.Category_ID = c.Category_ID
          LEFT JOIN user_table u ON p.User_ID = u.User_ID
          ORDER BY p.Date_Filed DESC";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reported Items - BalikGamit</title>
    <style>
        body { margin: 0; padding: 0; background-color: #0a0a0a; color: white; font-family: sans-serif; }
        .app-container { display: flex; }
        .main-content { flex: 1; padding: 20px; min-height: 100vh; background-color: #0a0a0a; }

        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .item-card { background: #1a1a1a; border: 1px solid #333; border-radius: 8px; overflow: hidden; }

        .item-image-wrapper { width: 100%; height: 180px; background: #252525; overflow: hidden; }
        .item-image-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .item-image-placeholder {
            width: 100%; height: 100%; display: flex;
            align-items: center; justify-content: center;
            color: #555; font-size: 0.85rem;
        }

        .item-info { padding: 15px; }

        .status-badge {
            display: inline-block; padding: 4px 8px; border-radius: 4px;
            font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px;
        }
        .status-Found { background: #28a745; color: white; }
        .status-Lost { background: #dc3545; color: white; }

        .item-name { font-size: 1.2rem; font-weight: bold; margin: 0 0 5px 0; }
        .item-desc { color: #aaa; font-size: 0.9rem; margin-bottom: 10px; }
        .meta-info { font-size: 0.85rem; color: #888; margin-top: 5px; }
        .meta-label { color: #555; }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include '../includes/nav_master.php'; ?>

            <h2 style="margin-top: 20px;">Reported Items</h2>

            <div class="item-grid">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <div class="item-card">
                            <div class="item-image-wrapper">
                                <?php if (!empty($row['Item_Image'])): ?>
                                    <img src="/balikgamit/<?php echo htmlspecialchars($row['Item_Image']); ?>" alt="Item Image">
                                <?php else: ?>
                                    <div class="item-image-placeholder">No Image Available</div>
                                <?php endif; ?>
                            </div>

                            <div class="item-info">
                                <span class="status-badge status-<?php echo htmlspecialchars($row['Item_Status']); ?>">
                                    <?php echo htmlspecialchars($row['Item_Status']); ?>
                                </span>

                                <h3 class="item-name"><?php echo htmlspecialchars($row['Item_Name']); ?></h3>
                                <p class="item-desc"><?php echo htmlspecialchars($row['Item_Description']); ?></p>

                                <div class="meta-info">
                                    <div><span class="meta-label">Category:</span> <?php echo htmlspecialchars($row['Category'] ?? 'N/A'); ?></div>
                                    <div><span class="meta-label">Location:</span> <?php echo htmlspecialchars($row['Location']); ?></div>
                                    <div><span class="meta-label">Date:</span> <?php echo date("M d, Y", strtotime($row['Date_Filed'])); ?></div>
                                    <div><span class="meta-label">Reported by:</span>
                                        <?php echo htmlspecialchars($row['First_Name'] . " " . $row['Last_Name']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#e74c3c;">No reported items found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>