<?php
require '../config/db.php';
session_start();

$user_id = $_SESSION['user_id'];

// SEARCH
$search = isset($_GET['search']) ? $_GET['search'] : "";

// FETCH USER REPORTS
$query = "SELECT * FROM reports 
          WHERE user_id = '$user_id' 
          AND (item_name LIKE '%$search%' OR location LIKE '%$search%')
          ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Reports</title>

<link rel="stylesheet" href="../assets/css/dashboard.css"> <!-- your existing CSS -->
<link rel="stylesheet" href="../assets/css/cards.css">

<style>
/* Reuse dashboard feel */
.container {
    padding: 20px;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.search-box input {
    padding: 10px;
    width: 300px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.card {
    background: #fff;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.card-body {
    padding: 15px;
}

.badge {
    padding: 5px 10px;
    border-radius: 10px;
    font-size: 12px;
}

.found { background: #d4edda; color: #155724; }
.lost { background: #f8d7da; color: #721c24; }

.actions {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

.btn {
    padding: 6px 10px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
}

.edit { background: #007bff; color: white; }
.delete { background: #dc3545; color: white; }
</style>

</head>

<body>

<div class="container">

    <h2>My Reports</h2>
    <p>Manage your reported lost and found items.</p>

    <!-- SEARCH -->
    <div class="top-bar">
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search your reports..." value="<?= $search ?>">
        </form>
    </div>

    <!-- CARDS -->
    <div class="cards">
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
            
            <div class="card">
                <img src="../uploads/<?= $row['image'] ?>" alt="item">

                <div class="card-body">
                    
                    <span class="badge <?= strtolower($row['status']) ?>">
                        <?= strtoupper($row['status']) ?>
                    </span>

                    <h3><?= $row['item_name'] ?></h3>
                    <p>📍 <?= $row['location'] ?></p>
                    <p>📅 <?= date("M d, Y", strtotime($row['created_at'])) ?></p>

                    <div class="actions">
                        <!-- UPDATE -->
                        <a href="edit_report.php?id=<?= $row['id'] ?>" class="btn edit">Edit</a>

                        <!-- DELETE -->
                        <a href="delete_report.php?id=<?= $row['id'] ?>" 
                           class="btn delete"
                           onclick="return confirm('Delete this report?')">
                           Delete
                        </a>
                    </div>

                </div>
            </div>

        <?php } ?>
    </div>

</div>

</body>
</html>