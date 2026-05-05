<?php
/**
 * logout.php
 * Processes logout and clears session data.
 * Improved with relative pathing and flexible request handling.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for either a POST request from a form OR a GET request from a direct link
// This ensures the logout works whether you use a <form> or an <a> tag in your modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['confirm'])) {
    
    // Clear remember_me cookie if it exists
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    }

    // Fully clear the session array
    $_SESSION = [];

    // Destroy the session cookie in the browser
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params['path'], 
            $params['domain'],
            $params['secure'], 
            $params['httponly']
        );
    }

    // Destroy the session on the server
    session_destroy();

    /**
     * PATH FIX:
     * Instead of "/balikgamit/login.php", we use a relative path.
     * Since logout.php is in your root folder, "login.php" is in the same place.
     */
    header("Location: login.php");
    exit();
}

// If accessed incorrectly, default to login page
header("Location: login.php");
exit();
?>