<?php
class UserManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createUser($fullname, $age, $gender, $continent, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (fullname, age, gender, continent, password) 
                     VALUES (:fullname, :age, :gender, :continent, :password)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':continent', $continent);
            $stmt->bindParam(':password', $hashedPassword);
            
            return $stmt->execute() ? "success" : "Failed to create user";
        } catch(PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
}