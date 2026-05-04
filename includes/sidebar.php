<?php
// sidebar.php
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
<style>
    :root {
        --sidebar-width: 260px;
        --sidebar-bg: #001529; /* Dark blue from your screenshot[cite: 1] */
        --main-bg: #0a0a0a;    /* Deep black for content area[cite: 1] */
    }

    /* GLOBAL FONT & RESET[cite: 1] */
    html, body {
        margin: 0;
        padding: 0;
        background-color: var(--main-bg);
        color: white;
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

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
        flex-shrink: 0;
        border-right: 1px solid #1f1f1f;
    }

    /* Sidebar Styling[cite: 1] */
    .sidebar h2 {
        margin: 0;
        font-size: 22px;
    }

    .sidebar .version {
        font-size: 11px;
        color: #888;
        margin-bottom: 40px;
        display: block;
    }

    .menu-label {
        color: #595959;
        font-size: 12px;
        margin: 25px 0 10px 0;
        font-weight: bold;
    }

    /* Logged-in User Display Style */
    .logged-in-user {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logged-in-user span {
        display: block;
        font-size: 10px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .logged-in-user strong {
        display: block;
        font-size: 14px;
        color: #3498db; /* Consistent with nav_master highlight color[cite: 2] */
        margin-top: 2px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar li {
        margin-bottom: 5px;
    }

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

    .sidebar a:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>BalikGamit</h2>
        <span class="version">BY ASYNC V.1.0</span>
    </div>

    <p class="menu-label">MAIN</p>
    <ul>
        <li class="<?= ($current_page == 'index.php' || $current_page == 'dashboard.php') ? 'active' : ''; ?>"><a
                href="/Balikgamit/student/dashboard.php">Dashboard</a></li>
        <li class="<?= ($current_page == 'reported.php') ? 'active' : ''; ?>"><a href="reported.php">Reported Items</a>
        </li>
<<<<<<< Updated upstream
        <li class="<?= ($current_page == 'report.php') ? 'active' : ''; ?>"><a href="report.php">Report Item</a></li>
        <li class="<?= ($current_page == 'my-reports.php') ? 'active' : ''; ?>"><a href="my-reports.php">My Reports</a>
=======
        <li class="<?= $current_page === 'report-item.php' ? 'active' : '' ?>">
            <a href="report-item.php">
                <i class="fa-solid fa-circle-plus sidebar-icon"></i>
                Report Item
            </a>
>>>>>>> Stashed changes
        </li>
        <li class="<?= ($current_page == 'history.php') ? 'active' : ''; ?>"><a href="history.php">History</a></li>
    </ul>

    <p class="menu-label">ACCOUNT</p>
    
    <!-- User Display added here -->
    <div class="logged-in-user">
        <span>Logged in as</span>
        <strong><?php echo $firstName; ?></strong>
    </div>

    <ul>
        <li class="<?= ($current_page == 'settings.php') ? 'active' : ''; ?>"><a href="settings.php">Settings</a></li>
        <li>
            <a href="../logout.php"
                onclick="return confirm('Are you sure you want to log out? You will need to log in again to access your account.');">
                Logout
            </a>
        </li>
    </ul>
</div>