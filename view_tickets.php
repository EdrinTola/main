<?php
session_start();
$purchaseSuccess = false;
$purchaseError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'purchase') {
    $event_id = $_POST['event_id'] ?? null;
    
    if (!$event_id) {
        $purchaseError = "Event not found.";
    } else {
        require_once 'db_connection.php';
        
        if (!isset($_SESSION['user_id'])) {
            $purchaseError = "You must be logged in to purchase tickets.";
        } else {
            try {
                $db = new Database();
                $conn = $db->getConnection();
                

                $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
                $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);
                $stmt->execute();
                $event = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$event) {
                    $purchaseError = "Event not found.";
                } else {
                    $ticket_type = $_POST['ticket_type'];
                    $quantity = (int)$_POST['quantity'];
                    $price_per_ticket = (float)$_POST['price'];
                    
                    $subtotal = $price_per_ticket * $quantity;
                    $service_fee = $subtotal * 0.10;
                    $total_price = $subtotal + $service_fee;
                    
                    $ticket_number = 'TKT-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
                    
                    $stmt = $conn->prepare("INSERT INTO tickets (user_id, event_id, ticket_number, quantity, total_price, status) VALUES (:user_id, :event_id, :ticket_number, :quantity, :total_price, 'active')");
                    $stmt->execute([
                        ':user_id' => $_SESSION['user_id'],
                        ':event_id' => $event_id,
                        ':ticket_number' => $ticket_number,
                        ':quantity' => $quantity,
                        ':total_price' => $total_price
                    ]);
                    
                    $stmt = $conn->prepare("UPDATE events SET available_tickets = available_tickets - :qty WHERE id = :id");
                    $stmt->execute([':qty' => $quantity, ':id' => $event_id]);
                    
                    $purchaseSuccess = true;
                    $lastTicketNumber = $ticket_number;
                    $lastTotal = $total_price;
                    $lastQuantity = $quantity;
                    $lastTicketType = $ticket_type;
                }
            } catch (Exception $e) {
                $purchaseError = "Error processing purchase: " . $e->getMessage();
            }
        }
    }
}

$event_id = $_GET['event_id'] ?? ($_POST['event_id'] ?? null);

if (!$event_id) {
    die("Event not found. Please select an event from the <a href='index.php'>homepage</a>.");
}

require_once 'db_connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        die("Event not found. Please select an event from the <a href='index.php'>homepage</a>.");
    }
    
} catch (Exception $e) {
    die("Error loading event: " . $e->getMessage());
}

$event_date = new DateTime($event['event_date']);
$formatted_date = $event_date->format('d F Y');
$formatted_time = $event_date->format('H:i');
$day_of_week = $event_date->format('l');

$isLoggedIn = isset($_SESSION['user_id']);

