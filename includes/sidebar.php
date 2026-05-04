<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);

// Logic from nav_master to fetch the user's first name[cite: 2]
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$firstName = "Guest";

// Ensure $conn is available from your db.php or config.php
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
        <a href="home.php" class="logo-link">
            <img src="../assets/images/BalikGamitLogo2.png" alt="BalikGamit Logo" class="sidebar-logo">
        </a>
    </div>

    <div class="sidebar-section-label">MAIN</div>
    <ul class="sidebar-menu">
        <li class="<?= $current_page === 'home.php' ? 'active' : '' ?>">
            <a href="home.php">
                <i class="fa-solid fa-house sidebar-icon"></i>
                Home
            </a>
        </li>
        <li class="<?= $current_page === 'report_item.php' ? 'active' : '' ?>">
            <a href="report_item.php">
                <i class="fa-solid fa-circle-plus sidebar-icon"></i>
                Report Item
            </a>
        </li>
        <li class="<?= $current_page === 'my_reports.php' ? 'active' : '' ?>">
            <a href="my_reports.php">
                <i class="fa-solid fa-file-lines sidebar-icon"></i>
                My Reports

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