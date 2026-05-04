<?php
// nav_master.php
// NOTE: Does NOT require db.php — the parent file already loaded it.
// This file only outputs the nav HTML.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$firstName = "Guest";

if (isset($_SESSION['user_id']) && isset($conn)) {
    $u_id = $_SESSION['user_id'];
    $query = "SELECT First_Name FROM user_table WHERE User_ID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $u_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($user = mysqli_fetch_assoc($res)) {
        $firstName = htmlspecialchars($user['First_Name']);
    }
}
?>
<style>
    .dev-nav {
        background: #2c3e50;
        color: #fff;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        position: relative;
    }
    .dev-nav h4 { margin: 0 0 10px 0; color: #3498db; text-transform: uppercase; font-size: 12px; }
    .nav-group { margin-bottom: 15px; display: inline-block; vertical-align: top; margin-right: 30px; }
    .nav-group a { display: block; color: #ecf0f1; text-decoration: none; font-size: 14px; margin-bottom: 5px; }
    .nav-group a:hover { color: #3498db; text-decoration: underline; }
    .user-info {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 14px;
        color: #3498db;
        font-weight: bold;
    }
    /* Nav styling */
</style>

<nav class="dev-nav">
    <div class="user-info">Welcome, <?php echo $firstName; ?>!</div>

    <div class="nav-group">
        <h4>Main App</h4>
        <a href="/balikgamit/index.php">Dashboard (Feed)</a>
        <a href="/balikgamit/report-item.php">Post Item (Publish)</a>
        <a href="/balikgamit/settings.php">User Settings</a>
    </div>

    <div class="nav-group">
        <h4>Auth Logic</h4>
        <a href="/balikgamit/actions/auth/login_action.php">Login Page</a>
        <a href="/balikgamit/signup.php">Register Page</a>
        <a href="/balikgamit/auth/forgot-password.php">Forgot Password</a>
        <a href="/balikgamit/auth/change-password.php">Change Password</a>
        <a href="/balikgamit/auth/verify-email.php">Verify Email</a>
        <a href="#" onclick="openLogoutModal(); return false;" style="color: #e74c3c;">Logout</a>
    </div>

    <div class="nav-group">
        <h4>Temporary</h4>
        <a href="/balikgamit/includes/view-database-temporary.php">View database</a>
    </div>
</nav>

<?php
// Include the shared logout modal (provides openLogoutModal/closeLogoutModal)
include_once __DIR__ . '/../logout-modal.php';
?>