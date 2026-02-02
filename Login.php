<?php
require_once 'AuthService.php';

$auth = new AuthService();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($email, $password) === "LOGIN_SUCCESS") {
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TicketGeek - Login</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>

    <header class="main-header">
        <div class="header-content">
            <div class="left-nav">
                <a href="index.php" class="logo">TicketGeek</a>

                <nav>
                    <a href="concerts.php">Concerts</a>
                    <a href="sports.php">Sports</a>
                    <a href="arts_theatre.php">Arts & Theatre</a>
                    <a href="more.php">More</a>
                </nav>
            </div>

            <div class="right-nav">
                <a href="Login.php" class="user-icon">Login / Sign Up</a>
            </div>

            <div class="hamburger" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>

    <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="toggleMobileMenu()">
        <div class="mobile-nav-links" onclick="event.stopPropagation()">
            <a href="index.php" class="logo mobile-only" style="font-size: 28px; margin-bottom: 20px;">TicketGeek</a>
            <a href="concerts.php">Concerts</a>
            <a href="sports.php">Sports</a>
            <a href="arts_theatre.php">Arts & Theatre</a>
            <a href="more.php">More</a>
            <hr style="width: 50%; border-color: #444; margin: 15px 0;">
            <a href="Login.php" class="user-icon">Login / Sign Up</a>
        </div>
    </div>

    <script>
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    
    function toggleMobileMenu() {
        const hamburger = document.querySelector('.hamburger');
        const overlay = document.getElementById('mobileNavOverlay');
        hamburger.classList.toggle('active');
        overlay.classList.toggle('active');
        if (overlay.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
            document.body.style.overflowX = 'hidden';
        } else {
            document.body.style.overflow = '';
            document.body.style.overflowX = '';
        }
    }
    </script>

    <div class="content-wrapper">
        <div class="auth-container">
            <h2>Login to TicketGeek</h2>
            
            <form method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="auth-input" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="auth-input" required>
                </div>
                
                <?php if($error): ?>
                    <p style="color: red; text-align: center; margin-bottom: 10px;"><?php echo $error; ?></p>
                <?php endif; ?>
                
                <button type="submit" class="auth-submit-btn">Login</button>
            </form>

            <span class="switch-auth-link">
                Don't have an account? <a href="SignUp.php">Sign Up here</a>.
            </span>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 TicketGeek</p>
        <a href="AboutUs.php">About Us</a> | <a href="FAQ.php">FAQ</a> | <a href="ContactUs.php">Contact Us</a>
    </footer>

</body>
</html>