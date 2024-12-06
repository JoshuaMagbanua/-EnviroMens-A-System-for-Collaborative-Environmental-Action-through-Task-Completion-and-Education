<?php
class UserManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function createUser($username, $age, $gender, $continent, $password, $profile_picture) {
        try {
            // Check if username already exists
            $checkQuery = "SELECT user_id FROM users WHERE username = :username";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return "Username already exists";
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $query = "INSERT INTO users (username, age, gender, continent, password, profile_picture) 
                     VALUES (:username, :age, :gender, :continent, :password, :profile_picture)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':continent', $continent);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':profile_picture', $profile_picture);
            
            if ($stmt->execute()) {
                return "success";
            } else {
                return "Failed to create user";
            }
            
        } catch(PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
    
    public function validateLogin($username, $password) {
        try {
            $query = "SELECT user_id, username, password, profile_picture FROM users WHERE username = :username";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['profile_picture'] = $user['profile_picture'];
                    return true;
                }
            }
            return false;
            
        } catch(PDOException $e) {
            return false;
        }
    }
}