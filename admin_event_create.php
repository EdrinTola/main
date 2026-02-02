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
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $image_url = $_POST['image_url'];

    $sql = "INSERT INTO events (title, description, event_date, location, image_url) VALUES (:title, :description, :date, :location, :image_url)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title, 
        ':description' => $description, 
        ':date' => $date, 
        ':location' => $location,
        ':image_url' => $image_url
    ]);

    header("Location: admin_events.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Event</title>
    <link rel="stylesheet" href="style.css">
    <style>.form-container { max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; }</style>
</head>
<body>
    <script>
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    </script>
    <div class="form-container">
        <h2>Add New Event</h2>
        <form method="POST">
            <div class="input-group">
                <label>Title</label>
                <input type="text" name="title" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Description</label>
                <textarea name="description" class="auth-input" rows="3"></textarea>
            </div>
            <div class="input-group">
                <label>Date & Time</label>
                <input type="datetime-local" name="date" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Location</label>
                <input type="text" name="location" class="auth-input" required>
            </div>
            <div class="input-group">
                <label>Image URL</label>
                <input type="text" name="image_url" class="auth-input">
            </div>
            <button type="submit" name="submit" class="auth-submit-btn">Save Event</button>
        </form>
        <br>
        <a href="admin_events.php">Back to Events</a>
    </div>
</body>
</html>
