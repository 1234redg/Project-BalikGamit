<?php
session_start();
// Include your database connection here
// include_once '../../config/db_connection.php'; 

// For demonstration based on your campus_lost_found.sql structure
$host = "localhost";
$user = "root";
$pass = "";
$db   = "campus_lost_found";
$port = 3307; // Matches your SQL dump port

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        header("Location: ../../forgot-password.php?status=empty");
        exit();
    }

    // Check if the email exists in user_table[cite: 1]
    $stmt = $conn->prepare("SELECT User_ID, First_Name FROM user_table WHERE Email_Address = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $firstName = $user['First_Name'];

        /* 
           LOGIC FOR SENDING EMAIL 
           In a production environment, you would generate a unique token, 
           save it to a 'password_resets' table, and email it to the user.
        */
        
        $to = $email;
        $subject = "Password Reset - BalikGamit";
        $message = "Hi " . $firstName . ",\n\nYou requested a password reset. Click the link below to reset your password:\n";
        $message .= "http://localhost/balikgamit/reset-password.php?email=" . urlencode($email);
        $headers = "From: no-reply@balikgamit.com";

        // mail($to, $subject, $message, $headers); // Uncomment on a live server

        // Redirect with success to trigger the SweetAlert2 popup
        header("Location: ../../forgot-password.php?status=success");
        exit();
    } else {
        // Email not found in database[cite: 1]
        header("Location: ../../forgot-password.php?status=notfound");
        exit();
    }

    $stmt->close();
}
$conn->close();
?>