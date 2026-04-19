<?php 
require 'db.php'; 
include 'includes/header.php'; 

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = $_POST['user'];
    $pass_input = $_POST['pass'];

    // Search for the user by username
    $stmt = $pdo->prepare("SELECT * FROM User_Table WHERE Username = ?");
    $stmt->execute([$user_input]);
    $user = $stmt->fetch();

    // Verify the hashed password
    if ($user && password_verify($pass_input, $user['Password'])) {
        // Store the numeric ID and the Username in the session
        $_SESSION['user_id'] = $user['User_ID']; 
        $_SESSION['username'] = $user['Username'];
        
        header("Location: index.php");
        exit();
    } else {
        $message = "<p style='color:red;'>Invalid username or password.</p>";
    }
}
?>

    <h2>Login</h2>
    <?php echo $message; ?>
    <form action="login.php" method="POST">
        <label>Username:</label><br>
        <input type="text" name="user" required><br><br>
        
        <label>Password:</label><br>
        <input type="password" name="pass" required><br><br>
        
        <button type="submit">Enter</button>
    </form>
</body>
</html>