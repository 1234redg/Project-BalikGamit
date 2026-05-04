<?php
/**
 * actions/auth/login_action.php
 * This file handles the background processing for user authentication.
 */

// 1. Database Connection
// Path: Moving up two levels from actions/auth/ to root, then into config/
require_once '../../config/db.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Check if the form was actually submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize input to prevent basic injection (though using prepared statements below)
    $identifier = trim($_POST['identifier']);
    $pass_input = trim($_POST['pass']);

    // Validate that fields are not empty
    if (empty($identifier) || empty($pass_input)) {
        header("Location: ../../login.php?error=empty");
        exit();
    }

    // ============================================
    // RECAPTCHA v2 VERIFICATION
    // ============================================
    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        header("Location: ../../login.php?error=recaptcha");
        exit();
    }

    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $recaptchaSecretKey = '6LeEo9gsAAAAAOhzmpGCh0BT3HIaCKhpLxx3_rZ_'; // Your Secret Key
    
    // Send the response to Google to verify it
    $verificationUrl = 'https://www.google.com/recaptcha/api/siteverify';
    
    $recaptchaData = [
        'secret' => $recaptchaSecretKey,
        'response' => $recaptchaResponse
    ];
    
    // Use PHP's stream context to make a POST request
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($recaptchaData)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = @file_get_contents($verificationUrl, false, $context);
    
    // If Google doesn't respond, fail
    if ($response === false) {
        header("Location: ../../login.php?error=recaptcha");
        exit();
    }
    
    // Decode Google's JSON response
    $responseData = json_decode($response, true);
    
    // Check: success must be true (v2 doesn't have a score)
    if (!isset($responseData['success']) || !$responseData['success']) {
        header("Location: ../../login.php?error=recaptcha");
        exit();
    }

    // 3. Query the User_Table
    // Based on BalikGamit project requirements for email or username login
    $sql = "SELECT * FROM User_Table WHERE Username = ? OR Email_Address = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        // 4. Verify Password
        if ($user && password_verify($pass_input, $user['Password'])) {
            
            // Set Session variables for the BalikGamit system[cite: 1]
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['username'] = $user['Username'];

            // 5. "Remember Me" Cookie Logic[cite: 1]
            if (isset($_POST['remember'])) {
                $token = bin2hex(random_bytes(16));
                $token_hash = password_hash($token, PASSWORD_DEFAULT);
                $expiry = date('Y-m-d H:i:s', time() + (86400 * 30));
                
                $token_sql = "INSERT INTO User_Tokens (User_ID, Token_Hash, Expiry) VALUES (?, ?, ?)";
                $t_stmt = mysqli_prepare($conn, $token_sql);
                if ($t_stmt) {
                    mysqli_stmt_bind_param($t_stmt, "iss", $user['User_ID'], $token_hash, $expiry);
                    mysqli_stmt_execute($t_stmt);
                    // Set cookie for 30 days
                    setcookie('remember_me', $user['User_ID'] . ':' . $token, time() + (86400 * 30), "/", "", false, true);
                }
            }

            // 6. Successful Login: Redirect to the student dashboard
            // Path: From actions/auth/ to root, then into student/
            header("Location: ../../student/dashboard.php");
            exit();

        } else {
            // Invalid credentials: Send back to Login with error flag[cite: 1]
            header("Location: ../../login.php?error=invalid");
            exit();
        }
    } else {
        // Database/Statement failure
        header("Location: ../../login.php?error=system");
        exit();
    }

} else {
    // If someone tries to access this file directly via URL without POST
    header("Location: ../../login.php");
    exit();
}
?>