<?php 
require '../config/db.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
// Check for messages from the action file
if (isset($_SESSION['msg'])) {
    if ($_SESSION['msg'] == "success") {
        $message = "<p style='color: #28a745; background: rgba(40, 167, 69, 0.1); padding: 10px; border-radius: 4px;'>Report submitted successfully!</p>";
    } elseif ($_SESSION['msg'] == "error") {
        $details = $_SESSION['error_details'] ?? 'Unknown error';
        $message = "<p style='color: #dc3545; background: rgba(220, 53, 69, 0.1); padding: 10px; border-radius: 4px;'>Failed: $details</p>";
    }
    unset($_SESSION['msg']);
    unset($_SESSION['error_details']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Item - BalikGamit</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0a0a0a; color: white;">
    <div class="app-container" style="display: flex;">
        <?php include_once '../includes/sidebar.php'; ?>
        
        <div class="main-content" style="flex: 1; display: flex; flex-direction: column;">

            <div class="content-body" style="padding: 20px;">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <h2>Please <a href="login.php" style="color: #007bff;">login</a> to report an item.</h2>
                <?php else: ?>
                    <h2>Report an Item</h2>
                    <p style="color: #888;">Fill in the details to list a lost or found item.</p>
                    <hr style="border: 0; border-top: 1px solid #333; margin: 20px 0;">

                    <?php echo $message; ?>

                    <!-- UPDATED ACTION PATH -->
                    <form action="../actions/report/add_report.php" method="POST" enctype="multipart/form-data" style="max-width: 500px;">
                        <div style="margin-bottom: 20px;">
                            <label>Status:</label><br>
                            <input type="radio" name="status" value="Lost" checked> Lost Item
                            <input type="radio" name="status" value="Found" style="margin-left: 20px;"> Found Item
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label>Item Name:</label><br>
                            <input type="text" name="name" placeholder="e.g. Black Wallet" required 
                                   style="width: 100%; padding: 10px; background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 4px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label>Category:</label><br>
                            <select name="cat_id" required 
                                    style="width: 100%; padding: 10px; background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 4px;">
                                <option value="">-- Select Category --</option>
                                <?php
                                $catResult = mysqli_query($conn, "SELECT * FROM category_table");
                                while ($row = mysqli_fetch_assoc($catResult)) {
                                    echo "<option value='{$row['Category_ID']}'>{$row['Category']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label>Description:</label><br>
                            <textarea name="desc" placeholder="Describe the item..." 
                                      style="width: 100%; height: 100px; padding: 10px; background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 4px;"></textarea>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label>Location Last Seen:</label><br>
                            <input type="text" name="loc" placeholder="Specific building or room" required 
                                   style="width: 100%; padding: 10px; background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 4px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label>Date:</label><br>
                            <input type="date" name="date" required 
                                   style="width: 100%; padding: 10px; background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 4px;">
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label>Upload Photo (Optional):</label><br>
                            <input type="file" name="item_photo" style="color: #888;">
                        </div>

                        <button type="submit" 
                                style="background: #007bff; color: white; border: none; padding: 12px 25px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                            Submit Report
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>