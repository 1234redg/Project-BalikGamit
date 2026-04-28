<?php
session_start();
session_destroy();
header("Location: /balikgamit/actions/auth/login_action.php");
exit();
?>