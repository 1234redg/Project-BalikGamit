<?php
require '../../config/db.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../login.php");
        exit();
    }

    mysqli_begin_transaction($conn);
    try {
        $image_path = null;
        if (isset($_FILES['item_photo']) && $_FILES['item_photo']['error'] == 0) {
            // Note: Adjusting path to point to the correct uploads folder relative to this file
            $target_dir = "../../uploads/";
            if (!is_dir($target_dir)) { 
                mkdir($target_dir, 0777, true); 
            }
            $filename = time() . "_" . basename($_FILES["item_photo"]["name"]);
            $target_file = $target_dir . $filename;
            
            if (move_uploaded_file($_FILES["item_photo"]["tmp_name"], $target_file)) {
                // Save the path relative to the root for database consistency
                $image_path = "uploads/" . $filename;
            }
        }

        // 1. Insert into item_table[cite: 1]
        $item_sql = "INSERT INTO item_table (Item_Name, Item_Status, Item_Description, Category_ID, Item_Image) VALUES (?, ?, ?, ?, ?)";
        $item_stmt = mysqli_prepare($conn, $item_sql);
        mysqli_stmt_bind_param($item_stmt, "sssis", $_POST['name'], $_POST['status'], $_POST['desc'], $_POST['cat_id'], $image_path);
        mysqli_stmt_execute($item_stmt);
        $new_item_id = mysqli_insert_id($conn);

        // 2. Insert into publication_table[cite: 1]
        $pub_sql = "INSERT INTO publication_table (User_ID, Item_ID, Date_Filed, Location, Claim_Status_ID) VALUES (?, ?, ?, ?, ?)";
        $pub_stmt = mysqli_prepare($conn, $pub_sql);
        $status_id = 1; // Default to 'pending'[cite: 1]
        mysqli_stmt_bind_param($pub_stmt, "iissi", $_SESSION['user_id'], $new_item_id, $_POST['date'], $_POST['loc'], $status_id);
        mysqli_stmt_execute($pub_stmt);

        mysqli_commit($conn);
        
        // Redirect back with success message
        $_SESSION['msg'] = "success";
        header("Location: ../../student/report.php"); // Adjust this to your actual form page name
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['msg'] = "error";
        $_SESSION['error_details'] = $e->getMessage();
        header("Location: ../../student/dashboard.php");
        exit();
    }
} else {
    header("Location: ../../student/dashboard.php");
    exit();
}
?>