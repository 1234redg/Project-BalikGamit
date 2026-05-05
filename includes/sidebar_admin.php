<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);

// Ensure $conn is available from your config
$firstName = "Admin";
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
<div class="sidebar">
    <div class="sidebar-brand">
        <a href="dashboard.php" class="logo-link">
            <img src="../assets/images/BalikGamitLogo2.png" alt="BalikGamit Logo" class="sidebar-logo">
        </a>
    </div>

    <div class="sidebar-section-label">ADMIN PANEL</div>
    <ul class="sidebar-menu">
        <!-- Admin Dashboard -->
        <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class="fa-solid fa-chart-line sidebar-icon"></i>
                Dashboard
            </a>
        </li>
        <!-- Request Approval -->
        <li class="<?= $current_page === 'request_approval.php' ? 'active' : '' ?>">
            <a href="request_approval.php">
                <i class="fa-solid fa-check-to-slot sidebar-icon"></i>
                Request Approval
            </a>
        </li>
    </ul>

    <div class="sidebar-section-label">ACCOUNT</div>
    <ul class="sidebar-menu">
        <li class="<?= $current_page === 'settings.php' ? 'active' : '' ?>">
            <a href="settings.php">
                <i class="fa-solid fa-gear sidebar-icon"></i>
                Settings
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <ul class="sidebar-menu">
            <li>
                <a href="../login.php" class="logout-link">
                    <i class="fa-solid fa-right-from-bracket sidebar-icon"></i>
                    Log out
                </a>
            </li>
        </ul>
    </div>
</div>