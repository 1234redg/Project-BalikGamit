<?php 
require 'db.php'; 
include 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    echo "<h2>Please <a href='login.php'>login</a> to report an item.</h2>";
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();

        $item_sql = "INSERT INTO Item_Table (Item_ID, Item_Name, Item_Status, Item_Description, Category_ID) 
                     VALUES (?, ?, ?, ?, ?)";
        $item_stmt = $pdo->prepare($item_sql);
        $item_stmt->execute([
            $_POST['item_id'], 
            $_POST['name'], 
            $_POST['status'], 
            $_POST['desc'], 
            $_POST['cat_id']
        ]);

        $pub_sql = "INSERT INTO Publication_Table (Publication_ID, User_ID, Item_ID, Date_filed, Location, Claim_Status_ID) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $pub_stmt = $pdo->prepare($pub_sql);
        $pub_stmt->execute([
            $_POST['pub_id'], 
            $_SESSION['user_id'], // Uses the actual logged-in User ID
            $_POST['item_id'], 
            date('Y-m-d'), 
            $_POST['loc'], 
            'STS 301' 
        ]);

        $pdo->commit();
        $message = "<p style='color:green;'>Item successfully published!</p>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<p style='color:red;'>Failed to publish: " . $e->getMessage() . "</p>";
    }
}
?>

<h2>Report an Item</h2>
<?php echo $message; ?>
<form action="publish.php" method="POST">
    <fieldset>
        <legend>Item Information</legend>
        <input type="text" name="item_id" placeholder="Item ID (ITM 1007)" required>
        <input type="text" name="name" placeholder="Item Name" required>
        <select name="status">
            <option value="Found">Found</option>
            <option value="Lost">Lost</option>
        </select>
        <textarea name="desc" placeholder="Description"></textarea>
        <select name="cat_id">
            <option value="CAT 201">Electronics</option>
            <option value="CAT 202">Personal Belongings</option>
            <option value="CAT 203">Accessories</option>
        </select>
    </fieldset>

    <fieldset style="margin-top:10px;">
        <legend>Publication Details</legend>
        <input type="text" name="pub_id" placeholder="Pub ID (PUB 007)" required>
        <input type="text" name="loc" placeholder="Location Found/Lost" required>
    </fieldset>
    
    <button type="submit" style="margin-top:10px;">Post Report</button>
</form>
</body>
</html>