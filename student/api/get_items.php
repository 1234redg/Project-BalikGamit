<?php
/**
 * API: get_items.php
 * READ — Returns a list of items (joined with reports, categories)
 * for the dashboard cards grid.
 *
 * GET params:
 *   search      (string) — search term for Item_Name or Location
 *   status      (string) — 'all' | 'Lost' | 'Found'
 *   category_id (int)    — 0 = all, otherwise filter by Category_ID
 */

header('Content-Type: application/json');

// Allow only GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

require '../../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$search      = trim($_GET['search']      ?? '');
$status      = trim($_GET['status']      ?? 'all');
$category_id = (int)($_GET['category_id'] ?? 0);

// Build dynamic WHERE clause
$conditions = [];
$params     = [];
$types      = '';

// Status filter
if ($status !== 'all' && in_array($status, ['Lost', 'Found'])) {
    $conditions[] = 'i.Item_Status = ?';
    $params[]     = $status;
    $types       .= 's';
}

// Category filter
if ($category_id > 0) {
    $conditions[] = 'i.Category_ID = ?';
    $params[]     = $category_id;
    $types       .= 'i';
}

// Search filter (Item_Name OR Location)
if ($search !== '') {
    $conditions[] = '(i.Item_Name LIKE ? OR r.Location LIKE ?)';
    $like         = '%' . $search . '%';
    $params[]     = $like;
    $params[]     = $like;
    $types       .= 'ss';
}

$where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$sql = "
    SELECT
        r.Report_ID,
        r.Date_filed,
        r.Location,
        i.Item_ID,
        i.Item_Name,
        i.Item_Status,
        i.Item_Description,
        i.Item_Image,
        i.Category_ID,
        c.Category
    FROM reports_table r
    INNER JOIN item_table    i ON i.Item_ID    = r.Item_ID
    INNER JOIN category_table c ON c.Category_ID = i.Category_ID
    $where
    ORDER BY r.Date_filed DESC
";

$stmt = mysqli_prepare($conn, $sql);

if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

echo json_encode($items);