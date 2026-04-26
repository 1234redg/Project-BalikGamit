<?php 
require 'includes/db.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CAPTURE FILTER/SORT/SEARCH PARAMETERS
$search_query   = $_GET['search'] ?? '';          // Text search
$filter_status  = $_GET['status'] ?? '';          // 'Found' or 'Lost'
$filter_cat     = $_GET['category'] ?? '';        // Specific Category Name
$date_prio      = $_GET['date_order'] ?? 'DESC';  // 'ASC' or 'DESC'

// 2. BUILD THE WHERE CLAUSE
$where_clauses = [];

// Search Logic (Weakpoint note: this only checks Item_Name)
if (!empty($search_query)) {
    $escaped_search = mysqli_real_escape_string($conn, $search_query);
    $where_clauses[] = "i.Item_Name LIKE '%$escaped_search%'";
}

if (!empty($filter_status)) {
    $where_clauses[] = "i.Item_Status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}

if (!empty($filter_cat)) {
    $where_clauses[] = "c.Category = '" . mysqli_real_escape_string($conn, $filter_cat) . "'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(' AND ', $where_clauses);
}

// 3. BUILD THE ORDER BY CLAUSE
$date_dir = ($date_prio === 'ASC') ? 'ASC' : 'DESC';
$order_sql = " ORDER BY p.Date_Filed $date_dir";

// 4. MAIN QUERY
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
          JOIN status_table s ON p.Claim_Status_ID = s.Claim_Status_ID" 
          . $where_sql 
          . $order_sql;

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reported Items - BalikGamit</title>
    <style>
        .item-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .item-card { background: #1a1a1a; border: 1px solid #333; border-radius: 8px; overflow: hidden; transition: transform 0.2s; }
        .item-card:hover { transform: translateY(-5px); }
        .item-image { width: 100%; height: 200px; object-fit: cover; background: #252525; }
        .item-info { padding: 15px; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .status-found { background: #28a745; color: white; }
        .status-lost { background: #dc3545; color: white; }
        .claim-status { color: #888; font-size: 13px; margin-top: 10px; border-top: 1px solid #333; padding-top: 10px; }
        
        .filter-container { background: #111; padding: 15px; border-radius: 8px; border: 1px solid #333; margin-bottom: 20px; }
        .filter-container input, .filter-container select, .filter-container button { 
            background: #222; color: white; border: 1px solid #444; padding: 8px; border-radius: 4px; margin-right: 10px; 
        }
        .filter-container button { background: #007bff; border: none; padding: 8px 20px; cursor: pointer; font-weight: bold; }
        .search-input { width: 250px; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #0a0a0a; color: white;">
    <div class="app-container">
        <?php include_once 'includes/sidebar.php'; ?>
        
        <div class="main-content" style="display: flex; flex-direction: column;">
            <?php include 'includes/nav_master.php'; ?>

            <div class="content-body" style="padding: 20px;">
                <h2>Reported Items</h2>
                
                <div class="filter-container">
                    <form method="GET">
                        <input type="text" name="search" class="search-input" placeholder="Search item name..." value="<?= htmlspecialchars($search_query) ?>">

                        <select name="status">
                            <option value="">All Status</option>
                            <option value="Found" <?= ($filter_status == 'Found') ? 'selected' : '' ?>>Found</option>
                            <option value="Lost" <?= ($filter_status == 'Lost') ? 'selected' : '' ?>>Lost</option>
                        </select>

                        <select name="category">
                            <option value="">All Categories</option>
                            <option value="Electronics" <?= ($filter_cat == 'Electronics') ? 'selected' : '' ?>>Electronics</option>
                            <option value="Personal Belongings" <?= ($filter_cat == 'Personal Belongings') ? 'selected' : '' ?>>Personal Belongings</option>
                            <option value="Accessories" <?= ($filter_cat == 'Accessories') ? 'selected' : '' ?>>Accessories</option>
                        </select>

                        <select name="date_order">
                            <option value="DESC" <?= ($date_prio == 'DESC') ? 'selected' : '' ?>>Newest First</option>
                            <option value="ASC" <?= ($date_prio == 'ASC') ? 'selected' : '' ?>>Oldest First</option>
                        </select>

                        <button type="submit">Search</button>
                        <a href="reported-items.php" style="color: #666; font-size: 13px; text-decoration: none; margin-left: 10px;">Reset</a>
                    </form>
                </div>

                <div class="item-grid">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <div class="item-card">
                                <?php if (!empty($row['Item_Image'])): ?>
                                    <img src="<?= htmlspecialchars($row['Item_Image']); ?>" class="item-image" alt="Item Photo">
                                <?php else: ?>
                                    <div class="item-image" style="display: flex; align-items: center; justify-content: center; color: #444;">No Image Available</div>
                                <?php endif; ?>
                                
                                <div class="item-info">
                                    <span class="status-badge <?= ($row['Item_Status'] == 'Found') ? 'status-found' : 'status-lost'; ?>">
                                        <?= htmlspecialchars($row['Item_Status']); ?>
                                    </span>
                                    <h3 style="margin: 5px 0;"><?= htmlspecialchars($row['Item_Name']); ?></h3>
                                    <p style="font-size: 13px; color: #007bff; margin-bottom: 10px;"><?= htmlspecialchars($row['Category']); ?></p>
                                    
                                    <div style="font-size: 13px; color: #888;">
                                        <p style="margin: 5px 0;">📍 <?= htmlspecialchars($row['Location']); ?></p>
                                        <p style="margin: 5px 0;">📅 <?= date("M d, Y", strtotime($row['Date_Filed'])); ?></p>
                                        <p style="margin: 5px 0;">👤 <?= htmlspecialchars($row['Reporter']); ?></p>
                                    </div>

                                    <div class="claim-status">
                                        Status: <strong><?= ucfirst(htmlspecialchars($row['Claim_Status'])); ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: #888; font-style: italic;">No results found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>