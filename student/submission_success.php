<?php
require '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['new_report'])) {
    header("Location: report_item.php");
    exit();
}

$report      = $_SESSION['new_report'];
$username    = $_SESSION['username'] ?? 'User';
$reportedStatus = $report['status'];
$oppositeStatus = $reportedStatus === 'Lost' ? 'Found' : 'Lost';

// Clear session after reading so refresh doesn't re-trigger
unset($_SESSION['new_report']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Submitted - BalikGamit</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app-container">
    <?php include_once '../includes/sidebar.php'; ?>

    <div class="main-content">

        <!-- Header -->
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <span class="dashboard-section-label">Report Submitted</span>
                <h1>You're all set!</h1>
                <p>Your item has been listed. We're now searching for potential matches.</p>
            </div>
            <div class="dashboard-user-card">
                <div class="user-avatar-circle"><?= strtoupper(substr($username, 0, 1)) ?></div>
                <div class="user-card-info">
                    <span class="user-card-name"><?= htmlspecialchars($username) ?></span>
                    <span class="user-card-status">● Online</span>
                </div>
            </div>
        </div>

        <!-- Success Banner -->
        <div class="success-banner">
            <div class="success-banner__icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="success-banner__text">
                <strong>Report submitted successfully!</strong>
                <span>
                    Your <strong><?= htmlspecialchars($reportedStatus) ?></strong> item
                    "<strong><?= htmlspecialchars($report['name']) ?></strong>" has been added to the BalikGamit board.
                </span>
            </div>
            <div class="success-banner__actions">
                <a href="home.php" class="report-btn report-btn--cancel">
                    <i class="fa-solid fa-house"></i> Go Home
                </a>
                <a href="report_item.php" class="report-btn report-btn--submit">
                    <i class="fa-solid fa-plus"></i> Report Another
                </a>
            </div>
        </div>

        <!-- Smart Match Section -->
        <div class="smart-match-section">
            <div class="smart-match-header">
                <div class="smart-match-header__left">
                    <span class="smart-match-badge">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> AI Smart Match
                    </span>
                    <h2>Potential Matches Found</h2>
                    <p>
                        Based on your <strong><?= $reportedStatus ?></strong> item, we looked for
                        <strong><?= $oppositeStatus ?></strong> items that might be related.
                    </p>
                </div>
            </div>

            <!-- Loading State -->
            <div class="smart-match-loading" id="matchLoading">
                <div class="smart-match-spinner"></div>
                <p>Analyzing items with AI…</p>
            </div>

            <!-- No Match State (hidden by default) -->
            <div class="smart-match-empty" id="matchEmpty" style="display:none;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <p>No strong matches found right now.</p>
                <span>Check back later as more items get reported.</span>
            </div>

            <!-- Error State (hidden by default) -->
            <div class="smart-match-empty" id="matchError" style="display:none;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <p>Could not load suggestions.</p>
                <span>The matching service is temporarily unavailable.</span>
            </div>

            <!-- Results Grid -->
            <div class="cards-grid" id="matchResults" style="display:none;"></div>
        </div>

    </div>
</div>

<script>
// Fire the suggestion fetch immediately on page load
(async function loadMatches() {
    const loading = document.getElementById('matchLoading');
    const empty   = document.getElementById('matchEmpty');
    const error   = document.getElementById('matchError');
    const results = document.getElementById('matchResults');

    try {
        const res  = await fetch('../student/api/match_suggestions.php');
        const data = await res.json();

        loading.style.display = 'none';

        if (!data.matches || data.matches.length === 0) {
            empty.style.display = 'flex';
            return;
        }

        results.style.display = 'grid';
        results.innerHTML = data.matches.map(item => {
            const statusClass = item.status === 'Lost' ? 'status-lost' : 'status-found';
            const imgSrc = item.image
                ? `../assets/images/${item.image}`
                : `../assets/images/placeholder.png`;

            return `
            <div class="item-card">
                <div class="item-image">
                    <img src="${imgSrc}" alt="${escHtml(item.name)}"
                         onerror="this.src='../assets/images/placeholder.png'">
                </div>
                <div class="item-card-header">
                    <span class="item-status ${statusClass}">${escHtml(item.status)}</span>
                    <span class="item-category">${escHtml(item.category ?? '')}</span>
                </div>
                <div class="item-card-body">
                    <h3 class="item-title">${escHtml(item.name)}</h3>
                    <p class="item-meta">
                        <i class="fa-solid fa-location-dot"></i> ${escHtml(item.location)}
                    </p>
                    <div class="smart-match-reason">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        ${escHtml(item.reason)}
                    </div>
                </div>
                <div class="item-card-footer">
                    <span class="item-date">
                        <i class="fa-regular fa-calendar"></i> ${escHtml(item.date)}
                    </span>
                    <a href="reported.php?id=${item.item_id}" class="item-action">
                        View <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>`;
        }).join('');

    } catch (e) {
        loading.style.display = 'none';
        error.style.display   = 'flex';
    }
})();

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
</body>
</html>