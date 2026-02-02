<?php
require_once 'db_connection.php'; 

class User {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register($name, $email, $password, $confirmPassword) {
        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
            return "All fields are required.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }

        if ($password !== $confirmPassword) {
            return "Passwords do not match.";
        }

        $checkQuery = "SELECT id FROM users WHERE email = :email";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([':email' => $email]);

        if ($checkStmt->rowCount() > 0) {
            return "Email already exists.";
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';

        $query = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $this->conn->prepare($query);
        
        $params = [
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => $role
        ];

        return $stmt->execute($params) ? "Registration successful." : "Error: Unable to register.";
    }

    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return "Please fill in both fields.";
        }

        $query = "SELECT id, password, role FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $row['role'];

                return $row['role'] === 'admin' ? "admin_panel.php" : "index.php";
            }
        }
        return "Invalid email or password.";
    }

    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        return "Logged out successfully.";
    }

    public function getUserById($id) {
        $query = "SELECT id, name, email, role, created_at FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
