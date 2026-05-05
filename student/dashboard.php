<?php 
require '../config/db.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();s
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BalikGamit</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0a0a0a; color: white;">
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>
        
        <div class="main-content" style="display: flex; flex-direction: column;">
            <div class="content-body" style="padding-top: 20px;">
                <h1>Welcome to BalikGamit</h1>
                <p style="color: #888;">Select an option from the sidebar to manage lost and found items.</p>
                <hr style="border: 0; border-top: 1px solid #333; margin: 20px 0;">
                
                <div style="border: 2px dashed #333; padding: 40px; text-align: center; border-radius: 8px;">
                    <p style="color: #555;">Dashboard content coming soon...</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once '../logout-modal.php'; ?>
</body>
</html>