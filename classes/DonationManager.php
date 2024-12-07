<?php
class DonationManager {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function processDonation($userId, $driveId, $amount) {
        try {
            $this->conn->beginTransaction();

            // First check if user has enough points
            $userQuery = "SELECT total_points FROM users WHERE user_id = :user_id";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->bindParam(':user_id', $userId);
            $userStmt->execute();
            $userPoints = $userStmt->fetch(PDO::FETCH_ASSOC)['total_points'];

            if ($userPoints < $amount) {
                throw new Exception('Insufficient points');
            }

            // Insert donation record
            $donationQuery = "INSERT INTO donations (user_id, drive_id, amount, donation_date) 
                            VALUES (:user_id, :drive_id, :amount, NOW())";
            $donationStmt = $this->conn->prepare($donationQuery);
            $donationStmt->bindParam(':user_id', $userId);
            $donationStmt->bindParam(':drive_id', $driveId);
            $donationStmt->bindParam(':amount', $amount);
            
            if (!$donationStmt->execute()) {
                throw new Exception('Failed to record donation');
            }

            // Update user's points
            $updateUserQuery = "UPDATE users 
                              SET total_points = total_points - :amount 
                              WHERE user_id = :user_id";
            $updateUserStmt = $this->conn->prepare($updateUserQuery);
            $updateUserStmt->bindParam(':amount', $amount);
            $updateUserStmt->bindParam(':user_id', $userId);
            
            if (!$updateUserStmt->execute()) {
                throw new Exception('Failed to update user points');
            }

            // Update donation drive's current amount
            $updateDriveQuery = "UPDATE donation_drives 
                               SET current_amount = current_amount + :amount 
                               WHERE drive_id = :drive_id";
            $updateDriveStmt = $this->conn->prepare($updateDriveQuery);
            $updateDriveStmt->bindParam(':amount', $amount);
            $updateDriveStmt->bindParam(':drive_id', $driveId);
            
            if (!$updateDriveStmt->execute()) {
                throw new Exception('Failed to update drive amount');
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Donation Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDonationDrive($driveId) {
        $query = "SELECT * FROM donation_drives WHERE drive_id = :drive_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':drive_id', $driveId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllDonationDrives() {
        try {
            $query = "SELECT d.*, u.username as creator_name,
                            (SELECT COUNT(*) FROM donations WHERE drive_id = d.drive_id) as donor_count
                     FROM donation_drives d
                     LEFT JOIN users u ON d.creator_id = u.user_id
                     WHERE d.end_date >= CURRENT_DATE
                     ORDER BY d.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching donation drives: " . $e->getMessage());
            return [];
        }
    }

    public function createDonationDrive($data) {
        try {
            $query = "INSERT INTO donation_drives 
                     (title, description, goal_amount, creator_id, end_date, created_at) 
                     VALUES 
                     (:title, :description, :goal_amount, :creator_id, :end_date, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':goal_amount', $data['goal_amount']);
            $stmt->bindParam(':creator_id', $data['creator_id']);
            $stmt->bindParam(':end_date', $data['end_date']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creating donation drive: " . $e->getMessage());
            return false;
        }
    }

    public function getDriveStatistics($driveId) {
        try {
            $query = "SELECT 
                        d.*,
                        COUNT(DISTINCT don.user_id) as total_donors,
                        u.username as creator_name
                     FROM donation_drives d
                     LEFT JOIN donations don ON d.drive_id = don.drive_id
                     LEFT JOIN users u ON d.creator_id = u.user_id
                     WHERE d.drive_id = :drive_id
                     GROUP BY d.drive_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':drive_id', $driveId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching drive statistics: " . $e->getMessage());
            return null;
        }
    }

    public function getUserDonationDrives($userId) {
        try {
            $query = "SELECT d.*, 
                            (SELECT COUNT(*) FROM donations WHERE drive_id = d.drive_id) as donor_count,
                            (SELECT SUM(amount) FROM donations WHERE drive_id = d.drive_id) as total_donations
                     FROM donation_drives d
                     WHERE d.creator_id = :user_id
                     ORDER BY d.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user donation drives: " . $e->getMessage());
            return [];
        }
    }

    public function getDriveDonations($driveId) {
        try {
            $query = "SELECT d.*, u.username, u.profile_picture,
                            DATE_FORMAT(d.donation_date, '%Y-%m-%d') as donation_date
                     FROM donations d
                     JOIN users u ON d.user_id = u.user_id
                     WHERE d.drive_id = :drive_id
                     ORDER BY d.donation_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':drive_id', $driveId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching drive donations: " . $e->getMessage());
            return [];
        }
    }

    public function getDonationStats($driveId) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT user_id) as unique_donors,
                        SUM(amount) as total_amount,
                        MAX(amount) as largest_donation,
                        AVG(amount) as average_donation
                     FROM donations
                     WHERE drive_id = :drive_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':drive_id', $driveId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching donation stats: " . $e->getMessage());
            return null;
        }
    }

    public function getDonationHistory($driveId) {
        try {
            $query = "SELECT 
                        DATE(donation_date) as date,
                        SUM(amount) as daily_total,
                        COUNT(*) as donation_count
                     FROM donations
                     WHERE drive_id = :drive_id
                     GROUP BY DATE(donation_date)
                     ORDER BY date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':drive_id', $driveId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching donation history: " . $e->getMessage());
            return [];
        }
    }

    public function getTopContributors($driveId) {
        try {
            $query = "SELECT u.username, SUM(d.amount) as total_amount
                     FROM donations d
                     JOIN users u ON d.user_id = u.user_id
                     WHERE d.drive_id = :drive_id
                     GROUP BY u.user_id, u.username
                     ORDER BY total_amount DESC
                     LIMIT 5";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':drive_id', $driveId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting top contributors: " . $e->getMessage());
            return [];
        }
    }

    public function getContinentalDistribution($driveId) {
        try {
            $query = "SELECT u.continent, COUNT(DISTINCT d.user_id) as donor_count
                     FROM donations d
                     JOIN users u ON d.user_id = u.user_id
                     WHERE d.drive_id = :drive_id
                     GROUP BY u.continent
                     ORDER BY donor_count DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':drive_id', $driveId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting continental distribution: " . $e->getMessage());
            return [];
        }
    }
} 