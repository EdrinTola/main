<?php
require_once 'AuthService.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login.php");
    exit;
}

require_once 'db_connection.php';
$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->query("SELECT COUNT(*) as count FROM tickets");
$totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $conn->query("SELECT t.*, u.name as user_name, u.email, e.title as event_title 
                      FROM tickets t 
                      JOIN users u ON t.user_id = u.id 
                      JOIN events e ON t.event_id = e.id 
                      ORDER BY t.purchased_at DESC LIMIT 5");
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT SUM(total_price) as total FROM tickets");
$totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - TicketGeek</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #0a0a23;
            color: white;
        }
        .admin-container { 
            padding: 50px; 
        }
        .dashboard-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px;
        }
        .dashboard-header h2 {
            color: white;
        }
        .admin-container h3 {
            color: #0a0a23;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <a href="index.php" class="logo">TicketGeek</a>
            <div class="right-nav">
                <span class="user-icon">Admin: <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="user-icon" style="margin-left: 10px;">Logout</a>
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
            <span class="user-icon mobile-only">Admin: <?php echo htmlspecialchars($_SESSION['name']); ?></span>
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
        <div class="dashboard-header">
            <h2>Admin Dashboard</h2>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-row" style="display: flex; gap: 20px; margin-bottom: 30px;">
            <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; flex: 1; text-align: center; color: #0a0a23;">
                <h3 style="margin: 0; font-size: 14px; color: #666;">Total Orders</h3>
                <div style="font-size: 36px; font-weight: bold; color: #0a0a23;"><?php echo $totalOrders; ?></div>
            </div>
            <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; flex: 1; text-align: center; color: #0a0a23;">
                <h3 style="margin: 0; font-size: 14px; color: #666;">Total Revenue</h3>
                <div style="font-size: 36px; font-weight: bold; color: #e74c3c;">$<?php echo number_format($totalRevenue, 2); ?></div>
            </div>
        </div>
        
        <!-- Recent Orders Table -->
        <div style="background: white; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; color: #0a0a23;">Recent Orders</h3>
                <a href="admin_orders.php" style="color: #0a0a23; text-decoration: none;">View All &rarr;</a>
            </div>
            <?php if (empty($recentOrders)): ?>
                <p style="color: #666; text-align: center; padding: 20px;">No orders yet.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee;">
                            <th style="padding: 12px; text-align: left; color: #0a0a23;">Order #</th>
                            <th style="padding: 12px; text-align: left; color: #0a0a23;">Customer</th>
                            <th style="padding: 12px; text-align: left; color: #0a0a23;">Event</th>
                            <th style="padding: 12px; text-align: left; color: #0a0a23;">Qty</th>
                            <th style="padding: 12px; text-align: left; color: #0a0a23;">Total</th>
                            <th style="padding: 12px; text-align: left; color: #0a0a23;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px; color: #333;"><?php echo htmlspecialchars($order['ticket_number']); ?></td>
                                <td style="padding: 12px; color: #333;"><?php echo htmlspecialchars($order['user_name']); ?><br><small style="color: #999;"><?php echo htmlspecialchars($order['email']); ?></small></td>
                                <td style="padding: 12px; color: #333;"><?php echo htmlspecialchars($order['event_title']); ?></td>
                                <td style="padding: 12px; color: #333;"><?php echo $order['quantity']; ?></td>
                                <td style="padding: 12px; color: #333;"><?php echo date('M d, Y H:i', strtotime($order['purchased_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 20px;">
            <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; flex: 1; text-align: center; background: white;">
                <h3 style="color: #0a0a23;">User Management</h3>
                <p style="color: #666;">Add, Edit, View, or Delete users.</p>
                <a href="admin_users.php" class="auth-submit-btn" style="display: inline-block; width: auto; text-decoration: none;">Manage Users</a>
            </div>
            
            <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; flex: 1; text-align: center; background: white;">
                <h3 style="color: #0a0a23;">Event Management</h3>
                <p style="color: #666;">Manage concert and sport events.</p>
                <a href="admin_events.php" class="auth-submit-btn" style="display: inline-block; width: auto; text-decoration: none;">Manage Events</a>
            </div>
            
            <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; flex: 1; text-align: center; background: white;">
                <h3 style="color: #0a0a23;">Contact Messages</h3>
                <p style="color: #666;">View and manage contact form submissions.</p>
                <a href="admin_messages.php" class="auth-submit-btn" style="display: inline-block; width: auto; text-decoration: none;">View Messages</a>
            </div>
        </div>
    </div>
</body>
</html>
