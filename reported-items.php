<?php 
require 'includes/db.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch reported items with item details, categories, and reporter names
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
            s.Claim_Status
          FROM publication_table p
          JOIN item_table i ON p.Item_ID = i.Item_ID
          JOIN category_table c ON i.Category_ID = c.Category_ID
          JOIN user_table u ON p.User_ID = u.User_ID
          JOIN status_table s ON p.Claim_Status_ID = s.Claim_Status_ID
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
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .item-card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .item-card:hover {
            transform: translateY(-5px);
        }
        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #252525;
        }
        .item-info {
            padding: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .status-found { background: #28a745; color: white; }
        .status-lost { background: #dc3545; color: white; }
        .claim-status { color: #888; font-size: 13px; margin-top: 10px; border-top: 1px solid #333; padding-top: 10px; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #0a0a0a; color: white;">
    <div class="app-container">
        <?php include_once 'includes/sidebar.php'; ?>
        
        <div class="main-content" style="display: flex; flex-direction: column;">
            <?php include 'includes/nav_master.php'; ?>

            <div class="content-body" style="padding-top: 20px;">
                <h2>Reported Items</h2>
                <p style="color: #888;">Browse all items reported lost or found within the community.</p>
                <hr style="border: 0; border-top: 1px solid #333; margin: 20px 0;">

                <div class="item-grid">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <div class="item-card">
                                <?php if (!empty($row['Item_Image'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['Item_Image']); ?>" class="item-image" alt="Item Photo">
                                <?php else: ?>
                                    <div class="item-image" style="display: flex; align-items: center; justify-content: center; color: #444;">
                                        No Image Available
                                    </div>
                                <?php endif; ?>
                                
                                <div class="item-info">
                                    <span class="status-badge <?php echo ($row['Item_Status'] == 'Found') ? 'status-found' : 'status-lost'; ?>">
                                        <?php echo htmlspecialchars($row['Item_Status']); ?>
                                    </span>
                                    <h3 style="margin: 5px 0;"><?php echo htmlspecialchars($row['Item_Name']); ?></h3>
                                    <p style="font-size: 13px; color: #007bff; margin-bottom: 10px;"><?php echo htmlspecialchars($row['Category']); ?></p>
                                    
                                    <p style="font-size: 14px; color: #ccc; height: 40px; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($row['Item_Description']); ?>
                                    </p>
                                    
                                    <div style="font-size: 13px; color: #888;">
                                        <p style="margin: 5px 0;">📍 <?php echo htmlspecialchars($row['Location']); ?></p>
                                        <p style="margin: 5px 0;">📅 <?php echo date("M d, Y", strtotime($row['Date_Filed'])); ?></p>
                                        <p style="margin: 5px 0;">👤 Reported by: <?php echo htmlspecialchars($row['Reporter']); ?></p>
                                    </div>

                                    <div class="claim-status">
                                        Status: <strong><?php echo ucfirst(htmlspecialchars($row['Claim_Status'])); ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: #888; font-style: italic;">No items have been reported yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>