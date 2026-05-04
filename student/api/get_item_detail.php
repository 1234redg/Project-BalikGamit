<?php
/**
 * API: get_item_detail.php
 * READ — Returns full details for one report (for the modal).
 * Also returns the logged-in user's claim status on this item.
 *
 * GET params:
 *   report_id (int) — required
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

require '../../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$reportId    = (int)($_GET['report_id'] ?? 0);
$sessionUser = (int)$_SESSION['user_id'];

if ($reportId <= 0) {
    echo json_encode(['error' => 'Invalid report_id']);
    exit();
}

// Fetch item + report + category + reporter name
$sql = "
    SELECT
        r.Report_ID,
        r.User_ID       AS Reporter_ID,
        r.Date_filed,
        r.Location,
        i.Item_ID,
        i.Item_Name,
        i.Item_Status,
        i.Item_Description,
        i.Item_Image,
        c.Category,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS Reporter
    FROM reports_table   r
    INNER JOIN item_table     i ON i.Item_ID    = r.Item_ID
    INNER JOIN category_table c ON c.Category_ID = i.Category_ID
    INNER JOIN user_table     u ON u.User_ID    = r.User_ID
    WHERE r.Report_ID = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $reportId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item   = mysqli_fetch_assoc($result);

if (!$item) {
    echo json_encode(['error' => 'Item not found']);
    exit();
}

// Check if logged-in user already has a claim on this report
$claimSql  = "
    SELECT Claim_Status
    FROM claims_table
    WHERE Report_ID = ? AND User_ID = ?
    ORDER BY Claim_Request_ID DESC
    LIMIT 1
";
$claimStmt = mysqli_prepare($conn, $claimSql);
mysqli_stmt_bind_param($claimStmt, 'ii', $reportId, $sessionUser);
mysqli_stmt_execute($claimStmt);
$claimResult = mysqli_stmt_get_result($claimStmt);
$claimRow    = mysqli_fetch_assoc($claimResult);

// Add claim status to response (null if no claim exists)
$item['user_claim_status'] = $claimRow ? $claimRow['Claim_Status'] : null;

echo json_encode($item);