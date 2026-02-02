<?php
require_once 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login.php");
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST['submit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    
    $sql = "UPDATE users SET name=:name, email=:email, role=:role WHERE id=:id";
    $params = [':name' => $name, ':email' => $email, ':role' => $role, ':id' => $id];
    
    if (!empty($_POST['password'])) {
        $sql = "UPDATE users SET name=:name, email=:email, password=:password, role=:role WHERE id=:id";
        $params[':password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: admin_users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
    <style>.form-container { max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; }</style>
</head>
<body>
    <div class="form-container">
        <h2>Edit User</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <div class="input-group">
                <label>Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="auth-input">
            </div>
            <div class="input-group">
                <label>Role</label>
                <select name="role" class="auth-input">
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <button type="submit" name="submit" class="auth-submit-btn">Update User</button>
        </form>
        <br>
        <a href="admin_users.php">Back to Users</a>
    </div>
</body>
</html>
