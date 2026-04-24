<?php
session_start();
session_destroy();
header("Location: /balikgamit/login.php");
exit();
?>