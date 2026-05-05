<?php
session_start();
$message = '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Preserve input values if an error occurs
$fname_val = isset($_GET['fname']) ? htmlspecialchars($_GET['fname']) : '';
$lname_val = isset($_GET['lname']) ? htmlspecialchars($_GET['lname']) : '';
$email_val = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$phone_val = isset($_GET['phone']) ? htmlspecialchars($_GET['phone']) : '';

if ($success === '1') {
    $message = '<p style="color: #16a34a; font-weight: 600; margin-bottom: 16px;">Account created successfully. Redirecting...</p>';
} elseif (!empty($_GET['message'])) {
    $message = '<p style="color: #dc2626; font-weight: 600; margin-bottom: 16px;">' . htmlspecialchars($_GET['message']) . '</p>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - BalikGamit</title>
    
    <!-- SweetAlert2 Library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- reCAPTCHA API -->
    <script src="https://www.google.com/recaptcha/api.js?onload=renderRecaptcha&render=explicit" async defer></script>
    <script src="assets/js/login.js?v=1.5" defer></script>

    <style>
        /* Custom Theme for SweetAlert to match BalikGamit UI */
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
            background-color: #2563eb !important; /* Your Theme Blue */
            padding: 12px 32px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2) !important;
        }
    </style>
</head>
<body class="signup-page">
    <div class="container">
        <div class="left-panel">
            <div class="logo">
                <img src="assets/images/BalikGamitLogo1.png" alt="BalikGamit Logo" width="40" height="40">
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

        <div class="right-panel">
            <form class="login-form" id="loginForm" action="actions/auth/signup_action.php" method="post" autocomplete="off">
                <h1>Sign Up</h1>
                <p>Please register your account to continue.</p>
                <?php echo $message; ?>

                <div class="form-group">
                    <label for="fname">First Name</label>
                    <input type="text" id="fname" name="fname" placeholder="First Name" value="<?php echo $fname_val; ?>" autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" id="lname" name="lname" placeholder="Last Name" value="<?php echo $lname_val; ?>" autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email here" value="<?php echo $email_val; ?>" autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label for="phone">Contact Number</label>
                    <input type="text" id="phone" name="phone" placeholder="Enter your contact number" value="<?php echo $phone_val; ?>" autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label for="pass">Password</label>
                    <input type="password" id="pass" name="pass" placeholder="Enter your password" autocomplete="new-password" required>
                </div>

                <div class="form-group">
                    <label for="conf_pass">Confirm Password</label>
                    <input type="password" id="conf_pass" name="conf_pass" placeholder="Confirm your password" autocomplete="new-password" required>
                </div>

                <input type="hidden" name="role" value="Student">
                
                <div id="recaptcha-container" style="margin-bottom: 15px; display: none;"></div>

                <button id="submitBtn" type="button" class="login-btn" onclick="handleSubmit()">Sign Up</button>
                
                <p class="signup-link">Already have an account? <a href="login.php">Login here</a></p>
            </form>
        </div>
    </div>

    <!-- Themed Success Popup and Redirect to Student Home -->
    <?php if ($success === '1'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Registration Successful!',
                text: 'Welcome to the community. Redirecting you to your home page...',
                icon: 'success',
                iconColor: '#2563eb',
                showConfirmButton: true,
                confirmButtonText: 'Enter Home',
                confirmButtonColor: '#2563eb',
                customClass: {
                    popup: 'balikgamit-popup',
                    title: 'balikgamit-title',
                    confirmButton: 'balikgamit-confirm-btn'
                },
                allowOutsideClick: false,
                timer: 3500,
                timerProgressBar: true
            }).then((result) => {
                // Path adjusted to your student/home.php location
                window.location.href = 'student/home.php'; 
            });
        });
    </script>
    <?php endif; ?>
</body>
</html> 