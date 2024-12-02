<?php
session_start();
require_once '../database/dbConnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Get user information
try {
    $query = "SELECT fullname, age, gender, continent FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If no user found, set default values
    if (!$user) {
        $user = [
            'fullname' => 'User Not Found',
            'age' => 'N/A',
            'gender' => 'N/A',
            'continent' => 'N/A'
        ];
    }
} catch(PDOException $e) {
    // Set default values in case of database error
    $user = [
        'fullname' => 'Error Loading User',
        'age' => 'N/A',
        'gender' => 'N/A',
        'continent' => 'N/A'
    ];
    $error = "Error: " . $e->getMessage();
}

// Get user's total points
try {
    $query = "SELECT SUM(points) as total_points FROM tasks 
              WHERE task_leader = :user_id AND status = 'completed'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $points = $result['total_points'] ?? 0;
} catch(PDOException $e) {
    $points = 0;
}

// Get user's tasks
try {
    $query = "SELECT t.*, u.fullname as task_leader_name 
              FROM tasks t 
              LEFT JOIN users u ON t.task_leader = u.user_id 
              WHERE t.task_leader = :user_id
              ORDER BY t.due_date ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $tasks = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - EnviroMens</title>
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>
    <div class="container">
        <!-- Profile Section -->
        <div class="profile-card">
            <h2>Profile</h2>
            <div class="profile-image">
                <img src="../assets/default-avatar.png" alt="Profile Picture">
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($user['fullname']); ?></h3>
                <p><?php echo htmlspecialchars($user['age']); ?></p>
                <p><?php echo htmlspecialchars($user['gender']); ?></p>
                <p><?php echo htmlspecialchars($user['continent']); ?></p>
            </div>
            <div class="achievement">
                <h3>Ultimate World Saver</h3>
                <img src="../assets/achievement-badge.png" alt="Achievement Badge">
            </div>
        </div>

        <!-- Tasks Section -->
        <div class="tasks-section">
            <h2>Tasks to Complete</h2>
            <div class="tasks-container">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card">
                        <div class="task-info">
                            <p><strong>Task:</strong> <?php echo htmlspecialchars($task['name']); ?></p>
                            <p><strong>Task Due:</strong> <?php echo htmlspecialchars($task['due_date']); ?></p>
                            <p><strong>Task Leader:</strong> <?php echo htmlspecialchars($task['task_leader_name']); ?></p>
                            <p><strong>Cause:</strong> <?php echo htmlspecialchars($task['cause']); ?></p>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($task['category']); ?></p>
                        </div>
                        <div class="task-status">
                            <?php if ($task['status'] === 'completed'): ?>
                                <img src="../assets/check.png" alt="Completed" class="status-icon">
                            <?php elseif ($task['status'] === 'failed'): ?>
                                <img src="../assets/x.png" alt="Failed" class="status-icon">
                            <?php else: ?>
                                <img src="../assets/pending.png" alt="Pending" class="status-icon">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Points Section -->
        <div class="points-section">
            <h2>POINTS</h2>
            <div class="points-display">
                <?php echo number_format($points); ?>
            </div>
        </div>
    </div>
</body>
</html> 