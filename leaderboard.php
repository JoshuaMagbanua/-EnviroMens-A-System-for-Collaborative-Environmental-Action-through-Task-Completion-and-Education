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

// Get username from database
try {
    $query = "SELECT username FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $result['username'] ?? 'User';
} catch(PDOException $e) {
    $username = 'User';
}

// Get leaderboard data
try {
    $query = "SELECT u.username, u.fullname, COUNT(t.id) as tasks_completed, 
              SUM(t.points) as total_points 
              FROM users u 
              LEFT JOIN tasks t ON u.user_id = t.task_leader AND t.status = 'completed'
              GROUP BY u.user_id
              ORDER BY total_points DESC
              LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $leaderboard = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - EnviroMens</title>
    <link rel="stylesheet" href="../css/leaderboard.css">
</head>
<body>
    <div class="container">
        <!-- Left Sidebar -->
        <div class="sidebar">
            <img src="../assets/leaderboard.png" alt="Leaderboard" class="logo">
            <h2 class="username"><?php echo htmlspecialchars($username); ?></h2>
            <nav>
                <button onclick="window.location.href='profile.php'">Profile</button>
                <button onclick="window.location.href='home.php'">Home</button>
                <button onclick="window.location.href='logout.php'">Log Out</button>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Leaderboard</h1>
            <div id="leaderboard">
                <?php if (!empty($leaderboard)): ?>
                    <table class="leaderboard-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Name</th>
                                <th>Tasks Completed</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboard as $index => $user): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                    <td><?php echo $user['tasks_completed'] ?? 0; ?></td>
                                    <td><?php echo number_format($user['total_points'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">No leaderboard data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 