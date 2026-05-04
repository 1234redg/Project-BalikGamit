<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <a href="dashboard.php" class="logo-link">
            <img src="../assets/images/BalikGamitLogo2.png" alt="BalikGamit Logo" class="sidebar-logo">
        </a>
    </div>

    <div class="sidebar-section-label">MAIN</div>
    <ul class="sidebar-menu">
        <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class="fa-solid fa-gauge-high sidebar-icon"></i>
                Dashboard
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
                <a href="../logout.php" class="logout-link">
                    <i class="fa-solid fa-right-from-bracket sidebar-icon"></i>
                    Log out
                </a>
            </li>
        </ul>
    </div>
</div>