<?php
/**
 * API: submit_claim.php
 * CREATE — Inserts a new claim request into claims_table.
 *
 * POST params:
 *   report_id  (int)    — required
 *   claim_note (string) — required, proof of ownership
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require '../../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a claim.']);
    exit();
}

$reportId  = (int)($_POST['report_id']  ?? 0);
$claimNote = trim($_POST['claim_note']  ?? '');
$userId    = (int)$_SESSION['user_id'];

// Validate inputs
if ($reportId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid report ID.']);
    exit();
}
if (empty($claimNote)) {
    echo json_encode(['success' => false, 'message' => 'Claim note is required.']);
    exit();
}
if (strlen($claimNote) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Claim note is too long (max 1000 characters).']);
    exit();
}

// Verify the report exists and is a Found item
$checkSql  = "
    SELECT r.Report_ID, i.Item_Status, r.User_ID AS Reporter_ID
    FROM reports_table r
    INNER JOIN item_table i ON i.Item_ID = r.Item_ID
    WHERE r.Report_ID = ?
    LIMIT 1
";
$checkStmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($checkStmt, 'i', $reportId);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);
$report      = mysqli_fetch_assoc($checkResult);

if (!$report) {
    echo json_encode(['success' => false, 'message' => 'Report not found.']);
    exit();
}
if ((int)$report['Reporter_ID'] === $userId) {
    echo json_encode(['success' => false, 'message' => 'You cannot claim your own report.']);
    exit();
}
if ($report['Item_Status'] !== 'Found') {
    echo json_encode(['success' => false, 'message' => 'You can only claim Found items.']);
    exit();
}

// Check for duplicate active claim (pending or claimed)
$dupSql  = "
    SELECT Claim_Request_ID
    FROM claims_table
    WHERE Report_ID = ? AND User_ID = ? AND Claim_Status IN ('pending', 'claimed')
    LIMIT 1
";
$dupStmt = mysqli_prepare($conn, $dupSql);
mysqli_stmt_bind_param($dupStmt, 'ii', $reportId, $userId);
mysqli_stmt_execute($dupStmt);
$dupResult = mysqli_stmt_get_result($dupStmt);

if (mysqli_fetch_assoc($dupResult)) {
    echo json_encode(['success' => false, 'message' => 'You already have an active claim on this item.']);
    exit();
}

// INSERT the claim — default Claim_Status is 'pending'
$insertSql  = "
    INSERT INTO claims_table (Report_ID, User_ID, Claim_Note, Claim_Status)
    VALUES (?, ?, ?, 'pending')
";
$insertStmt = mysqli_prepare($conn, $insertSql);
mysqli_stmt_bind_param($insertStmt, 'iis', $reportId, $userId, $claimNote);

if (mysqli_stmt_execute($insertStmt)) {
    echo json_encode([
        'success'          => true,
        'claim_request_id' => mysqli_insert_id($conn)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error. Please try again.'
    ]);
}