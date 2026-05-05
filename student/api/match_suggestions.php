<?php
/**
 * match_suggestions.php
 * Called via fetch() from submission_success.php
 * Returns JSON: { matches: [...] }
 */

require '../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['new_report'])) {
    echo json_encode(['matches' => [], 'error' => 'No session data.']);
    exit();
}

$report  = $_SESSION['new_report'];
$user_id = $_SESSION['user_id'];
$item_id = $report['item_id'];

// -----------------------------------------------------------
// 1. Pull candidate items from DB (opposite status, same user excluded)
//    Lost report → look for Found items | Found report → look for Lost items
// -----------------------------------------------------------
$oppositeStatus = $report['status'] === 'Lost' ? 'Found' : 'Lost';

$candidateStmt = mysqli_prepare($conn,
    "SELECT i.Item_ID, i.Item_Name, i.Item_Status, i.Item_Description, i.Item_Image,
            c.Category, r.Location, r.Date_filed, r.User_ID
     FROM item_table i
     JOIN reports_table r ON i.Item_ID = r.Item_ID
     LEFT JOIN category_table c ON i.Category_ID = c.Category_ID
     WHERE i.Item_Status = ?
       AND i.Item_ID != ?
       AND r.User_ID != ?
     ORDER BY r.Date_filed DESC
     LIMIT 20"
);
mysqli_stmt_bind_param($candidateStmt, "sii", $oppositeStatus, $item_id, $user_id);
mysqli_stmt_execute($candidateStmt);
$candidates = mysqli_fetch_all(mysqli_stmt_get_result($candidateStmt), MYSQLI_ASSOC);

if (empty($candidates)) {
    echo json_encode(['matches' => [], 'note' => 'No candidates in DB.']);
    exit();
}

// -----------------------------------------------------------
// 2. Build candidate list string for Claude prompt
// -----------------------------------------------------------
$candidateLines = [];
foreach ($candidates as $i => $c) {
    $num  = $i + 1;
    $candidateLines[] =
        "$num. ID:{$c['Item_ID']} | Name: {$c['Item_Name']} | "
        . "Category: {$c['Category']} | "
        . "Description: " . ($c['Item_Description'] ?? 'N/A') . " | "
        . "Location: {$c['Location']} | "
        . "Date: {$c['Date_filed']}";
}
$candidateText = implode("\n", $candidateLines);

$reportedItem =
    "Name: {$report['name']}\n"
    . "Status: {$report['status']}\n"
    . "Description: " . ($report['desc'] ?: 'N/A') . "\n"
    . "Location: {$report['loc']}";

$prompt = <<<PROMPT
You are a smart lost-and-found matching assistant for a university campus system called BalikGamit.

A user just reported the following item:
{$reportedItem}

Below is a list of {$oppositeStatus} items currently in the system. Your job is to find the ones most likely to be the same item or related.

Candidate items:
{$candidateText}

Instructions:
- Return ONLY a JSON array, no explanation, no markdown, no extra text.
- Include up to 3 best matches. If none are relevant, return an empty array [].
- Each match object must have exactly these keys:
  {
    "item_id": <number>,
    "reason": "<one short sentence why it matches>"
  }
- Consider: name similarity, description similarity, category, and location proximity.
- Be strict — only include items with a genuine chance of being the same item.
PROMPT;

// -----------------------------------------------------------
// 3. Call Anthropic API
// -----------------------------------------------------------
$apiKey = 557301; // Replace with your actual key or load from config

$payload = json_encode([
    'model'      => 'claude-sonnet-4-20250514',
    'max_tokens' => 512,
    'messages'   => [
        ['role' => 'user', 'content' => $prompt]
    ]
]);

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
    ],
    CURLOPT_TIMEOUT        => 20,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    echo json_encode(['matches' => [], 'error' => 'API request failed.']);
    exit();
}

$apiData = json_decode($response, true);
$rawText = $apiData['content'][0]['text'] ?? '[]';

// Strip any accidental markdown fences
$rawText = preg_replace('/```json|```/', '', $rawText);
$rawText = trim($rawText);

$matchedIds = json_decode($rawText, true);

if (!is_array($matchedIds)) {
    echo json_encode(['matches' => [], 'error' => 'Could not parse AI response.']);
    exit();
}

// -----------------------------------------------------------
// 4. Enrich matched items with full DB data
// -----------------------------------------------------------
$matches = [];
foreach ($matchedIds as $match) {
    $mid = intval($match['item_id'] ?? 0);
    if ($mid === 0) continue;

    foreach ($candidates as $c) {
        if ((int)$c['Item_ID'] === $mid) {
            $matches[] = [
                'item_id'     => $c['Item_ID'],
                'name'        => $c['Item_Name'],
                'status'      => $c['Item_Status'],
                'category'    => $c['Category'],
                'description' => $c['Item_Description'],
                'location'    => $c['Location'],
                'date'        => date('M d, Y', strtotime($c['Date_filed'])),
                'image'       => $c['Item_Image'],
                'reason'      => $match['reason'] ?? '',
            ];
            break;
        }
    }
}

echo json_encode(['matches' => $matches]);
exit();