<?php
$host = "127.0.0.1";
$port = 3306;               // Adjust the port 3307 for this pc, 3306 for official deploy
$dbname = "campus_lost_found";
$username = "root";
$password = "";
$conn = mysqli_connect($host, $username, $password, $dbname, $port);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>