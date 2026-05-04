<?php
require '../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;

if ($report_id === 0) {
    $_SESSION['msg'] = 'error';
    $_SESSION['error_details'] = 'Invalid report ID.';
    header("Location: ../../student/my_reports.php");
    exit();
}

// Verify ownership and get item_id + image
$check = mysqli_prepare($conn,
    "SELECT r.Item_ID, i.Item_Image 
     FROM reports_table r 
     JOIN item_table i ON r.Item_ID = i.Item_ID 
     WHERE r.Report_ID = ? AND r.User_ID = ?"
);
mysqli_stmt_bind_param($check, "ii", $report_id, $user_id);
mysqli_stmt_execute($check);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($check));

if (!$row) {
    $_SESSION['msg'] = 'error';
    $_SESSION['error_details'] = 'Report not found or access denied.';
    header("Location: ../../student/my_reports.php");
    exit();
}

$item_id  = $row['Item_ID'];
$imgFile  = $row['Item_Image'];

mysqli_begin_transaction($conn);

try {
    // Delete from claims first (FK constraint)
    $delClaims = mysqli_prepare($conn, "DELETE FROM claims_table WHERE Report_ID = ?");
    mysqli_stmt_bind_param($delClaims, "i", $report_id);
    mysqli_stmt_execute($delClaims);

    // Delete from reports_table
    $delReport = mysqli_prepare($conn, "DELETE FROM reports_table WHERE Report_ID = ?");
    mysqli_stmt_bind_param($delReport, "i", $report_id);
    mysqli_stmt_execute($delReport);

    // Delete from item_table
    $delItem = mysqli_prepare($conn, "DELETE FROM item_table WHERE Item_ID = ?");
    mysqli_stmt_bind_param($delItem, "i", $item_id);
    mysqli_stmt_execute($delItem);

    mysqli_commit($conn);

    // Remove image file if it exists
    if (!empty($imgFile)) {
        $imgPath = '../../assets/images/' . $imgFile;
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
    }

    $_SESSION['msg'] = 'deleted';

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['msg']           = 'error';
    $_SESSION['error_details'] = $e->getMessage();
}

header("Location: ../../student/my_reports.php");
exit();