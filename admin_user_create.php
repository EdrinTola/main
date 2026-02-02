<?php
require_once 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login.php");
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':name' => $name, ':email' => $email, ':password' => $password, ':role' => $role]);

    header("Location: admin_users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <link rel="stylesheet" href="style.css">
    <style>.form-container { max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; }</style>
</head>
<body>
    <div class="form-container">
        <h2>Add New User</h2>
        <form method="POST">
            <div class="input-group">
                <label>Name</label>
                <input type="text" name="name" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Role</label>
                <select name="role" class="auth-input">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="submit" class="auth-submit-btn">Save User</button>
        </form>
        <br>
        <a href="admin_users.php">Back to Users</a>
    </div>
</body>
</html>
