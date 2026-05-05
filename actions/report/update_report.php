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
$item_id   = isset($_POST['item_id'])   ? intval($_POST['item_id'])   : 0;

if ($report_id === 0 || $item_id === 0) {
    $_SESSION['msg'] = 'error';
    $_SESSION['error_details'] = 'Invalid report or item ID.';
    header("Location: ../../student/my_reports.php");
    exit();
}

// Verify this report belongs to the logged-in user[cite: 4]
$check = mysqli_prepare($conn, "SELECT r.Report_ID FROM reports_table r WHERE r.Report_ID = ? AND r.User_ID = ?");
mysqli_stmt_bind_param($check, "ii", $report_id, $user_id);
mysqli_stmt_execute($check);
$checkResult = mysqli_stmt_get_result($check);

if (mysqli_num_rows($checkResult) === 0) {
    $_SESSION['msg'] = 'error';
    $_SESSION['error_details'] = 'Access denied.';
    header("Location: ../../student/my_reports.php");
    exit();
}

$name   = trim($_POST['name']   ?? '');
$status = trim($_POST['status'] ?? '');
$cat_id = intval($_POST['cat_id'] ?? 0);
$loc    = trim($_POST['loc']    ?? '');
$date   = trim($_POST['date']   ?? '');
$desc   = trim($_POST['desc']   ?? '');

if ($name === '' || $status === '' || $cat_id === 0 || $loc === '' || $date === '') {
    $_SESSION['msg'] = 'error';
    $_SESSION['error_details'] = 'Please fill in all required fields.';
    header("Location: ../../student/my_reports.php");
    exit();
}

// Handle optional photo upload[cite: 4]
$newImage = null;
if (!empty($_FILES['item_photo']['name'])) {
    // FIX 1: Change physical upload directory to root uploads folder[cite: 3]
    $uploadDir  = '../../uploads/'; 
    $ext        = strtolower(pathinfo($_FILES['item_photo']['name'], PATHINFO_EXTENSION));
    $allowed    = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

    if (!in_array($ext, $allowed)) {
        $_SESSION['msg'] = 'error';
        $_SESSION['error_details'] = 'Invalid image format.';
        header("Location: ../../student/my_reports.php");
        exit();
    }

    $newFilename = time() . '_' . basename($_FILES['item_photo']['name']);
    if (move_uploaded_file($_FILES['item_photo']['tmp_name'], $uploadDir . $newFilename)) {
        // FIX 2: Prepend 'uploads/' to the filename for database storage[cite: 3]
        $newImage = 'uploads/' . $newFilename;
    }
}

mysqli_begin_transaction($conn);

try {
    // Update item_table[cite: 4]
    if ($newImage) {
        $itemStmt = mysqli_prepare($conn,
            "UPDATE item_table SET Item_Name=?, Item_Status=?, Category_ID=?, Item_Description=?, Item_Image=? WHERE Item_ID=?"
        );
        mysqli_stmt_bind_param($itemStmt, "ssissi", $name, $status, $cat_id, $desc, $newImage, $item_id);
    } else {
        $itemStmt = mysqli_prepare($conn,
            "UPDATE item_table SET Item_Name=?, Item_Status=?, Category_ID=?, Item_Description=? WHERE Item_ID=?"
        );
        mysqli_stmt_bind_param($itemStmt, "ssisi", $name, $status, $cat_id, $desc, $item_id);
    }
    mysqli_stmt_execute($itemStmt);

    // Update reports_table[cite: 4]
    $repStmt = mysqli_prepare($conn,
        "UPDATE reports_table SET Location=?, Date_filed=? WHERE Report_ID=?"
    );
    mysqli_stmt_bind_param($repStmt, "ssi", $loc, $date, $report_id);
    mysqli_stmt_execute($repStmt);

    mysqli_commit($conn);
    $_SESSION['msg'] = 'updated';

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['msg']           = 'error';
    $_SESSION['error_details'] = $e->getMessage();
}

header("Location: ../../student/my_reports.php");
exit();