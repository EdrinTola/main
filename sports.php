<?php
session_start();

require_once 'db_connection.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT * FROM events WHERE 
    title LIKE '%NBA%' OR 
    title LIKE '%F1%' OR 
    title LIKE '%Formula%' OR 
    title LIKE '%Champions League%' OR 
    title LIKE '%Wimbledon%' OR 
    title LIKE '%El Clásico%' OR 
    title LIKE '%Clásico%' OR 
    title LIKE '%NFL%' OR 
    title LIKE '%Bundesliga%' OR 
    title LIKE '%UFC%' OR 
    title LIKE '%Grand Prix%' OR 
    title LIKE '%Final%' OR
    title LIKE '%Bayern%' OR
    title LIKE '%Dortmund%' OR
    title LIKE '%Real Madrid%' OR
    title LIKE '%Barcelona%'
    ORDER BY event_date");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.8" />
    <title>Sports | TicketGeek</title>
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
        <section class="hero" style="background: linear-gradient(rgba(10, 10, 35, 0.6), rgba(10, 10, 35, 0.6)), url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=1200&h=500&fit=crop') center/cover;">
            <h2>Sports</h2>
            <p>Catch the biggest sporting events around the world</p>
        </section>

        <section class="main-layout" style="grid-template-columns: 1fr;">
        <div class="left-genres">
            <div class="event-row">
                <h2 class="section-title" style="text-align: center;">Upcoming Sports Events</h2>
                <?php if (empty($events)): ?>
                    <p>No sports events available at the moment.</p>
                <?php else: ?>
                <div class="event-list" style="flex-wrap: wrap; overflow: visible;">
                    <?php foreach ($events as $event): ?>
                    <a href="event.php?id=<?php echo $event['id']; ?>" class="event-card">
                        <div class="event-card-media">
                            <img class="event-card-img" src="<?php echo !empty($event['image_url']) ? htmlspecialchars($event['image_url']) : 'https://picsum.photos/id/240/300/200'; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>"/>
                            <span class="buy-button-overlay">Buy Tickets</span>
                        </div>
                        <div class="event-card-info">
                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                            <p><?php echo date('d M Y', strtotime($event['event_date'])); ?> | <?php echo htmlspecialchars($event['location']); ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        </section>
    </div>

    <footer>
        <p>© 2025-2026 TicketGeek</p>
        <a href="AboutUs.php">About Us</a> | <a href="FAQ.php">FAQ</a> | <a href="ContactUs.php">Contact Us</a>
    </footer>

</body>

</html>
