<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $messageId = $_POST['message_id'] ?? 0;
    
    if ($action == 'mark_read' && $messageId) {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = :id");
        $stmt->execute([':id' => $messageId]);
    } elseif ($action == 'delete' && $messageId) {
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = :id");
        $stmt->execute([':id' => $messageId]);
    }
}

$stmt = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'");
$newCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Contact Messages</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            background: #0a0a23;
            color: white;
        }
        .admin-container {
            max-width: 1200px;
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
        .stat-card.new .number {
            color: #e74c3c;
        }
        .messages-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .messages-table th,
        .messages-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .messages-table th {
            background: #0a0a23;
            color: white;
            font-weight: bold;
        }
        .messages-table td {
            color: #333;
        }
        .messages-table tr:hover {
            background: #f7f7f7;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-badge.new {
            background: #e74c3c;
            color: white;
        }
        .status-badge.read {
            background: #f39c12;
            color: white;
        }
        .status-badge.replied {
            background: #27ae60;
            color: white;
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        .action-btn.view {
            background: #3498db;
            color: white;
        }
        .action-btn.read {
            background: #f39c12;
            color: white;
        }
        .action-btn.delete {
            background: #e74c3c;
            color: white;
        }
        .message-content {
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .empty-state {
            text-align: center;
            padding: 50px;
            color: white;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        .modal-meta {
            color: #666;
            font-size: 14px;
        }
        .modal-meta p {
            margin: 5px 0;
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

    <!-- Mobile Navigation Overlay -->
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
            <h1>Contact Messages</h1>
            <a href="admin_panel.php" class="back-link">← Back to Admin Panel</a>
        </div>
        
        <div class="stats-bar">
            <div class="stat-card new">
                <h3>New Messages</h3>
                <div class="number"><?php echo $newCount; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Messages</h3>
                <div class="number"><?php echo count($messages); ?></div>
            </div>
        </div>
        
        <?php if (empty($messages)): ?>
            <div class="empty-state">
                <h3>No messages yet</h3>
                <p>Contact form submissions will appear here.</p>
            </div>
        <?php else: ?>
            <table class="messages-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td>
                                <span class="status-badge <?php echo $msg['status']; ?>">
                                    <?php echo ucfirst($msg['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($msg['name']); ?></td>
                            <td><?php echo htmlspecialchars($msg['email']); ?></td>
                            <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                            <td class="message-content" title="<?php echo htmlspecialchars($msg['message']); ?>">
                                <?php echo htmlspecialchars($msg['message']); ?>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></td>
                            <td>
                                <button class="action-btn view" onclick="viewMessage(<?php echo $msg['id']; ?>)">View</button>
                                <?php if ($msg['status'] == 'new'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="action-btn read">Mark Read</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this message?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" class="action-btn delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Message Modal -->
    <div class="modal" id="messageModal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 id="modalSubject" style="margin: 0;"></h2>
                    <div class="modal-meta">
                        <p><strong>From:</strong> <span id="modalName"></span> (<span id="modalEmail"></span>)</p>
                        <p><strong>Date:</strong> <span id="modalDate"></span></p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="modalMessage" style="line-height: 1.6; white-space: pre-wrap;"></div>
        </div>
    </div>

    <script>
        const messages = <?php echo json_encode($messages); ?>;
        
        function viewMessage(id) {
            const msg = messages.find(m => m.id === id);
            if (msg) {
                document.getElementById('modalSubject').textContent = msg.subject;
                document.getElementById('modalName').textContent = msg.name;
                document.getElementById('modalEmail').textContent = msg.email;
                document.getElementById('modalDate').textContent = new Date(msg.created_at).toLocaleString();
                document.getElementById('modalMessage').textContent = msg.message;
                document.getElementById('messageModal').classList.add('active');
            }
        }
        
        function closeModal() {
            document.getElementById('messageModal').classList.remove('active');
        }
        
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

</body>
</html>
