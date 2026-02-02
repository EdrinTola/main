<?php
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Event not found. Please select an event from the <a href='index.php'>homepage</a>.");
}

$event_id = $_GET['id'];

require_once 'db_connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        die("Event not found (ID: " . htmlspecialchars($event_id) . "). Please select an event from the <a href='index.php'>homepage</a>.");
    }
    
    
} catch (Exception $e) {
    die("Error loading event: " . $e->getMessage());
}

$event_date = new DateTime($event['event_date']);
$formatted_date = $event_date->format('d F Y');
$formatted_time = $event_date->format('H:i');
$day_of_week = $event_date->format('l');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.8" />
    <title><?php echo htmlspecialchars($event['title']); ?> | TicketGeek</title>
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
                        <?php echo 'Hello, ' . htmlspecialchars($_SESSION["name"] ?? $_SESSION["email"]); ?>
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
                    <?php echo 'Hello, ' . htmlspecialchars($_SESSION["name"] ?? $_SESSION["email"]); ?>
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
        
        <section class="detail-header" style="background: linear-gradient(to top, rgba(10, 10, 35, 0.8), rgba(10, 10, 35, 0.4)), url('<?php echo !empty($event['image_url']) ? htmlspecialchars($event['image_url']) : 'https://picsum.photos/id/240/1200/500'; ?>') center/cover;">
            <p><?php echo htmlspecialchars($formatted_date); ?> | <?php echo htmlspecialchars($event['location']); ?></p>
            <h1><?php echo htmlspecialchars($event['title']); ?></h1>
            <p><?php echo htmlspecialchars($event['location']); ?></p>
        </section>

        <section class="detail-layout">

            <div class="detail-content">
                
                <div class="info-section">
                    <h3>Event Description</h3>
                    <p>
                        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                    </p>
                </div>

                <div class="info-section">
                    <h3>Venue Details </h3>
                    <div class="venue-details">
                        <div>
                            <strong>Venue:</strong> <?php echo htmlspecialchars($event['location']); ?><br>
                            <strong>Date:</strong> <?php echo htmlspecialchars($formatted_date); ?><br>
                            <strong>Time:</strong> <?php echo htmlspecialchars($formatted_time); ?>
                        </div>
                        <div>
                            <strong>Available Tickets:</strong> <?php echo htmlspecialchars($event['available_tickets']); ?><br>
                            <strong>Capacity:</strong> <?php echo htmlspecialchars($event['capacity']); ?>
                        </div>
                    </div>
                </div>

                <div class="info-section">
                    <h3>Ticket Notes</h3>
                    <ul>
                        <li>All ticket prices are subject to a service fee and tax.</li>
                        <li>Tickets are delivered via mobile app 48 hours before the event.</li>
                        <li>No professional cameras or recording devices allowed.</li>
                    </ul>
                </div>

            </div>

            <div class="ticket-box">
                <h3>Get Your Tickets</h3>
                <p>Date: <b><?php echo htmlspecialchars($day_of_week . ', ' . $formatted_date); ?></b></p>
                <p>Location: <b><?php echo htmlspecialchars($event['location']); ?></b></p>
                <hr style="border-top: 1px solid #444; margin: 15px 0;">
                
                <p>Lowest Price From:</p>
                <div class="ticket-price">€<?php echo number_format($event['price'], 2); ?></div>
                
                <a href="view_tickets.php?event_id=<?php echo $event['id']; ?>" class="buy-now-btn">VIEW ALL TICKETS</a>

                <p style="font-size: 12px; margin-top: 20px; text-align: center;">
                    Tickets are guaranteed by the TicketGeek FanProtect Policy.
                </p>
            </div>
        </section>
        
    </div>

    <footer>
        <p>© 2025-2026 TicketGeek</p>
        <a href="AboutUs.php">About Us</a> | <a href="FAQ.php">FAQ</a> | <a href="ContactUs.php">Contact Us</a>
    </footer>

</body>
</html>
