<?php
include 'includes/header.php'; // Includes the navigation and basic styling
require 'db.php';

// Function to fetch and display a table as an HTML table
function displayTable($pdo, $tableName, $query) {
    echo "<h3>Table: $tableName</h3>";
    try {
        $stmt = $pdo->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; margin-bottom: 30px;'>";
            echo "<tr style='background-color: #eee;'>";
            // Create headers from keys of the first row
            foreach (array_keys($rows[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";

            // Create data rows
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No data found in $tableName.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Error loading $tableName: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>Database Overview</h2>";

// 1. Display User_Table
displayTable($pdo, "User_Table", "SELECT * FROM User_Table");

// 2. Display Category_Table
displayTable($pdo, "Category_Table", "SELECT * FROM Category_Table");

// 3. Display Status_Table
displayTable($pdo, "Status_Table (Claim Status)", "SELECT * FROM Status_Table");

// 4. Display Item_Table
displayTable($pdo, "Item_Table", "SELECT * FROM Item_Table");

// 5. Display Publication_Table (Joined with other tables for readability)
$pubQuery = "SELECT 
                p.Publication_ID, 
                u.Username as Reporter, 
                i.Item_Name, 
                p.Date_filed, 
                p.Location, 
                s.Claim_Status 
             FROM Publication_Table p
             LEFT JOIN User_Table u ON p.User_ID = u.User_ID
             LEFT JOIN Item_Table i ON p.Item_ID = i.Item_ID
             LEFT JOIN Status_Table s ON p.Claim_Status_ID = s.Claim_Status_ID";

displayTable($pdo, "Publication_Table (Joined View)", $pubQuery);

?>
</body>
</html>