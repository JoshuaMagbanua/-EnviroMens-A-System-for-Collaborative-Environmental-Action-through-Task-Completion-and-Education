<?php

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
        $username = $this->sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $this->error = "Please enter both username and password";
            return false;
        }
        
        try {
            if ($username === "admin" && $password === "password") {
                $_SESSION['username'] = $username;
                header("Location: dashboard.php");
                exit();
                return true;
            } else {
                $this->error = "Invalid username or password";
                return false;
            }
        } catch (Exception $e) {
            $this->error = "Login error occurred";
            return false;
        }
    }
    
    private function sanitizeInput($input) {
        return trim($input);
    }
    
    public function getError() {
        return $this->error;
    }
} 