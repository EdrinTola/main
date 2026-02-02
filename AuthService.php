<?php
require_once 'db_connection.php';

class AuthService {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
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

        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            return "Email already exists.";
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'user')";
        $stmt = $this->conn->prepare($sql);

        if ($stmt->execute([':name' => $name, ':email' => $email, ':password' => $hashedPassword])) {
            return "REGISTER_SUCCESS";
        }
        
        return "REGISTER_FAILED";
    }

    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return "Email and password are required.";
        }

        $stmt = $this->conn->prepare("SELECT id, name, password, role FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                return "LOGIN_SUCCESS";
            }
        }

        return "INVALID_CREDENTIALS";
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
    }

    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}
?>
