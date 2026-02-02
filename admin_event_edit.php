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
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST['submit'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $image_url = $_POST['image_url'];

    $sql = "UPDATE events SET title=:title, description=:description, event_date=:date, location=:location, image_url=:image_url WHERE id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title, 
        ':description' => $description, 
        ':date' => $date, 
        ':location' => $location,
        ':image_url' => $image_url,
        ':id' => $id
    ]);

    header("Location: admin_events.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link rel="stylesheet" href="style.css">
    <style>.form-container { max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; }</style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Event</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $event['id'] ?>">
            <div class="input-group">
                <label>Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Description</label>
                <textarea name="description" class="auth-input" rows="3"><?= htmlspecialchars($event['description']) ?></textarea>
            </div>
            <div class="input-group">
                <label>Date & Time</label>
                <input type="datetime-local" name="date" value="<?= date('Y-m-d\TH:i', strtotime($event['event_date'])) ?>" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Image URL</label>
                <input type="text" name="image_url" value="<?= htmlspecialchars($event['image_url']) ?>" class="auth-input">
            </div>
            <button type="submit" name="submit" class="auth-submit-btn">Update Event</button>
        </form>
        <br>
        <a href="admin_events.php">Back to Events</a>
    </div>
</body>
</html>
