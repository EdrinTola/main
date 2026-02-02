<?php
session_start();
require_once 'db_connection.php';

$message = "";
$messageType = "";

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['name'] ?? '';
$userEmail = $_SESSION['email'] ?? '';

if ($isLoggedIn) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $userName = $user['name'];
            $userEmail = $user['email'];
        }
    } catch (Exception $e) {
    }
}

function ensureContactTableExists($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'replied') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$isLoggedIn) {
        $message = "You must be logged in to send a message.";
        $messageType = "error";
    } else {
        $subject = trim($_POST['subject'] ?? '');
        $messageText = trim($_POST['message'] ?? '');
        
        if (empty($subject) || empty($messageText)) {
            $message = "Please fill in all fields.";
            $messageType = "error";
        } else {
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                ensureContactTableExists($conn);
                
                $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, name, email, subject, message) VALUES (:user_id, :name, :email, :subject, :message)");
                $stmt->execute([
                    ':user_id' => $_SESSION['user_id'],
                    ':name' => $userName,
                    ':email' => $userEmail,
                    ':subject' => $subject,
                    ':message' => $messageText
                ]);
                
                $message = "Thank you! Your message has been sent successfully.";
                $messageType = "success";
                $messageText = '';
            } catch (PDOException $e) {
                $message = "Error: Could not send message. Please try again. (" . $e->getMessage() . ")";
                $messageType = "error";
                error_log("Contact form error: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.8" />
    <title>TicketGeek - Contact Us</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .page-content {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #0a0a23;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input[readonly] {
            background: #f5f5f5;
            cursor: not-allowed;
        }
        .form-group textarea {
            height: 150px;
            resize: vertical;
        }
        .submit-btn {
            background: #0a0a23;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .submit-btn:hover {
            background: #1a1a3a;
        }
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .contact-info {
            margin-top: 30px;
            padding: 20px;
            background: #f7f7f7;
            border-radius: 4px;
        }
        .contact-info h3 {
            margin-top: 0;
        }
        .login-prompt {
            text-align: center;
            padding: 40px 20px;
        }
        .login-prompt h2 {
            color: #0a0a23;
            margin-bottom: 20px;
        }
        .login-prompt p {
            margin-bottom: 30px;
            color: #666;
        }
        .login-prompt a {
            display: inline-block;
            background: #0a0a23;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
        }
        .login-prompt a:hover {
            background: #1a1a3a;
        }
        .user-badge {
            background: #e8f4ff;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 25px;
            border-left: 4px solid #0a0a23;
        }
        .user-badge strong {
            color: #0a0a23;
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
        <section class="page-content">
            <h1>Contact Us</h1>
            
            <?php if(!$isLoggedIn): ?>
                <div class="login-prompt">
                    <h2>Please Log In</h2>
                    <p>You need to be logged in to send us a message. This helps us respond to you quickly and securely.</p>
                    <a href="Login.php">Log In / Sign Up</a>
                </div>
            <?php else: ?>
                
                <?php if($message): ?>
                    <div class="alert <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="user-badge">
                    <strong>Logged in as:</strong> <?php echo htmlspecialchars($userName); ?> (<?php echo htmlspecialchars($userEmail); ?>)
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" value="<?php echo htmlspecialchars($userName); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($userEmail); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject" required>
                            <option value="">Select a topic</option>
                            <option value="General Inquiry" <?php echo (($_POST['subject'] ?? '') == 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                            <option value="Ticket Issue" <?php echo (($_POST['subject'] ?? '') == 'Ticket Issue') ? 'selected' : ''; ?>>Ticket Issue</option>
                            <option value="Refund Request" <?php echo (($_POST['subject'] ?? '') == 'Refund Request') ? 'selected' : ''; ?>>Refund Request</option>
                            <option value="Technical Support" <?php echo (($_POST['subject'] ?? '') == 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                            <option value="Partnership" <?php echo (($_POST['subject'] ?? '') == 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                            <option value="Other" <?php echo (($_POST['subject'] ?? '') == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required placeholder="How can we help you?"><?php echo htmlspecialchars($messageText ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
                
            <?php endif; ?>
            
            <div class="contact-info">
                <h3>Other Ways to Reach Us</h3>
                <p><strong>Email:</strong> support@ticketgeek.com</p>
                <p><strong>Phone:</strong> +1 (800) 123-4567</p>
                <p><strong>Hours:</strong> Monday - Friday, 9am - 6pm EST</p>
            </div>
        </section>
    </div>

    <footer>
        <p>© 2025-2026 TicketGeek</p>
        <a href="AboutUs.php">About Us</a> | <a href="FAQ.php">FAQ</a> | <a href="ContactUs.php">Contact Us</a>
    </footer>

</body>
</html>
