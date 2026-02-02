 <?php
session_start();
require_once 'AuthService.php';
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->query("SELECT t.*, u.name as user_name, u.email, e.title as event_title, e.event_date 
                      FROM tickets t 
                      JOIN users u ON t.user_id = u.id 
                      JOIN events e ON t.event_id = e.id 
                      ORDER BY t.purchased_at DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT COUNT(*) as count, SUM(total_price) as total FROM tickets");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Orders - TicketGeek</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            background: #0a0a23;
            color: white;
        }
        .admin-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .admin-header h1 {
            color: white;
        }
        .back-link {
            color: white;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            min-width: 150px;
        }
        .stat-card h3 {
            margin: 0;
            color: #0a0a23;
            font-size: 14px;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #0a0a23;
        }
        .stat-card.revenue .number {
            color: #e74c3c;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .orders-table th {
            background: #0a0a23;
            color: white;
            font-weight: bold;
        }
        .orders-table td {
            color: #333;
        }
        .orders-table tr:hover {
            background: #f7f7f7;
        }
        .empty-state {
            text-align: center;
            padding: 50px;
            color: white;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .filter-bar label {
            color: #0a0a23;
            font-weight: bold;
        }
        .filter-bar select,
        .filter-bar input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filter-bar button {
            padding: 8px 20px;
            background: #0a0a23;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-content">
            <div class="left-nav">
                <a href="index.php" class="logo">TicketGeek</a>
            </div>

            <div class="right-nav">
                <span class="user-icon">
                    Admin: <?php echo htmlspecialchars($_SESSION["name"] ?? $_SESSION["email"]); ?>
                </span>
                <a href="logout.php" class="user-icon" style="margin-left: 10px; font-size: 0.9em;">Logout</a>
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
            <span class="user-icon mobile-only">
                Admin: <?php echo htmlspecialchars($_SESSION["name"] ?? $_SESSION["email"]); ?>
            </span>
            <a href="logout.php" class="user-icon mobile-only">Logout</a>
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

    <div class="admin-container">
        <div class="admin-header">
            <h1>Order Management</h1>
            <a href="admin_panel.php" class="back-link">← Back to Dashboard</a>
        </div>
        
        <div class="stats-bar">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?php echo $stats['count']; ?></div>
            </div>
            <div class="stat-card revenue">
                <h3>Total Revenue</h3>
                <div class="number">$<?php echo number_format($stats['total'] ?? 0, 2); ?></div>
            </div>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <h3>No orders yet</h3>
                <p>Orders will appear here when customers purchase tickets.</p>
            </div>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Event</th>
                        <th>Event Date</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Purchase Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['ticket_number']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['user_name']); ?><br>
                                <small style="color: #999;"><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($order['event_title']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['event_date'])); ?></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['purchased_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>
