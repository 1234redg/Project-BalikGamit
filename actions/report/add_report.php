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
            $target_dir = "../../uploads/";
            if (!is_dir($target_dir)) { 
                mkdir($target_dir, 0777, true); 
            }
            $filename = time() . "_" . basename($_FILES["item_photo"]["name"]);
            $target_file = $target_dir . $filename;
            
            if (move_uploaded_file($_FILES["item_photo"]["tmp_name"], $target_file)) {
                $image_path = "uploads/" . $filename;
            }
        }

        // 1. Insert into item_table
        $item_sql = "INSERT INTO item_table (Item_Name, Item_Status, Item_Description, Category_ID, Item_Image) VALUES (?, ?, ?, ?, ?)";
        $item_stmt = mysqli_prepare($conn, $item_sql);
        mysqli_stmt_bind_param($item_stmt, "sssis", $_POST['name'], $_POST['status'], $_POST['desc'], $_POST['cat_id'], $image_path);
        mysqli_stmt_execute($item_stmt);
        $new_item_id = mysqli_insert_id($conn);

        // 2. Insert into reports_table (Updated to match your actual columns)
        // Fixed: Removed Claim_Status_ID and used 'Date_filed' to match database screenshot
        $report_sql = "INSERT INTO reports_table (User_ID, Item_ID, Date_filed, Location) VALUES (?, ?, ?, ?)";
        $report_stmt = mysqli_prepare($conn, $report_sql);
        
        // Corrected bind_param: 4 placeholders (?, ?, ?, ?) = "iiss"
        mysqli_stmt_bind_param($report_stmt, "iiss", $_SESSION['user_id'], $new_item_id, $_POST['date'], $_POST['loc']);
        mysqli_stmt_execute($report_stmt);

        mysqli_commit($conn);
        
        // Redirect back with success message[cite: 2]
        $_SESSION['msg'] = "success";
        header("Location: ../../student/report_item.php"); 
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['msg'] = "error";
        $_SESSION['error_details'] = $e->getMessage();
        header("Location: ../../student/report_item.php");
        exit();
    }
} else {
    header("Location: ../../student/report_item.php");
    exit();
}
?>