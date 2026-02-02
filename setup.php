<?php

require_once 'db_connection.php';

echo "<h1>TicketGeek Database Setup</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<p>✓ Database connected successfully</p>";
    
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone_number VARCHAR(20),
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "<p>✓ Users table created</p>";
    
    $sql = "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        event_date DATETIME NOT NULL,
        location VARCHAR(255) NOT NULL,
        image_url VARCHAR(500),
        price DECIMAL(10, 2) DEFAULT 0.00,
        capacity INT DEFAULT 100,
        available_tickets INT DEFAULT 100,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "<p>✓ Events table created</p>";
    
    $sql = "CREATE TABLE IF NOT EXISTS tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        event_id INT NOT NULL,
        ticket_number VARCHAR(50) NOT NULL UNIQUE,
        quantity INT DEFAULT 1,
        total_price DECIMAL(10, 2) NOT NULL,
        status ENUM('active', 'cancelled', 'used') DEFAULT 'active',
        purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "<p>✓ Tickets table created</p>";
    
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
    echo "<p>✓ Contact messages table created</p>";
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => 'admin@ticketgeek.com']);
    
    if ($stmt->rowCount() == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $stmt->execute([
            ':name' => 'Administrator',
            ':email' => 'admin@ticketgeek.com',
            ':password' => $hashedPassword,
            ':role' => 'admin'
        ]);
        echo "<p>✓ Admin user created</p>";
    } else {
        echo "<p>✓ Admin user already exists</p>";
    }
    
    $events = [
        ['Taylor Swift - Eras Tour', 'Experience Taylor Swift journey through all her musical eras!', '2026-08-15 20:00:00', 'Wembley Stadium, London', 150.00, 90000, 85000],
        ['The Weeknd - After Hours Til Dawn', 'Abel Tesfaye brings his critically acclaimed tour to Paris.', '2026-09-20 20:00:00', 'Stade de France, Paris', 125.00, 80000, 78000],
    ];
    
    $eventsInserted = 0;
    foreach ($events as $event) {
        $stmt = $conn->prepare("SELECT id FROM events WHERE title = :title");
        $stmt->execute([':title' => $event[0]]);
        
        if ($stmt->rowCount() == 0) {
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, location, price, capacity, available_tickets) VALUES (:title, :description, :event_date, :location, :price, :capacity, :available_tickets)");
            $stmt->execute([
                ':title' => $event[0],
                ':description' => $event[1],
                ':event_date' => $event[2],
                ':location' => $event[3],
                ':price' => $event[4],
                ':capacity' => $event[5],
                ':available_tickets' => $event[6]
            ]);
            $eventsInserted++;
        }
    }
    
    if ($eventsInserted > 0) {
        echo "<p>✓ $eventsInserted sample events inserted</p>";
    } else {
        echo "<p>✓ Events already exist (no duplicates added)</p>";
    }
    
    echo "<hr>";
    echo "<h2>Setup Complete!</h2>";
    echo "<p><strong>Admin Login:</strong></p>";
    echo "<ul>";
    echo "<li>Email: admin@ticketgeek.com</li>";
    echo "<li>Password: admin123</li>";
    echo "</ul>";
    echo "<p><a href='Login.php'>Go to Login Page</a></p>";
    echo "<p><a href='ContactUs.php'>Test Contact Form</a></p>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM events");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>Total events in database: <strong>$count</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
