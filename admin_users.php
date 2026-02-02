<?php
require_once 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login.php");
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: admin_users.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #0a0a23;
            color: white;
        }
        .admin-container { 
            padding: 50px; 
            max-width: 1200px;
            margin: 0 auto;
        }
        .admin-container h2 {
            color: white;
        }
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .data-table th, .data-table td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        .data-table th { 
            background-color: #0a0a23; 
            color: white;
        }
        .data-table td {
            color: #333;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn-action { 
            text-decoration: none; 
            padding: 5px 10px; 
            border-radius: 4px; 
            color: white; 
            margin-right: 5px; 
        }
        .btn-edit { background-color: #4CAF50; }
        .btn-delete { background-color: #f44336; }
        .btn-create { 
            background-color: #0a0a23; 
            padding: 10px 15px; 
            text-decoration: none; 
            color: white; 
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <a href="index.php" class="logo">TicketGeek</a>
            <div class="right-nav">
                <a href="admin_panel.php" class="user-icon">Back to Dashboard</a>
                <a href="logout.php" class="user-icon">Logout</a>
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
            <a href="admin_panel.php" class="user-icon">Back to Dashboard</a>
            <a href="logout.php" class="user-icon">Logout</a>
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
        <h2>Manage Users</h2>
        <a href="admin_user_create.php" class="btn-create">Add New User</a>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <a href="admin_user_edit.php?id=<?= $user['id'] ?>" class="btn-action btn-edit">Edit</a>
                        <a href="admin_users.php?delete_id=<?= $user['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
