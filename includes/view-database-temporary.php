<?php
require '../config/db.php';

function displayTable($conn, $tableName, $query) {
    echo "<h3>Table: $tableName</h3>";
    $result = mysqli_query($conn, $query);
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; margin-bottom: 30px;'>";
            echo "<tr style='background-color: #eee;'>";
            $fields = mysqli_fetch_fields($result);
            foreach ($fields as $field) {
                echo "<th>" . htmlspecialchars($field->name) . "</th>";
            }
            echo "</tr>";
            while ($row = mysqli_fetch_assoc($result)) {
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
    } else {
        echo "<p style='color:red;'>Error loading $tableName: " . mysqli_error($conn) . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BalikGamit</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0a0a0a; color: white;">
    <div class="app-container">
        <?php include_once '../includes/sidebar.php'; ?>
        
        <div class="main-content" style="display: flex; flex-direction: column;">
            <?php include '../includes/nav_master.php'; ?>

            <div class="content-body" style="padding-top: 20px;">
                <?php
                echo "<h2>Database Overview</h2>";
                displayTable($conn, "User_Table", "SELECT * FROM User_Table");
                displayTable($conn, "Category_Table", "SELECT * FROM Category_Table");
                displayTable($conn, "Status_Table (Claim Status)", "SELECT * FROM Status_Table");
                displayTable($conn, "Item_Table", "SELECT * FROM Item_Table");

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

                displayTable($conn, "Publication_Table (Joined View)", $pubQuery);
                ?>
            </div>
        </div>
    </div>
</body>
</html>