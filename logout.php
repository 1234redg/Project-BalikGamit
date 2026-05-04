<?php
/**
 * logout.php
 * Processes logout confirmation from the modal.
 * This file should NOT be visited directly — it receives a POST from logout-modal.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only act on POST with the hidden confirm field
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    // Clear remember_me cookie if it exists
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    }

    // Destroy the session
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();

    // Redirect to login
    header("Location: /balikgamit/login.php");
    exit();
}

// If someone hits this URL directly or without the POST field, send them back
header("Location: /balikgamit/login.php");
exit();
?>