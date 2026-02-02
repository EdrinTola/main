<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.8" />
    <title>TicketGeek - FAQ</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .page-content {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            min-height: 50vh;
            color: #333;
        }
        .page-content h1 {
            color: #0a0a23;
            font-size: 28px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
            margin-top: 0;
        }
        
        .faq-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .question {
            background-color: #f7f7f7;
            padding: 15px 20px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            color: #0a0a23;
        }

        .answer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            background-color: white;
            line-height: 1.6;
        }
    </style>
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="user-icon">
                        <?php echo 'Hello, ' . htmlspecialchars($_SESSION["name"]); ?>
                    </span>
                    
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin_panel.php" class="user-icon" style="margin-left: 10px; color: #ffcc00;">Admin Panel</a>
                    <?php endif; ?>

                    <a href="logout.php" class="user-icon" style="margin-left: 10px; font-size: 0.9em;">Logout</a>
                <?php else: ?>
                    <a href="Login.php" class="user-icon">Login / Sign Up</a>
                <?php endif; ?>
            </div>

            <div class="hamburger" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="toggleMobileMenu()">
        <div class="mobile-nav-links" onclick="event.stopPropagation()">
            <a href="index.php" class="logo mobile-only" style="font-size: 28px; margin-bottom: 20px;">TicketGeek</a>
            <a href="concerts.php">Concerts</a>
            <a href="sports.php">Sports</a>
            <a href="arts_theatre.php">Arts & Theatre</a>
            <a href="more.php">More</a>
            <hr style="width: 50%; border-color: #444; margin: 15px 0;">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="user-icon mobile-only">
                    <?php echo 'Hello, ' . htmlspecialchars($_SESSION["name"]); ?>
                </span>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin_panel.php" class="user-icon mobile-only" style="color: #ffcc00;">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" class="user-icon mobile-only">Logout</a>
            <?php else: ?>
                <a href="Login.php" class="user-icon">Login / Sign Up</a>
            <?php endif; ?>
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
        <section class="page-content">
            <h1>Frequently Asked Questions (FAQ)</h1>
            
            <div class="faq-item">
                <div class="question">How do I know my tickets are legitimate?</div>
                <div class="answer">All tickets sold on TicketGeek are covered by our <b>FanProtect Guarantee</b>. This ensures that the tickets you receive are valid and authentic for entry to the event.</div>
            </div>

            <div class="faq-item">
                <div class="question">What forms of payment do you accept?</div>
                <div class="answer">We accept major credit cards (Visa, MasterCard, Amex), PayPal, and other popular regional payment methods. All payments are processed securely.</div>
            </div>

            <div class="faq-item">
                <div class="question">Can I get a refund if the event is cancelled?</div>
                <div class="answer">If an event is <b>cancelled and not rescheduled</b>, we will automatically issue you a full refund for the purchase price, including fees. If the event is postponed, your tickets will typically be valid for the new date.</div>
            </div>

            <div class="faq-item">
                <div class="question">How are the tickets delivered?</div>
                <div class="answer">Ticket delivery methods vary by event but generally include <b>electronic transfer</b> (e-tickets), <b>mobile delivery</b>, or in some cases, physical shipment. The delivery method will be clearly stated at checkout.</div>
            </div>
        </section>
    </div>

    <footer>
        <p>© 2025-2026 TicketGeek</p>
        <a href="AboutUs.php">About Us</a> | <a href="FAQ.php">FAQ</a> | <a href="ContactUs.php">Contact Us</a>
    </footer>

</body>
</html>