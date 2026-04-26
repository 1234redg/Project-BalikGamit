<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --sidebar-width: 260px;
        --sidebar-bg: #001529; /* Dark blue from your screenshot */
        --main-bg: #0a0a0a;    /* Deep black for content area */
    }

    /* This wrapper must be in your main files (index.php, report-item.php, etc.) */
    .app-container {
        display: flex;
        min-height: 100vh;
        width: 100%;
        overflow-x: hidden;
    }

    .sidebar {
        width: var(--sidebar-width);
        background-color: var(--sidebar-bg);
        color: white;
        padding: 30px 20px;
        flex-shrink: 0; /* Prevents sidebar from squishing */
        border-right: 1px solid #1f1f1f;
    }

    .main-content {
        flex-grow: 1; /* Takes up all remaining space */
        background-color: var(--main-bg);
        color: white;
        padding: 40px;
        min-width: 0; /* Fixes potential flexbox overflow issues */
    }

    /* Sidebar Styling */
    .sidebar h2 { margin: 0; font-size: 22px; }
    .sidebar .version { font-size: 11px; color: #888; margin-bottom: 40px; display: block; }
    .menu-label { color: #595959; font-size: 12px; margin: 25px 0 10px 0; font-weight: bold; }
    .sidebar ul { list-style: none; padding: 0; margin: 0; }
    .sidebar li { margin-bottom: 5px; }
    .sidebar a { 
        color: #a6adb4; 
        text-decoration: none; 
        display: block; 
        padding: 10px;
        border-radius: 4px;
        transition: 0.3s;
    }
    .sidebar li.active a { 
        background-color: rgba(255, 255, 255, 0.1); 
        color: white; 
    }
    .sidebar a:hover { background-color: rgba(255, 255, 255, 0.05); }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>BalikGamit</h2>
        <span class="version">BY ASYNC V.1.0</span>
    </div>

    <p class="menu-label">MAIN</p>
    <ul>
        <li class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>"><a href="index.php">Dashboard</a></li>
        <li class="<?= ($current_page == 'view-item.php') ? 'active' : ''; ?>"><a href="view-item.php">View an item</a></li>
        <li class="<?= ($current_page == 'reported-items.php') ? 'active' : ''; ?>"><a href="reported-items.php">Reported Items</a></li>
        <li class="<?= ($current_page == 'report-item.php') ? 'active' : ''; ?>"><a href="report-item.php">Report Item</a></li>
        <li class="<?= ($current_page == 'my-reports.php') ? 'active' : ''; ?>"><a href="my-reports.php">My Reports</a></li>
        <li class="<?= ($current_page == 'history.php') ? 'active' : ''; ?>"><a href="history.php">History</a></li>
    </ul>

    <p class="menu-label">ACCOUNT</p>
    <ul>
        <li class="<?= ($current_page == 'settings.php') ? 'active' : ''; ?>"><a href="settings.php">Settings</a></li>
        <li><a href="auth/logout.php">Logout</a></li>
    </ul>
</div>