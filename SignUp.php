<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TicketGeek - Sign Up</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>

    <header class="main-header">
        <div class="header-content">
            <div class="left-nav">
                <a href="index.html" class="logo">TicketGeek</a>

                <nav>
                    <a href="#">Concerts</a>
                    <a href="#">Sports</a>
                    <a href="#">Arts & Theatre</a>
                    <a href="#">More</a>
                </nav>
            </div>

            <div class="right-nav">
                <a href="login.html" class="user-icon">Login / Sign Up</a>
            </div>
        </div>
    </header>

    <div class="content-wrapper">
        <div class="auth-container">
            
            <h2>Create Your TicketGeek Account</h2>
            
            <form action="#" method="POST">
                <div class="input-group">
                    <label for="signup-name">Full Name</label>
                    <input type="text" id="signup-name" class="auth-input" required>
                </div>
                <div class="input-group">
                    <label for="signup-email">Email</label>
                    <input type="email" id="signup-email" class="auth-input" required>
                </div>
                <div class="input-group">
                    <label for="signup-password">Password</label>
                    <input type="password" id="signup-password" class="auth-input" required>
                </div>
                <div class="input-group">
                    <label for="signup-confirm">Confirm Password</label>
                    <input type="password" id="signup-confirm" class="auth-input" required>
                </div>
                <button type="submit" class="auth-submit-btn">Sign Up</button>
            </form>

            <span class="switch-auth-link">
                Already have an account? <a href="login.html">Login here</a>.
            </span>
        </div>
    </div>

    <footer>
        <p>Â© 2025 TicketGeek</p>
        <a href="about-us.html">About Us</a> | <a href="faq.html">FAQ</a>
    </footer>

</body>
</html>