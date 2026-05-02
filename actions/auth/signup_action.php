<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../signup.php');
    exit();
}

$fname = trim($_POST['fname'] ?? '');
$lname = trim($_POST['lname'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = trim($_POST['pass'] ?? '');
$conf_pass = trim($_POST['conf_pass'] ?? '');
$role = trim($_POST['role'] ?? 'Student');
$phone = trim($_POST['phone'] ?? '');

function generate_username($first, $last) {
    $base = strtolower(preg_replace('/[^a-z0-9]/', '', $first . $last));
    if (empty($base)) {
        $base = 'user';
    }
    return $base;
}

function redirect_with_message($message, $fname = '', $lname = '', $email = '') {
    $params = http_build_query([
        'message' => $message,
        'fname' => $fname,
        'lname' => $lname,
        'email' => $email,
    ]);
    header('Location: ../../signup.php?' . $params);
    exit();
}

if (empty($fname) || empty($lname) || empty($email) || empty($pass) || empty($conf_pass)) {
    redirect_with_message('Please fill in all required fields.', $fname, $lname, $email);
}

if ($pass !== $conf_pass) {
    redirect_with_message('Passwords do not match.', $fname, $lname, $email);
}

$emailSql = 'SELECT Email_Address FROM User_Table WHERE Email_Address = ?';
$emailStmt = mysqli_prepare($conn, $emailSql);
if ($emailStmt) {
    mysqli_stmt_bind_param($emailStmt, 's', $email);
    mysqli_stmt_execute($emailStmt);
    mysqli_stmt_store_result($emailStmt);
    if (mysqli_stmt_num_rows($emailStmt) > 0) {
        redirect_with_message('This email address is already registered.', $fname, $lname, $email);
    }
}

$username_base = generate_username($fname, $lname);
$username = $username_base;
$counter = 1;

while (true) {
    $checkSql = 'SELECT Username FROM User_Table WHERE Username = ?';
    $checkStmt = mysqli_prepare($conn, $checkSql);
    if (!$checkStmt) {
        redirect_with_message('Unable to validate username availability.', $fname, $lname, $email);
    }
    mysqli_stmt_bind_param($checkStmt, 's', $username);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);

    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        $username = $username_base . $counter;
        $counter++;
    } else {
        break;
    }
}

$hashed_password = password_hash($pass, PASSWORD_DEFAULT);
$sql = 'INSERT INTO User_Table (Username, Password, First_Name, Last_Name, Role, Email_Address, Contact_Number) VALUES (?, ?, ?, ?, ?, ?, ?)';
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    redirect_with_message('Database error while preparing account creation.', $fname, $lname, $email);
}

mysqli_stmt_bind_param($stmt, 'sssssss', $username, $hashed_password, $fname, $lname, $role, $email, $phone);

if (mysqli_stmt_execute($stmt)) {
    header('Location: ../../signup.php?success=1');
    exit();
} else {
    redirect_with_message('Error creating account: ' . mysqli_error($conn), $fname, $lname, $email);
}
