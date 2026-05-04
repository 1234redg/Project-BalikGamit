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

        /* Card Interaction */
        .item-card { 
            background: #1a1a1a; 
            border: 1px solid #333; 
            border-radius: 8px; 
            overflow: hidden; 
            cursor: pointer; 
            transition: transform 0.2s, border-color 0.2s;
        }
        .item-card:hover { transform: translateY(-3px); border-color: #444; }

        .item-image-wrapper { width: 100%; height: 180px; background: #222; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .item-image-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .item-image-placeholder { color: #444; font-size: 0.8rem; font-weight: bold; letter-spacing: 1px; }

        .item-info { padding: 15px; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .status-Found { background: #28a745; color: white; }
        .status-Lost { background: #dc3545; color: white; }

        /* MODAL STYLING */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); z-index: 1000; justify-content: center; align-items: center;
        }
        .modal-window {
            background: #111; width: 90%; max-width: 500px; border-radius: 12px;
            padding: 30px; border: 1px solid #222; position: relative;
        }
        .modal-image { width: 100%; height: 200px; border-radius: 8px; object-fit: cover; margin-bottom: 20px; background: #222; }
        .modal-title { font-size: 24px; margin: 0; font-weight: 500; }
        .modal-subtitle { color: #666; font-size: 14px; margin-bottom: 25px; }

        .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #222; font-size: 15px; }
        .detail-label { color: #888; }
        .detail-value { color: #eee; text-align: right; max-width: 60%; }

        .modal-actions { display: flex; gap: 15px; margin-top: 30px; }
        .btn { flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #333; cursor: pointer; font-weight: bold; font-size: 14px; transition: 0.2s; }
        .btn-close { background: transparent; color: white; }
        .btn-claim { background: #0d1b2a; color: #3498db; border-color: #1f3a5f; }
        .btn:hover { opacity: 0.8; }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>

        <div class="main-content">
            <h2 style="margin-top: 20px;">Reported Items</h2>
            <div class="item-grid">
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="item-card" onclick="openModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                        <div class="item-image-wrapper">
                            <?php 
                                $image_path = $row['Item_Image'];
                                $full_path = $_SERVER['DOCUMENT_ROOT'] . "/balikgamit/" . $image_path;
                                if (!empty($image_path) && file_exists($full_path)): 
                            ?>
                                <img src="/balikgamit/<?php echo htmlspecialchars($image_path); ?>" alt="Item Image">
                            <?php else: ?>
                                <div class="item-image-placeholder">No Image Available</div>
                            <?php endif; ?>
                        </div>
                        <div class="item-info">
                            <span class="status-badge status-<?php echo htmlspecialchars($row['Item_Status']); ?>"><?php echo htmlspecialchars($row['Item_Status']); ?></span>
                            <h3 style="margin: 0;"><?php echo htmlspecialchars($row['Item_Name']); ?></h3>
                            <p style="color: #888; font-size: 0.85rem;"><?php echo htmlspecialchars($row['Location']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- MODAL WINDOW -->
    <div id="itemModal" class="modal-overlay" onclick="closeModal(event)">
        <div class="modal-window" onclick="event.stopPropagation()">
            <img id="modalImg" class="modal-image" src="" alt="Item">
            <h2 id="modalName" class="modal-title">Item Name</h2>
            <p class="modal-subtitle">Item Details</p>

            <div class="detail-row"><span class="detail-label">Category</span><span id="modalCat" class="detail-value"></span></div>
            <div class="detail-row"><span class="detail-label">Location</span><span id="modalLoc" class="detail-value"></span></div>
            <div class="detail-row"><span class="detail-label">Status</span><span id="modalStat" class="detail-value"></span></div>
            <div class="detail-row"><span class="detail-label">Date</span><span id="modalDate" class="detail-value"></span></div>
            <div class="detail-row" style="border:none;"><span class="detail-label">Description</span><span id="modalDesc" class="detail-value"></span></div>

            <div class="modal-actions">
                <button class="btn btn-close" onclick="document.getElementById('itemModal').style.display='none'">Close</button>
                <button class="btn btn-claim">I own this</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(item) {
            const modal = document.getElementById('itemModal');
            document.getElementById('modalName').innerText = item.Item_Name;
            document.getElementById('modalCat').innerText = item.Category || 'N/A';
            document.getElementById('modalLoc').innerText = item.Location;
            document.getElementById('modalStat').innerText = item.Item_Status;
            document.getElementById('modalDate').innerText = new Date(item.Date_Filed).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            document.getElementById('modalDesc').innerText = item.Item_Description;

            // Handle Image
            const img = document.getElementById('modalImg');
            if (item.Item_Image) {
                img.src = '/balikgamit/' + item.Item_Image;
                img.style.display = 'block';
            } else {
                img.style.display = 'none';
            }

            modal.style.display = 'flex';
        }

        function closeModal(e) {
            document.getElementById('itemModal').style.display = 'none';
        }
    </script>
</body>
</html>