$ticketTypes = [
    [
        'name' => 'VIP Front Row',
        'description' => 'Best seats in the house! Front row access with premium benefits',
        'price' => $event['price'] * 3,
        'features' => ['Front row seats', 'Meet & Greet access', 'Exclusive merchandise', 'Priority entry'],
        'color' => '#FFD700',
        'available' => min(10, floor($event['available_tickets'] * 0.05))
    ],
    [
        'name' => 'VIP',
        'description' => 'Premium seating with exclusive benefits',
        'price' => $event['price'] * 2,
        'features' => ['Premium seating', 'Access to VIP lounge', 'Complimentary drinks', 'Early entry'],
        'color' => '#C0C0C0',
        'available' => min(50, floor($event['available_tickets'] * 0.1))
    ],
    [
        'name' => 'Floor',
        'description' => 'General admission standing/floor tickets',
        'price' => $event['price'] * 1.5,
        'features' => ['Floor access', 'Standing room', 'Close to stage'],
        'color' => '#CD7F32',
        'available' => min(200, floor($event['available_tickets'] * 0.25))
    ],
    [
        'name' => 'Lower Bowl',
        'description' => 'Lower section seating with great view',
        'price' => $event['price'] * 1.2,
        'features' => ['Lower bowl seating', 'Good view of stage', 'Comfortable seating'],
        'color' => '#4169E1',
        'available' => min(300, floor($event['available_tickets'] * 0.3))
    ],
    [
        'name' => 'Upper Bowl',
        'description' => 'Upper section seating at great value',
        'price' => $event['price'],
        'features' => ['Upper bowl seating', 'Full event view', 'Budget friendly'],
        'color' => '#32CD32',
        'available' => min($event['available_tickets'] - 560, $event['available_tickets'])
    ]
];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($event['title']); ?> - Tickets | TicketGeek</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .tickets-header {
            background: linear-gradient(to bottom, #0a0a23, #1a1a3a);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .tickets-header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .tickets-header p {
            font-size: 18px;
            opacity: 0.9;
        }
        .tickets-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .ticket-card {
            display: flex;
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .ticket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .ticket-badge {
            width: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            transform: rotate(180deg);
        }
        .ticket-info {
            flex: 1;
            padding: 25px;
        }
        .ticket-info h3 {
            margin: 0 0 10px 0;
            color: #0a0a23;
            font-size: 22px;
        }
        .ticket-info p {
            color: #666;
            margin: 0 0 15px 0;
        }
        .ticket-features {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }
        .ticket-features li {
            display: inline-block;
            background: #f0f0f0;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-right: 5px;
            margin-bottom: 5px;
            color: #555;
        }
        .ticket-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .ticket-price {
            font-size: 28px;
            font-weight: bold;
            color: #0a0a23;
        }
        .ticket-price span {
            font-size: 14px;
            font-weight: normal;
            color: #888;
        }
        .buy-btn {
            background: #0a0a23;
            color: white;
            padding: 12px 35px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .buy-btn:hover {
            background: #2a2a4a;
        }
        .availability {
            font-size: 13px;
            color: #888;
        }
        .availability.low {
            color: #e74c3c;
        }
        .event-nav {
            background: #f5f5f5;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
        }
        .event-nav a {
            color: #0a0a23;
            text-decoration: none;
            font-weight: 500;
        }
        .event-nav a:hover {
            text-decoration: underline;
        }
        .legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal {
            background: white;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlide 0.3s ease;
        }
        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .modal-header {
            background: #0a0a23;
            color: white;
            padding: 20px 25px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 20px;
        }
        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            line-height: 1;
        }
        .modal-body {
            padding: 25px;
        }
        .modal-event-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .modal-event-info h4 {
            margin: 0 0 5px 0;
            color: #0a0a23;
        }
        .modal-event-info p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #0a0a23;
        }
        .quantity-selector {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .quantity-btn:hover {
            border-color: #0a0a23;
            background: #f5f5f5;
        }
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .quantity-display {
            font-size: 24px;
            font-weight: bold;
            width: 50px;
            text-align: center;
        }
        .card-input {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
        }
        .card-input:focus {
            outline: none;
            border-color: #0a0a23;
        }
        .card-row {
            display: flex;
            gap: 15px;
        }
        .card-row .form-group {
            flex: 1;
        }
        .total-section {
            background: #f0f4ff;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .total-row.final {
            font-size: 20px;
            font-weight: bold;
            color: #0a0a23;
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        .purchase-btn {
            width: 100%;
            background: linear-gradient(135deg, #0a0a23, #2a2a4a);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .purchase-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(10, 10, 35, 0.3);
        }
        .purchase-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
            font-size: 13px;
            color: #666;
        }
        .field-error {
            display: block;
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
            min-height: 18px;
        }
        .card-input.error {
            border-color: #dc3545;
        }
        .login-prompt {
            text-align: center;
            padding: 30px;
        }
        .login-prompt a {
            color: #0a0a23;
            font-weight: bold;
        }
        .success-message {
            text-align: center;
            padding: 20px;
        }
        .success-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .ticket-card {
                flex-direction: column;
            }
            .ticket-badge {
                width: 100%;
                writing-mode: horizontal-tb;
                transform: none;
                padding: 15px;
            }
            .ticket-action {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            .buy-btn {
                text-align: center;
            }
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

    <div class="event-nav">
        <div style="max-width: 1000px; margin: 0 auto;">
            <a href="index.php">Home</a> > 
            <a href="event.php?id=<?php echo $event_id; ?>"><?php echo htmlspecialchars($event['title']); ?></a> > 
            <span>Select Tickets</span>
        </div>
    </div>

    <div class="tickets-header">
        <h1><?php echo htmlspecialchars($event['title']); ?></h1>
        <p><?php echo htmlspecialchars($day_of_week . ', ' . $formatted_date); ?> | <?php echo htmlspecialchars($event['location']); ?></p>
        <p style="font-size: 14px; margin-top: 10px;">Starting from <strong>€<?php echo number_format($event['price'], 2); ?></strong></p>
    </div>

    <div class="tickets-container">
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #FFD700;"></div>
                <span>VIP Front Row</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #C0C0C0;"></div>
                <span>VIP</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #CD7F32;"></div>
                <span>Floor</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #4169E1;"></div>
                <span>Lower Bowl</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #32CD32;"></div>
                <span>Upper Bowl</span>
            </div>
        </div>

        <?php foreach ($ticketTypes as $index => $ticket): ?>
        <div class="ticket-card">
            <div class="ticket-badge" style="background: <?php echo $ticket['color']; ?>;">
                <?php echo htmlspecialchars($ticket['name']); ?>
            </div>
            <div class="ticket-info">
                <h3><?php echo htmlspecialchars($ticket['name']); ?></h3>
                <p><?php echo htmlspecialchars($ticket['description']); ?></p>
                
                <ul class="ticket-features">
                    <?php foreach ($ticket['features'] as $feature): ?>
                    <li><?php echo htmlspecialchars($feature); ?></li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="ticket-action">
                    <div>
                        <div class="ticket-price">
                            €<?php echo number_format($ticket['price'], 2); ?>
                            <span>per ticket</span>
                        </div>
                        <div class="availability <?php echo $ticket['available'] < 20 ? 'low' : ''; ?>">
                            <?php echo $ticket['available']; ?> tickets available
                        </div>
                    </div>
                    <button class="buy-btn" onclick="openModal(<?php echo $index; ?>, '<?php echo htmlspecialchars($ticket['name']); ?>', <?php echo $ticket['price']; ?>, <?php echo min($ticket['available'], 4); ?>)">
                        Select Tickets
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <div class="modal-overlay" id="purchaseModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Complete Your Purchase</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modalContent">
                    <?php if(!$isLoggedIn): ?>
                        <div class="login-prompt">
                            <p>Please <a href="Login.php">log in</a> to purchase tickets.</p>
                        </div>
                    <?php else: ?>
                        <div class="modal-event-info">
                            <h4 id="modalEventTitle"><?php echo htmlspecialchars($event['title']); ?></h4>
                            <p id="modalEventDetails"><?php echo htmlspecialchars($day_of_week . ', ' . $formatted_date); ?> | <?php echo htmlspecialchars($event['location']); ?></p>
                        </div>

                        <form id="purchaseForm" method="POST" action="view_tickets.php" onsubmit="return processPurchase(event)">
                            <input type="hidden" name="action" value="purchase">
                            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                            <input type="hidden" id="ticketType" name="ticket_type">
                            <input type="hidden" id="ticketPrice" name="price">
                            <input type="hidden" id="quantityInput" name="quantity" value="1">
                            
                            <div class="form-group">
                                <label>Number of Tickets</label>
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                                    <span class="quantity-display" id="quantityDisplay">1</span>
                                    <button type="button" class="quantity-btn" onclick="updateQuantity(1)">+</button>
                                    <span style="margin-left: 15px; color: #666;">(Max 4)</span>
                                </div>
                            </div>

                            <div class="total-section">
                                <div class="total-row">
                                    <span><span id="ticketTypeDisplay">VIP</span> × <span id="quantityDisplay2">1</span></span>
                                    <span id="subtotal">€0.00</span>
                                </div>
                                <div class="total-row">
                                    <span>Service Fee (10%)</span>
                                    <span id="serviceFee">€0.00</span>
                                </div>
                                <div class="total-row final">
                                    <span>Total</span>
                                    <span id="totalPrice">€0.00</span>
                                </div>
                            </div>

                            <h4 style="margin-bottom: 15px; color: #0a0a23;">Payment Details</h4>
                            
                            <div class="form-group">
                                <label for="cardName">Name on Card</label>
                                <input type="text" id="cardName" class="card-input" placeholder="John Doe" required>
                            </div>

                            <div class="form-group">
                                <label for="cardNumber">Card Number</label>
                                <input type="text" id="cardNumber" class="card-input" placeholder="1234 5678 9012 3456" maxlength="19" required>
                                <span class="field-error" id="cardNumberError"></span>
                            </div>

                            <div class="card-row">
                                <div class="form-group">
                                    <label for="cardExpiry">Expiry Date</label>
                                    <input type="text" id="cardExpiry" class="card-input" placeholder="MM/YY" maxlength="5" required>
                                    <span class="field-error" id="cardExpiryError"></span>
                                </div>
                                <div class="form-group">
                                    <label for="cardCvv">CVV</label>
                                    <input type="text" id="cardCvv" class="card-input" placeholder="123" maxlength="3" required>
                                    <span class="field-error" id="cardCvvError"></span>
                                </div>
                            </div>

                            <button type="submit" class="purchase-btn" id="purchaseBtn">Complete Purchase</button>
                            
                            <div class="secure-badge">
                                Secure Payment | Your data is protected
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="successModal">
        <div class="modal">
            <div class="modal-header" style="background: #28a745;">
                <h2>Purchase Successful!</h2>
                <button class="modal-close" onclick="closeSuccessModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="success-message">
                    <div class="success-icon">✓</div>
                    <h3>Thank You for Your Purchase!</h3>
                    <p>Your tickets have been confirmed.</p>
                    <p style="color: #666; margin-top: 15px;">A confirmation email has been sent to your registered email address.</p>
                    <p style="margin-top: 20px;">
                        <strong>Order Details:</strong><br>
                        <?php if ($purchaseSuccess): ?>
                            <span id="successTickets"><?php echo $lastQuantity; ?>× <?php echo htmlspecialchars($lastTicketType); ?></span><br>
                            <span id="successTotal">Total: €<?php echo number_format($lastTotal, 2); ?></span><br>
                            <span style="color: #27ae60; font-size: 12px;">Order #: <?php echo htmlspecialchars($lastTicketNumber); ?></span>
                        <?php else: ?>
                            <span id="successTickets"></span><br>
                            <span id="successTotal"></span>
                        <?php endif; ?>
                    </p>
                    <button class="buy-btn" onclick="window.location.href='index.php'" style="margin-top: 20px;">
                        Back to Home
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2025-2026 TicketGeek</p>
        <a href="AboutUs.php">About Us</a> | <a href="FAQ.php">FAQ</a> | <a href="ContactUs.php">Contact Us</a>
    </footer>

    <script>
        let currentTicketType = '';
        let currentPrice = 0;
        let currentMaxQty = 4;
        let quantity = 1;

        function openModal(index, name, price, maxQty) {
            currentTicketType = name;
            currentPrice = price;
            currentMaxQty = Math.min(maxQty, 4);
            quantity = 1;
            
            document.getElementById('ticketType').value = name;
            document.getElementById('ticketPrice').value = price;
            document.getElementById('ticketTypeDisplay').textContent = name;
            document.getElementById('modalEventTitle').textContent = '<?php echo htmlspecialchars($event['title']); ?>';
            document.getElementById('modalEventDetails').textContent = '<?php echo htmlspecialchars($day_of_week . ', ' . $formatted_date); ?> | <?php echo htmlspecialchars($event['location']); ?>';
            
            updateTotals();
            document.getElementById('purchaseModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('purchaseModal').classList.remove('active');
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.remove('active');
        }

        function updateQuantity(change) {
            const newQty = quantity + change;
            if (newQty >= 1 && newQty <= currentMaxQty) {
                quantity = newQty;
                document.getElementById('quantityDisplay').textContent = quantity;
                document.getElementById('quantityDisplay2').textContent = quantity;
                document.getElementById('quantityInput').value = quantity;
                updateTotals();
            }
        }

        function updateTotals() {
            const subtotal = currentPrice * quantity;
            const serviceFee = subtotal * 0.10;
            const total = subtotal + serviceFee;
            
            document.getElementById('subtotal').textContent = '€' + subtotal.toFixed(2);
            document.getElementById('serviceFee').textContent = '€' + serviceFee.toFixed(2);
            document.getElementById('totalPrice').textContent = '€' + total.toFixed(2);
        }

        function clearErrors() {
            document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
            document.querySelectorAll('.card-input').forEach(el => el.classList.remove('error'));
        }
        
        function showError(fieldId, message) {
            document.getElementById(fieldId + 'Error').textContent = message;
            document.getElementById(fieldId).classList.add('error');
        }
        
        function processPurchase(event) {
            clearErrors();
            let hasErrors = false;
            
            const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
            if (cardNumber.length < 13 || cardNumber.length > 19) {
                showError('cardNumber', 'Please enter a valid card number (13-19 digits)');
                hasErrors = true;
            }
            
            const expiry = document.getElementById('cardExpiry').value;
            if (!expiry.match(/^\d{2}\/\d{2}$/)) {
                showError('cardExpiry', 'Please enter a valid expiry date (MM/YY)');
                hasErrors = true;
            } else {
                const [expMonth, expYear] = expiry.split('/').map(Number);
                const currentYear = new Date().getFullYear();
                const currentMonth = new Date().getMonth() + 1;
                
                if (expMonth < 1 || expMonth > 12) {
                    showError('cardExpiry', 'Please enter a valid month (01-12)');
                    hasErrors = true;
                } else if (expYear < (currentYear % 100)) {
                    showError('cardExpiry', 'Card has expired');
                    hasErrors = true;
                } else if (expYear === (currentYear % 100) && expMonth < currentMonth) {
                    showError('cardExpiry', 'Card has expired');
                    hasErrors = true;
                }
            }
            
            const cvv = document.getElementById('cardCvv').value;
            if (!cvv.match(/^\d{3}$/)) {
                showError('cardCvv', 'Please enter a valid 3-digit CVV');
                hasErrors = true;
            }
            
            if (hasErrors) {
                event.preventDefault();
                return false;
            }
            
            return true;
        }

        document.getElementById('cardNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) formatted += ' ';
                formatted += value[i];
            }
            e.target.value = formatted;
        });

        document.getElementById('cardExpiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                let month = parseInt(value.substring(0, 2));
                if (month > 12) month = 12;
                if (month < 1) month = 1;
                value = String(month).padStart(2, '0') + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
        
        document.getElementById('cardCvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
            clearFieldError('cardCvv');
        });
        
        document.getElementById('cardNumber').addEventListener('input', function() {
            clearFieldError('cardNumber');
        });
        
        document.getElementById('cardExpiry').addEventListener('input', function() {
            clearFieldError('cardExpiry');
        });
        
        function clearFieldError(fieldId) {
            document.getElementById(fieldId + 'Error').textContent = '';
            document.getElementById(fieldId).classList.remove('error');
        }

        document.getElementById('purchaseModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        document.getElementById('successModal').addEventListener('click', function(e) {
            if (e.target === this) closeSuccessModal();
        });
        
        <?php if ($purchaseSuccess): ?>
        document.getElementById('successModal').classList.add('active');
        <?php endif; ?>
    </script>

</body>
</html>
