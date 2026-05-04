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
$pass = $_POST['pass'] ?? '';
$conf_pass = $_POST['conf_pass'] ?? '';
$role = $_POST['role'] ?? 'Student';
$phone = trim($_POST['phone'] ?? '');

function generate_username($first, $last) {
    $base = strtolower(preg_replace('/[^a-z0-9]/', '', $first . $last));
    return empty($base) ? 'user' : $base;
}

function redirect_with_message($message, $fname = '', $lname = '', $email = '', $phone = '') {
    $params = http_build_query([
        'message' => $message, 'fname' => $fname, 'lname' => $lname, 'email' => $email, 'phone' => $phone
    ]);
    header('Location: ../../signup.php?' . $params);
    exit();
}

// 1. Validation
if (empty($fname) || empty($lname) || empty($email) || empty($pass) || empty($conf_pass)) {
    redirect_with_message('Please fill in all required fields.', $fname, $lname, $email, $phone);
}

// 2. reCAPTCHA
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
$secret = '6LeEo9gsAAAAAOhzmpGCh0BT3HIaCKhpLxx3_rZ_';
$verify = @file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$recaptchaResponse}");
$responseData = json_decode($verify);

if (!$responseData || !$responseData->success) {
    redirect_with_message('reCAPTCHA verification failed.', $fname, $lname, $email, $phone);
}

// 3. Password Check
if ($pass !== $conf_pass) {
    redirect_with_message('Passwords do not match.', $fname, $lname, $email, $phone);
}

// 4. Duplicate Check
$emailSql = 'SELECT Email_Address FROM User_Table WHERE Email_Address = ?';
$emailStmt = mysqli_prepare($conn, $emailSql);
mysqli_stmt_bind_param($emailStmt, 's', $email);
mysqli_stmt_execute($emailStmt);
mysqli_stmt_store_result($emailStmt);
if (mysqli_stmt_num_rows($emailStmt) > 0) {
    redirect_with_message('This email is already registered.', $fname, $lname, $email, $phone);
}

// 5. Generate Username & Insert
$username = generate_username($fname, $lname);
$hashed_password = password_hash($pass, PASSWORD_DEFAULT);
$sql = 'INSERT INTO User_Table (Username, Password, First_Name, Last_Name, Role, Email_Address, Contact_Number) VALUES (?, ?, ?, ?, ?, ?, ?)';
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sssssss', $username, $hashed_password, $fname, $lname, $role, $email, $phone);

if (mysqli_stmt_execute($stmt)) {
    header('Location: ../../signup.php?success=1');
    exit();
} else {
    redirect_with_message('Error creating account.', $fname, $lname, $email, $phone);
}
?>