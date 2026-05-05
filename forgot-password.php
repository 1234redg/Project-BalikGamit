<?php
session_start();

// Handle URL parameters for feedback
$status = $_GET['status'] ?? "";
$message = "";

if ($status == "notfound") {
    $message = '<p style="color: #dc2626; font-weight: 600; margin-bottom: 16px;">No account found with that email address.</p>';
} elseif ($status == "error") {
    $message = '<p style="color: #dc2626; font-weight: 600; margin-bottom: 16px;">Something went wrong. Please try again later.</p>';
} elseif ($status == "empty") {
    $message = '<p style="color: #dc2626; font-weight: 600; margin-bottom: 16px;">Please enter your email address.</p>';
}

// Dynamic Path Logic
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$isSubfolder = ($currentDir === 'student' || $currentDir === 'admin');
$prefix = $isSubfolder ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - BalikGamit</title>
    
    <!-- SweetAlert2 Library[cite: 3] -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Dynamic Assets -->
    <link rel="stylesheet" href="<?= $prefix ?>assets/css/style.css">
    
    <style>
        /* Consistent Theming for Popups */
        .balikgamit-popup {
            border-radius: 16px !important;
            font-family: 'Poppins', sans-serif !important;
            padding: 2rem !important;
        }
        .balikgamit-title {
            color: #1e293b !important;
            font-size: 1.5rem !important;
            font-weight: 700 !important;
        }
        .balikgamit-confirm-btn {
            background-color: #2563eb !important;
            padding: 12px 32px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Panel: Branded Hero (Matches Login/Signup)[cite: 2, 3] -->
        <div class="left-panel">
            <div class="logo">
                <img src="<?= $prefix ?>assets/images/BalikGamitLogo1.png" alt="BalikGamit Logo" width="40" height="40">
                <div class="logo-text">
                    <div class="title">BalikGamit</div>
                    <div class="subtitle">BY ASYNC V.1.0</div>
                </div>
            </div>
            <div class="hero">
                <h1>Reuniting lost items with their owners.</h1>
                <p>A centralized lost and found platform for Bukidnon State University.</p>
            </div>
        </div>

        <!-- Right Panel: Reset Action -->
        <div class="right-panel">
            <form class="login-form" action="<?= $prefix ?>actions/auth/forgot_password_action.php" method="post" autocomplete="off">
                <h1>Forgot Password?</h1>
                <p>Enter your email and we'll send you instructions to reset your password.</p>

                <!-- Status Message -->
                <?= $message ?>

                <div class="form-group">
                    <label for="email">EMAIL ADDRESS</label>
                    <input type="email" id="email" name="email" 
                           placeholder="Enter your registered email" 
                           autocomplete="off" required>
                </div>

                <button type="submit" class="login-btn">Send Reset Link</button>

                <div style="margin-top: 25px; text-align: center;">
                    <p class="signup-link">Remembered your password? <a href="<?= $prefix ?>login.php">Back to Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Popup[cite: 3] -->
    <?php if ($status === 'success'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Check Your Email!',
                text: 'We have sent a password reset link to your inbox.',
                icon: 'success',
                iconColor: '#2563eb',
                showConfirmButton: true,
                confirmButtonText: 'Back to Login',
                confirmButtonColor: '#2563eb',
                customClass: {
                    popup: 'balikgamit-popup',
                    title: 'balikgamit-title',
                    confirmButton: 'balikgamit-confirm-btn'
                },
                allowOutsideClick: false
            }).then((result) => {
                window.location.href = 'login.php'; 
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>