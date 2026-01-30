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
            
            <h2>Login to TicketGeek</h2>
            
            <form action="#" method="POST">
                <div class="input-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" class="auth-input" required>
                </div>
                <div class="input-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" class="auth-input" required>
                </div>
                <button type="submit" class="auth-submit-btn">Login</button>
            </form>

            <span class="switch-auth-link">
                Don't have an account? <a href="signup.html">Sign Up here</a>.
            </span>
        </div>
    </div>

    <footer>
        <p>Â© 2025 TicketGeek</p>
        <a href="about-us.html">About Us</a> | <a href="faq.html">FAQ</a>
    </footer>

</body>
</html>