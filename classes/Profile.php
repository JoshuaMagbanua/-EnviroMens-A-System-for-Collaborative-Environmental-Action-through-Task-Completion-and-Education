<?php
class Profile {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getUserProfile($userId) {
        try {
            $query = "SELECT u.user_id, u.username, u.age, u.gender, u.continent, 
                            u.profile_picture, u.bio, u.total_points, u.tasks_completed, 
                            u.join_date
                     FROM users u 
                     WHERE u.user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function updateProfile($userId, $data) {
        try {
            $query = "UPDATE users 
                     SET profile_picture = :profile_picture,
                         bio = :bio
                     WHERE user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":profile_picture", $data['profile_picture']);
            $stmt->bindParam(":bio", $data['bio']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function updatePoints($userId, $points) {
        try {
            $query = "UPDATE users 
                     SET total_points = total_points + :points,
                         tasks_completed = tasks_completed + 1
                     WHERE user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":points", $points);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getLeaderboard($limit = 10) {
        try {
            $query = "SELECT username, profile_picture, total_points, tasks_completed 
                     FROM users 
                     ORDER BY total_points DESC 
                     LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
}