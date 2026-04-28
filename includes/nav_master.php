<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BalikGamit - Dev Navigator</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; background: #f4f4f4; }
        .dev-nav { background: #2c3e50; color: #fff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .dev-nav h4 { margin: 0 0 10px 0; color: #3498db; text-transform: uppercase; font-size: 12px; }
        .nav-group { margin-bottom: 15px; display: inline-block; vertical-align: top; margin-right: 30px; }
        .nav-group a { display: block; color: #ecf0f1; text-decoration: none; font-size: 14px; margin-bottom: 5px; }
        .nav-group a:hover { color: #3498db; text-decoration: underline; }
        .container { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <nav class="dev-nav">
        <div class="nav-group">
            <h4>Main App</h4>
            <a href="/balikgamit/index.php">Dashboard (Feed)</a>
            <a href="/balikgamit/report-item.php">Post Item (Publish)</a>
            <a href="/balikgamit/settings.php">User Settings</a>
        </div>

        <div class="nav-group">
            <h4>Auth Logic</h4>
            <a href="/balikgamit/actions/auth/login_action.php">Login Page</a>
            <a href="/balikgamit/signup.php">Register Page</a>
            <a href="/balikgamit/auth/forgot-password.php">Forgot Password</a>
            <a href="/balikgamit/auth/change-password.php">Change Password</a>
            <a href="/balikgamit/auth/verify-email.php">Verify Email</a>
            <a href="/balikgamit/auth/logout.php" style="color: #e74c3c;">Logout</a>
        </div>
        <div class="nav-group">
            <h4>temporary</h4>
            <a href="/balikgamit/view-database-temporary.php">View database</a>
        </div>
    </nav>
    <div class="container">