<?php
require '../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$displayName = 'Guest';
$initial = 'G';
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $query = 'SELECT First_Name, Last_Name, Username FROM User_Table WHERE User_ID = ?';
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($user = mysqli_fetch_assoc($result)) {
        $firstName = htmlspecialchars($user['First_Name'] ?? '');
        $lastName = htmlspecialchars($user['Last_Name'] ?? '');
        $displayName = trim($firstName . ' ' . $lastName);
        if (empty($displayName)) {
            $displayName = htmlspecialchars($user['Username']);
        }
        $initial = strtoupper(substr($displayName, 0, 1));
    }
}

// Fetch categories for dropdown
$categories = [];
$catResult = mysqli_query($conn, 'SELECT Category_ID, Category FROM category_table ORDER BY Category ASC');
while ($cat = mysqli_fetch_assoc($catResult)) {
    $categories[] = $cat;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BalikGamit</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        /* ── LOADING SPINNER ── */
        .cards-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            gap: 12px;
            color: var(--text-muted);
            font-size: 14px;
            grid-column: 1 / -1;
        }
        .spinner {
            width: 22px;
            height: 22px;
            border: 3px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── MODAL OVERLAY ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.52);
            z-index: 999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            backdrop-filter: blur(3px);
        }
        .modal-overlay.open { display: flex; }

        /* ── MODAL BOX ── */
        .modal-box {
            background: #fff;
            border-radius: 18px;
            width: 100%;
            max-width: 860px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            box-shadow: 0 24px 60px rgba(0,0,0,0.2);
            animation: modalIn 0.22s cubic-bezier(.22,.68,0,1.2);
            position: relative;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(20px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* LEFT: image pane */
        .modal-image-pane {
            width: 45%;
            flex-shrink: 0;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .modal-image-pane img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .modal-status-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            z-index: 2;
        }
        .modal-no-image {
            font-size: 72px;
            color: #d1d5db;
        }

        /* close button */
        .modal-close-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.92);
            border: none;
            border-radius: 50%;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            transition: background 0.15s, color 0.15s;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .modal-close-btn:hover { background: #fff; color: var(--text-primary); }

        /* RIGHT: detail pane */
        .modal-detail-pane {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            padding: 28px 28px 24px;
        }
        .modal-item-category {
            font-size: 11px;
            color: var(--blue-text);
            background: var(--blue-bg);
            padding: 3px 10px;
            border-radius: 999px;
            display: inline-block;
            margin-bottom: 10px;
            align-self: flex-start;
        }
        .modal-item-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .modal-item-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }
        .modal-item-meta .meta-row {
            font-size: 12.5px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-item-meta .meta-row i {
            color: var(--text-muted);
            width: 14px;
            text-align: center;
            flex-shrink: 0;
        }
        .modal-description {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 0.5px solid var(--border);
        }

        /* Claim section */
        .claim-form-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .claim-form-title i { color: var(--accent); }
        .claim-field { margin-bottom: 14px; }
        .claim-field label {
            display: block;
            font-size: 11px;
            font-weight: 500;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }
        .claim-field textarea {
            width: 100%;
            min-height: 95px;
            padding: 10px 12px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: var(--text-primary);
            resize: vertical;
            transition: border 0.18s, box-shadow 0.18s;
            outline: none;
        }
        .claim-field textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--focus-ring);
        }
        .claim-field textarea::placeholder { color: var(--text-muted); }

        .claim-submit-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .claim-submit-btn:hover:not(:disabled) { background: var(--primary-hover); }
        .claim-submit-btn:disabled { background: #9ca3af; cursor: not-allowed; }

        .claim-alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 12px;
            display: none;
        }
        .claim-alert.success { background: var(--green-bg); color: var(--green-text); display: block; }
        .claim-alert.error   { background: var(--red-bg);   color: var(--red-text);   display: block; }

        .info-note {
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.55;
        }
        .info-note.blue   { background: var(--blue-bg);  color: #1d4ed8; border: 1px solid #bfdbfe; }
        .info-note.yellow { background: #fef9c3; color: #92400e; border: 1px solid #fde68a; }
        .info-note.green  { background: var(--green-bg); color: var(--green-text); border: 1px solid #bbf7d0; }
        .info-note i { flex-shrink: 0; margin-top: 2px; }

        /* Result count */
        .result-count {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: -4px;
        }
        .result-count strong { color: var(--text-primary); font-weight: 600; }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 28px;
            right: 28px;
            background: #1e293b;
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 13.5px;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            z-index: 9999;
            display: none;
            align-items: center;
            gap: 10px;
            max-width: 320px;
        }
        .toast.show { display: flex; animation: toastIn 0.2s ease; }
        @keyframes toastIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .toast-success i { color: #4ade80; }
        .toast-error   i { color: #f87171; }


    </style>
</head>
<body>
<div class="app-container">

    <?php include_once '../includes/sidebar.php'; ?>

    <div class="main-content">

        <!-- HEADER -->
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <h1>Welcome back, <?php echo $displayName; ?></h1>
                <p>Track lost and found items in real time, filter by status, and sort by category.</p>
            </div>
            <div class="dashboard-user-card">
                <div class="user-avatar-circle"><?php echo $initial; ?></div>
                <div class="user-card-info">
                    <span class="user-card-name"><?php echo $displayName; ?></span>
                    <span class="user-card-status">● Online</span>
                </div>
            </div>
        </div>

        <!-- CONTROLS -->
        <div class="dashboard-controls">
            <div class="search-panel">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Search by item name or location...">
                </div>
                <div class="status-filters">
                    <button type="button" class="status-btn active" data-filter="all">All</button>
                    <button type="button" class="status-btn" data-filter="Lost">Lost</button>
                    <button type="button" class="status-btn" data-filter="Found">Found</button>

                </div>
            </div>
            <div class="category-panel">
                <label for="category-filter">Category</label>
                <select id="category-filter">
                    <option value="0">All categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['Category_ID']; ?>">
                            <?php echo htmlspecialchars($cat['Category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <a href="report_item.php" class="myreports-new-btn">
                <i class="fa-solid fa-plus"></i> New Report
            </a>
        </div>

        <!-- RESULT COUNT -->
        <p class="result-count" id="resultCount" style=" font-size: 16px; margin-bottom: 16px;">
            Showing <strong id="resultNum">0</strong> item(s)
        </p>

        <!-- CARDS GRID -->
        <div class="cards-grid" id="cardsGrid">
            <div class="cards-loading">
                <div class="spinner"></div> Loading items...
            </div>
        </div>

        <!-- EMPTY STATE -->
        <div class="dashboard-empty" id="emptyState" style="display:none;">
            <i class="fa-solid fa-box-open" style="font-size:32px;margin-bottom:12px;color:#9ca3af;"></i>
            <p>No items found matching your search.</p>
        </div>

        <!-- FOOTER -->
        <div class="dashboard-footer">
            <span>© 2026 BalikGamit — Async V.1.0</span>
            <a href="#">About Us</a>
        </div>

    </div>
</div>

<!-- ═══════════════════════════════════════
     ITEM DETAIL MODAL
═══════════════════════════════════════ -->
<div class="modal-overlay" id="itemModal" role="dialog" aria-modal="true">
    <div class="modal-box">

        <!-- LEFT: Big image -->
        <div class="modal-image-pane" id="modalImagePane">
            <span class="modal-status-badge" id="modalStatusBadge"></span>
            <img id="modalImg" src="" alt="Item image" style="display:none;">
            <div class="modal-no-image" id="modalNoImage" style="display:none;">
                <i class="fa-solid fa-image"></i>
            </div>
        </div>

        <!-- RIGHT: Details + Claim form -->
        <div class="modal-detail-pane" id="modalDetailPane">
            <!-- filled by JS -->
        </div>

        <button class="modal-close-btn" id="modalCloseBtn" title="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast">
    <i class="fa-solid fa-circle-check"></i>
    <span id="toastMsg"></span>
</div>

<script>
/* ═══════════════════════════════════════
   CONFIG
═══════════════════════════════════════ */
const SESSION_USER_ID = <?php echo (int)$_SESSION['user_id']; ?>;
const API_BASE        = 'api/';   // relative to pages/ folder

let activeStatus   = 'all';
let activeCategory = '0';
let searchTimer    = null;

/* ═══════════════════════════════════════
   READ — Load & render cards via AJAX
═══════════════════════════════════════ */
function loadCards() {
    $('#cardsGrid').html('<div class="cards-loading"><div class="spinner"></div> Loading items...</div>');
    $('#emptyState, #resultCount').hide();

    $.ajax({
        url: API_BASE + 'get_items.php',
        method: 'GET',
        data: {
            search:      $('#searchInput').val().trim(),
            status:      activeStatus,
            category_id: activeCategory
        },
        dataType: 'json',
        success(items) {
            $('#cardsGrid').empty();

            if (!items || !items.length) {
                $('#emptyState').show();
                return;
            }

            $('#resultNum').text(items.length);
            $('#resultCount').show();

            $.each(items, function(index, item) {
                const statusCls = item.Item_Status === 'Lost' ? 'status-lost' : 'status-found';
                const action    = item.Item_Status === 'Lost' ? 'Search now' : 'Claim now';
                
                // IMAGE LOGIC: Database stores "uploads/filename.png"
                // home.php is in /student/, uploads is in /uploads/
                const imgPath = item.Item_Image ? `../${item.Item_Image}` : null;
                const imgHtml = imgPath 
                    ? `<img src="${imgPath}" alt="${esc(item.Item_Name)}" style="width:100%;height:100%;object-fit:cover;">`
                    : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#222;color:#444;"><i class="fa-solid fa-image fa-2x"></i></div>`;

                const $card = $(`
                    <div class="item-card" data-report-id="${item.Report_ID}">
                        <div class="item-image">${imgHtml}</div>
                        <div class="item-card-header">
                            <span class="item-status ${statusCls}">${esc(item.Item_Status)}</span>
                            <span class="item-category">${esc(item.Category)}</span>
                        </div>
                        <div class="item-card-body">
                            <h3 class="item-title">${esc(item.Item_Name)}</h3>
                            <p class="item-meta">
                                <i class="fa-solid fa-location-dot"></i>
                                ${esc(item.Location)}
                            </p>
                        </div>
                        <div class="item-card-footer">
                            <span class="item-date">
                                <i class="fa-regular fa-calendar"></i>
                                ${fmtDate(item.Date_filed)}
                            </span>
                            <span class="item-action">
                                ${action} <i class="fa-solid fa-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                `);

                $card.on('click', () => openModal(item.Report_ID));
                $('#cardsGrid').append($card);
            });
        },
        error() {
            $('#cardsGrid').html(`
                <div class="dashboard-empty" style="grid-column:1/-1;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:28px;color:#9ca3af;margin-bottom:10px;"></i>
                    <p>Failed to load items. Please refresh the page.</p>
                </div>
            `);
        }
    });
}

/* ═══════════════════════════════════════
   READ — Open detail modal
═══════════════════════════════════════ */
function openModal(reportId) {
    // Show modal immediately with spinner on the right pane
    $('#modalDetailPane').html('<div class="cards-loading" style="height:100%;"><div class="spinner"></div> Loading...</div>');
    $('#modalImg').hide();
    $('#modalNoImage').show();
    $('#modalStatusBadge').attr('class', 'modal-status-badge').text('');
    $('#itemModal').addClass('open');
    $('body').css('overflow', 'hidden');

    $.ajax({
        url: API_BASE + 'get_item_detail.php',
        method: 'GET',
        data: { report_id: reportId },
        dataType: 'json',
        success(d) {
            if (!d || d.error) {
                closeModal();
                showToast('Could not load item details.', 'error');
                return;
            }

            // Left pane — image
            const statusCls = d.Item_Status === 'Lost' ? 'status-lost' : 'status-found';
            $('#modalStatusBadge').addClass(statusCls).text(d.Item_Status);

            if (d.Item_Image) {
                $('#modalImg')
                    .attr('src', `../${d.Item_Image}`)
                    .attr('alt', esc(d.Item_Name))
                    .show();
                $('#modalNoImage').hide();
            } else {
                $('#modalImg').hide();
                $('#modalNoImage').show();
            }

            // Right pane — details
            const reporterName = d.Reporter ? esc(d.Reporter) : 'Unknown';
            $('#modalDetailPane').html(`
                <span class="modal-item-category">${esc(d.Category)}</span>
                <h2 class="modal-item-title">${esc(d.Item_Name)}</h2>
                <div class="modal-item-meta">
                    <div class="meta-row"><i class="fa-solid fa-location-dot"></i>${esc(d.Location)}</div>
                    <div class="meta-row"><i class="fa-regular fa-calendar"></i>${fmtDate(d.Date_filed)}</div>
                    <div class="meta-row"><i class="fa-solid fa-user"></i>Reported by <strong style="margin-left:3px;">${reporterName}</strong></div>
                </div>
                <p class="modal-description">${esc(d.Item_Description || 'No description provided.')}</p>
                <div id="claimSection"></div>
            `);

            renderClaimSection(d);
        },
        error() {
            closeModal();
            showToast('Failed to load item. Please try again.', 'error');
        }
    });
}

/* ═══════════════════════════════════════
   CREATE — Render claim section
═══════════════════════════════════════ */
function renderClaimSection(d) {
    const $sec = $('#claimSection');

    // This user is the reporter
    if (parseInt(d.Reporter_ID) === SESSION_USER_ID) {
        $sec.html(`
            <div class="info-note blue">
                <i class="fa-solid fa-circle-info"></i>
                <span>This is your reported item. Manage it from
                <a href="my_reports.php" style="color:var(--accent);font-weight:600;margin-left:2px;">My Reports</a>.</span>
            </div>
        `);
        return;
    }

    // Lost item — can't claim, should report if found
    if (d.Item_Status === 'Lost') {
        $sec.html(`
            <div class="info-note yellow">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>This item is <strong>reported lost</strong>. If you found it, please
                <a href="report_item.php" style="color:#92400e;font-weight:600;">report it here</a>
                so the owner can be notified.</span>
            </div>
        `);
        return;
    }

    // User already has a claim
    if (d.user_claim_status && d.user_claim_status !== 'rejected') {
        const map = {
            pending: { cls: 'blue',  icon: 'fa-clock',        msg: "Your claim request is <strong>pending</strong> admin review." },
            claimed: { cls: 'green', icon: 'fa-circle-check', msg: "This item has been <strong>successfully claimed</strong>." }
        };
        const s = map[d.user_claim_status] || map['pending'];
        $sec.html(`
            <div class="info-note ${s.cls}">
                <i class="fa-solid ${s.icon}"></i>
                <span>${s.msg}</span>
            </div>
        `);
        return;
    }

    // Show claim form (Found items + no active claim or rejected)
    $sec.html(`
        <div class="claim-alert" id="claimAlert"></div>
        <div class="claim-form-title">
            <i class="fa-solid fa-hand-holding"></i>
            Submit a Claim Request
        </div>
        <form id="claimForm">
            <input type="hidden" id="claimReportId" value="${d.Report_ID}">
            <div class="claim-field">
                <label for="claimNote">Proof of Ownership</label>
                <textarea
                    id="claimNote"
                    placeholder="Describe the item in detail — color, brand markings, where you lost it, or any other proof it belongs to you..."
                    required
                ></textarea>
            </div>
            <button type="submit" class="claim-submit-btn" id="claimSubmitBtn">
                <i class="fa-solid fa-paper-plane"></i> Submit Claim Request
            </button>
        </form>
    `);

    // CREATE — handle claim submission
    $('#claimForm').on('submit', function(e) {
        e.preventDefault();
        const note = $('#claimNote').val().trim();
        if (!note) {
            setClaimAlert('Please describe your proof of ownership.', 'error');
            return;
        }
        const reportId = $('#claimReportId').val();

        $('#claimSubmitBtn').prop('disabled', true)
            .html('<i class="fa-solid fa-spinner fa-spin"></i> Submitting...');

        $.ajax({
            url: API_BASE + 'submit_claim.php',
            method: 'POST',
            data: { report_id: reportId, claim_note: note },
            dataType: 'json',
            success(res) {
                if (res.success) {
                    $sec.html(`
                        <div class="info-note green">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Your claim has been <strong>submitted successfully</strong>! The admin will review it shortly.</span>
                        </div>
                    `);
                    showToast('Claim submitted successfully!', 'success');
                } else {
                    setClaimAlert(res.message || 'Could not submit claim.', 'error');
                    $('#claimSubmitBtn').prop('disabled', false)
                        .html('<i class="fa-solid fa-paper-plane"></i> Submit Claim Request');
                }
            },
            error() {
                setClaimAlert('Server error. Please try again.', 'error');
                $('#claimSubmitBtn').prop('disabled', false)
                    .html('<i class="fa-solid fa-paper-plane"></i> Submit Claim Request');
            }
        });
    });
}

function setClaimAlert(msg, type) {
    $('#claimAlert').attr('class', `claim-alert ${type}`).text(msg);
}

/* ═══════════════════════════════════════
   MODAL CLOSE
═══════════════════════════════════════ */
function closeModal() {
    $('#itemModal').removeClass('open');
    $('body').css('overflow', '');
}

/* ═══════════════════════════════════════
   TOAST
═══════════════════════════════════════ */
function showToast(msg, type = 'success') {
    const iconCls = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';
    $('#toast').find('i').attr('class', `fa-solid ${iconCls}`);
    $('#toast').attr('class', `toast toast-${type} show`);
    $('#toastMsg').text(msg);
    setTimeout(() => $('#toast').removeClass('show'), 3500);
}

/* ═══════════════════════════════════════
   HELPERS
═══════════════════════════════════════ */
function esc(str) {
    if (str === null || str === undefined) return '';
    return $('<div>').text(String(str)).html();
}
function fmtDate(str) {
    if (!str) return '—';
    const d = new Date(str);
    return isNaN(d) ? str : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

/* ═══════════════════════════════════════
   EVENT BINDINGS
═══════════════════════════════════════ */
// Search — debounced 350ms
$('#searchInput').on('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadCards, 350);
});

// Category filter
$('#category-filter').on('change', function() {
    activeCategory = $(this).val();
    loadCards();
});

// Status filter buttons
$(document).on('click', '.status-btn[data-filter]', function() {
    $('.status-btn[data-filter]').removeClass('active');
    $(this).addClass('active');
    activeStatus = $(this).data('filter');
    loadCards();
});

// Close modal
$('#modalCloseBtn').on('click', closeModal);
$('#itemModal').on('click', function(e) {
    if ($(e.target).is('#itemModal')) closeModal();
});
$(document).on('keydown', e => { if (e.key === 'Escape') closeModal(); });

/* ═══════════════════════════════════════
   INIT
═══════════════════════════════════════ */
$(document).ready(loadCards);
</script>

<?php include_once '../logout-modal.php'; ?>
</body>
</html>