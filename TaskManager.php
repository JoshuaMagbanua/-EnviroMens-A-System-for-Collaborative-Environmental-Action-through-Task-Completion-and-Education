<?php
class TaskManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllTasks() {
        try {
            $query = "SELECT t.*, u.username as task_leader_name, u.profile_picture 
                      FROM tasks t 
                      JOIN users u ON t.task_leader = u.user_id 
                      ORDER BY t.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getUserTasks($userId) {
        try {
            $query = "SELECT t.*, u.username as task_leader_name, tp.completion_status, tp.status
                     FROM tasks t
                     INNER JOIN users u ON t.task_leader = u.user_id
                     INNER JOIN task_participants tp ON t.id = tp.task_id
                     WHERE tp.user_id = :user_id
                     ORDER BY t.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getUserTasks: " . $e->getMessage());
            return [];
        }
    }

    public function getLeaderboard($filter = 'weekly', $region = null) {
        try {
            $timeFilter = '';
            $params = [];
            
            switch($filter) {
                case 'monthly':
                    $timeFilter = 'AND tp.join_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                    break;
                case 'yearly':
                    $timeFilter = 'AND tp.join_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                    break;
                case 'continent':
                    if ($region) {
                        $timeFilter = 'AND u.continent = :region';
                        $params[':region'] = $region;
                    }
                    break;
                default:
                    $timeFilter = 'AND tp.join_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                    break;
            }

            $query = "SELECT u.user_id, u.username, u.profile_picture, SUM(t.points) as total_points 
                     FROM users u 
                     LEFT JOIN task_participants tp ON u.user_id = tp.user_id 
                     LEFT JOIN tasks t ON tp.task_id = t.id 
                     WHERE tp.completion_status = 'completed' $timeFilter 
                     GROUP BY u.user_id 
                     ORDER BY total_points DESC";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getTasksByCreator($userId) {
        $query = "SELECT * FROM tasks 
                 WHERE task_leader = :user_id 
                 ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTaskParticipants($taskId) {
        try {
            $query = "SELECT tp.*, u.username, u.gender, u.continent, u.user_id 
                     FROM task_participants tp
                     JOIN users u ON tp.user_id = u.user_id
                     WHERE tp.task_id = :task_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":task_id", $taskId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function joinTask($taskId, $userId) {
        try {
            $checkQuery = "SELECT * FROM task_participants 
                          WHERE task_id = :task_id AND user_id = :user_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':task_id', $taskId);
            $checkStmt->bindParam(':user_id', $userId);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'You have already joined this task'
                ];
            }

            $query = "INSERT INTO task_participants (task_id, user_id, status) 
                     VALUES (:task_id, :user_id, 'ongoing')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->bindParam(':user_id', $userId);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Successfully joined the task'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to join task'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function createTask($taskData) {
        try {
            $query = "INSERT INTO tasks (name, due_date, task_leader, cause, category, points, created_at) 
                      VALUES (:name, :due_date, :task_leader, :cause, :category, :points, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":name", $taskData['name']);
            $stmt->bindParam(":due_date", $taskData['due_date']);
            $stmt->bindParam(":task_leader", $taskData['task_leader']);
            $stmt->bindParam(":cause", $taskData['cause']);
            $stmt->bindParam(":category", $taskData['category']);
            $stmt->bindParam(":points", $taskData['points']);
            
            $stmt->execute();
            return ['success' => true];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function searchTasks($query) {
        $searchQuery = "%{$query}%";
        
        $query = "SELECT t.*, u.username as task_leader_name 
                 FROM tasks t 
                 LEFT JOIN users u ON t.task_leader = u.user_id 
                 WHERE t.name LIKE :search 
                    OR t.cause LIKE :search 
                    OR t.category LIKE :search 
                    OR u.username LIKE :search 
                 ORDER BY t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':search', $searchQuery);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTaskStatus($taskId, $userId) {
        $query = "SELECT * FROM task_participants 
                  WHERE task_id = :task_id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return 'notdone'; 
        }
        
        return $result['status'] ?? 'ongoing';
    }

    public function getTaskAnalytics($taskId) {
        try {
            $statsQuery = "SELECT 
                COUNT(*) as total_participants,
                SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as ongoing,
                SUM(CASE WHEN status = 'notdone' THEN 1 ELSE 0 END) as not_done
            FROM task_participants 
            WHERE task_id = :task_id";
            
            $statsStmt = $this->conn->prepare($statsQuery);
            $statsStmt->bindParam(':task_id', $taskId);
            $statsStmt->execute();
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
            $continentQuery = "SELECT 
                u.continent,
                COUNT(*) as count,
                ROUND((COUNT(*) * 100.0 / (
                    SELECT COUNT(*) 
                    FROM task_participants 
                    WHERE task_id = :task_id
                )), 2) as percentage
            FROM task_participants tp
            JOIN users u ON tp.user_id = u.user_id
            WHERE tp.task_id = :task_id
            GROUP BY u.continent";
            
            $continentStmt = $this->conn->prepare($continentQuery);
            $continentStmt->bindParam(':task_id', $taskId);
            $continentStmt->execute();
            $continentStats = $continentStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!$stats['total_participants']) {
                $stats = [
                    'total_participants' => 0,
                    'completed' => 0,
                    'ongoing' => 0,
                    'not_done' => 0
                ];
            }
            
            return [
                'participation' => $stats,
                'continents' => $continentStats ?: []
            ];
        } catch (PDOException $e) {
            return [
                'participation' => [
                    'total_participants' => 0,
                    'completed' => 0,
                    'ongoing' => 0,
                    'not_done' => 0
                ],
                'continents' => []
            ];
        }
    }

    public function getUserGlobalRank($userId) {
        try {
            $query = "WITH UserRanks AS (
                        SELECT user_id, 
                               DENSE_RANK() OVER (ORDER BY total_points DESC) as rank
                        FROM users
                        WHERE total_points > 0
                      )
                      SELECT rank 
                      FROM UserRanks 
                      WHERE user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['rank'] : 'N/A';
        } catch(PDOException $e) {
            return 'N/A';
        }
    }

    public function getUserContinentRank($userId) {
        try {
            $query = "WITH UserRanks AS (
                        SELECT u.user_id, 
                               DENSE_RANK() OVER (
                                   PARTITION BY u.continent 
                                   ORDER BY u.total_points DESC
                               ) as rank
                        FROM users u
                        WHERE u.total_points > 0
                      )
                      SELECT rank 
                      FROM UserRanks 
                      WHERE user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['rank'] : 'N/A';
        } catch(PDOException $e) {
            return 'N/A';
        }
    }

    public function saveTaskEvidence($taskId, $userId, $evidenceFiles) {
        try {
            foreach ($evidenceFiles as $file) {
                $query = "INSERT INTO task_evidence (task_id, user_id, file_path, upload_date) 
                         VALUES (:task_id, :user_id, :file_path, NOW())";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":task_id", $taskId);
                $stmt->bindParam(":user_id", $userId);
                $stmt->bindParam(":file_path", $file);
                $stmt->execute();
            }
            return true;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getTaskEvidence($taskId, $userId) {
        try {
            $query = "SELECT * FROM task_evidence 
                     WHERE task_id = :task_id AND user_id = :user_id 
                     ORDER BY upload_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":task_id", $taskId);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function hasUserJoinedTask($taskId, $userId) {
        try {
            $query = "SELECT COUNT(*) FROM task_participants 
                     WHERE task_id = :task_id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":task_id", $taskId);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function addTaskParticipant($taskId, $userId) {
        try {
            $query = "INSERT INTO task_participants (task_id, user_id, join_date, completion_status, status) 
                     VALUES (:task_id, :user_id, NOW(), 'pending', 'ongoing')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":task_id", $taskId);
            $stmt->bindParam(":user_id", $userId);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function hasEvidence($taskId, $userId) {
        try {
            $query = "SELECT COUNT(*) FROM task_evidence 
                     WHERE task_id = :task_id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":task_id", $taskId);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getTasksByUser($userId) {
        try {
            $query = "SELECT t.*, u.username as task_leader_name, 
                            COALESCE(tp.completion_status, 'pending') as completion_status,
                            COALESCE(tp.status, 'ongoing') as participant_status
                     FROM tasks t
                     JOIN users u ON t.task_leader = u.user_id
                     LEFT JOIN task_participants tp ON t.id = tp.task_id AND tp.user_id = :user_id
                     WHERE tp.user_id = :user_id
                     ORDER BY t.due_date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getUserTotalPoints($userId) {
        try {
            $query = "SELECT SUM(t.points) as total_points 
                     FROM tasks t 
                     JOIN task_participants tp ON t.id = tp.task_id 
                     WHERE tp.user_id = :user_id 
                     AND tp.completion_status = 'completed'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_points'] ?? 0;
        } catch(PDOException $e) {
            return 0;
        }
    }

    public function getUserCurrentPoints($userId) {
        try {
            $query = "SELECT SUM(t.points) as current_points 
                     FROM tasks t 
                     JOIN task_participants tp ON t.id = tp.task_id 
                     WHERE tp.user_id = :user_id 
                     AND tp.completion_status = 'ongoing'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['current_points'] ?? 0;
        } catch(PDOException $e) {
            return 0;
        }
    }

    public function isTaskApproved($taskId, $userId) {
        try {
            $query = "SELECT completion_status 
                     FROM task_participants 
                     WHERE task_id = :task_id 
                     AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['completion_status'] === 'completed';
        } catch(PDOException $e) {
            return false;
        }
    }

    public function submitTaskEvidence($taskId, $userId, $evidenceFilename) {
        try {
            $this->conn->beginTransaction();

            // First, insert into task_evidence table
            $evidenceQuery = "INSERT INTO task_evidence (task_id, user_id, file_path, upload_date) 
                             VALUES (:task_id, :user_id, :file_path, NOW())";
            
            $evidenceStmt = $this->conn->prepare($evidenceQuery);
            $evidenceStmt->bindParam(':task_id', $taskId);
            $evidenceStmt->bindParam(':user_id', $userId);
            $evidenceStmt->bindParam(':file_path', $evidenceFilename);
            
            if (!$evidenceStmt->execute()) {
                error_log("Failed to insert task evidence: " . print_r($evidenceStmt->errorInfo(), true));
                $this->conn->rollBack();
                return false;
            }

            // Then update task_participants table
            $participantQuery = "UPDATE task_participants 
                               SET completion_status = 'completed',
                                   status = 'done'
                               WHERE task_id = :task_id 
                               AND user_id = :user_id";
            
            $participantStmt = $this->conn->prepare($participantQuery);
            $participantStmt->bindParam(':task_id', $taskId);
            $participantStmt->bindParam(':user_id', $userId);
            
            if (!$participantStmt->execute()) {
                error_log("Failed to update task participant status: " . print_r($participantStmt->errorInfo(), true));
                $this->conn->rollBack();
                return false;
            }

            // Update user's total points and tasks completed
            $taskQuery = "SELECT points FROM tasks WHERE id = :task_id";
            $taskStmt = $this->conn->prepare($taskQuery);
            $taskStmt->bindParam(':task_id', $taskId);
            $taskStmt->execute();
            $taskPoints = $taskStmt->fetch(PDO::FETCH_ASSOC)['points'];

            $updateUserQuery = "UPDATE users 
                              SET total_points = total_points + :points,
                                  tasks_completed = tasks_completed + 1
                              WHERE user_id = :user_id";
            
            $updateUserStmt = $this->conn->prepare($updateUserQuery);
            $updateUserStmt->bindParam(':points', $taskPoints);
            $updateUserStmt->bindParam(':user_id', $userId);
            
            if (!$updateUserStmt->execute()) {
                error_log("Failed to update user points: " . print_r($updateUserStmt->errorInfo(), true));
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            error_log("PDO Exception in submitTaskEvidence: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->conn->rollBack();
            return false;
        }
    }
} 