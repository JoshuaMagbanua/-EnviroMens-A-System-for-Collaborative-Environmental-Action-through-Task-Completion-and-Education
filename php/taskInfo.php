<?php
session_start();

require_once '../database/dbConnection.php';

class TaskInfo {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function getTaskDetails($taskId) {
        $query = "SELECT t.*, u.username as creator_name,
                  (SELECT COUNT(*) FROM user_tasks ut WHERE ut.task_id = t.id) as participant_count
                  FROM tasks t
                  LEFT JOIN users u ON t.created_by = u.user_id
                  WHERE t.id = :task_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':task_id', $taskId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Usage
$taskInfo = new TaskInfo();
$task = $taskInfo->getTaskDetails($_GET['task_id']);

if ($task === false) {
    echo "Task not found!";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Info</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="task-info-container">
        <div class="task-details">
            <h1>Task Details</h1>
            <p><strong>Task:</strong> <?php echo htmlspecialchars($task['task']); ?></p>
            <p><strong>Due Date:</strong> <?php echo htmlspecialchars($task['task_due']); ?></p>
            <p><strong>Leader:</strong> <?php echo htmlspecialchars($task['task_leader']); ?></p>
            <p><strong>Cause:</strong> <?php echo htmlspecialchars($task['cause']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($task['category']); ?></p>
            <p><strong>Points:</strong> <?php echo htmlspecialchars($task['points']); ?></p>
        </div>
        <div class="task-info-actions">
            <button onclick="window.location.href='dashboard.php'">Close</button>
            <form action="joinTask.php" method="post">
                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                <button type="submit">Join Task</button>
            </form>
        </div>
    </div>
</body>
</html>
