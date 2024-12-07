<?php
require_once 'Database.php';

class LoginController {
    private $error = '';
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function handleLoginRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            return $this->processLogin();
        }
        return false;
    }
    
    private function processLogin() {
        $fullname = $this->sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($fullname) || empty($password)) {
            $this->error = "Please enter both username and password";
            return false;
        }
        
        try {
            $conn = Database::getInstance()->getConnection();
            $stmt = $conn->prepare("SELECT * FROM users WHERE fullname = ?");
            $stmt->bind_param("s", $fullname);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['username'] = $user['fullname'];
                    return true;
                }
            }
            
            $this->error = "Invalid username or password";
            return false;
            
        } catch (Exception $e) {
            $this->error = "Login error occurred";
            return false;
        }
    }
    
    private function sanitizeInput($input) {
        return trim(htmlspecialchars($input));
    }
    
    public function getError() {
        return $this->error;
    }
} 