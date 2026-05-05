<?php
session_start();
require '../../config/db.php';

// 1. Receive the JWT credential from Google
$id_token = $_POST['credential'] ?? null;

if (!$id_token) {
    header('Location: ../../login.php?message=Authentication failed');
    exit();
}

// 2. Verify the token with Google API (Secure Method)
$url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
$payload = json_decode(@file_get_contents($url), true);

if (!$payload || isset($payload['error'])) {
    header('Location: ../../login.php?message=Invalid Google Session');
    exit();
}

// Extract data provided by Google
$google_id = $payload['sub'];
$email     = $payload['email'];
$fname     = $payload['given_name'];
$lname     = $payload['family_name'];

// 3. Database Check: See if this student exists
$sql = "SELECT User_ID, Role FROM User_Table WHERE google_id = ? OR Email_Address = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $google_id, $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {
    // FLOW: LOG IN
    // If they previously had a manual account, link their Google ID now
    if (empty($user['google_id'])) {
        $update = "UPDATE User_Table SET google_id = ? WHERE Email_Address = ?";
        $u_stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($u_stmt, "ss", $google_id, $email);
        mysqli_stmt_execute($u_stmt);
    }
    
    $_SESSION['user_id'] = $user['User_ID'];
    $_SESSION['role'] = $user['Role'];
    header('Location: ../../student/home.php');
} else {
    // FLOW: AUTOMATIC SIGNUP
    // Generate a username based on their name
    $username = strtolower(preg_replace('/[^a-z0-9]/', '', $fname)) . rand(100, 999);
    
    $insert = "INSERT INTO User_Table (google_id, Username, First_Name, Last_Name, Email_Address, Role) 
               VALUES (?, ?, ?, ?, ?, 'Student')";
    $i_stmt = mysqli_prepare($conn, $insert);
    mysqli_stmt_bind_param($i_stmt, "sssss", $google_id, $username, $fname, $lname, $email);
    
    if (mysqli_stmt_execute($i_stmt)) {
        $_SESSION['user_id'] = mysqli_insert_id($conn);
        $_SESSION['role'] = 'Student';
        header('Location: ../../student/home.php?success=1');
    } else {
        header('Location: ../../login.php?message=Failed to create Google account');
    }
}
exit();