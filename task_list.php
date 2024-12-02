<?php
session_start();
require_once '../database/dbConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Fetch tasks from database
try {
    $query = "SELECT t.*, u.username as task_leader_name 
              FROM tasks t 
              LEFT JOIN users u ON t.task_leader = u.user_id 
              ORDER BY t.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
    $tasks = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="../css/task_list.css">
</head>
<body>
    <div class="container">
        <!-- Left Sidebar -->
        <div class="sidebar">
            <div class="logo-section">
                <img src="../photos/tasks.png" alt="Tasks" class="tasks-logo">
            </div>
            <div class="user-info">
                <h2 class="username">Juan De La Cruz</h2>
            </div>
            <nav class="nav-buttons">
                <a href="profile.php" class="nav-btn">Profile</a>
                <a href="homepage.php" class="nav-btn">Home</a>
                <a href="logout.php" class="nav-btn">Log Out</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="task-list">
                <?php if (!empty($tasks)): ?>
                    <?php foreach($tasks as $task): ?>
                    <a href="task_info.php?id=<?php echo $task['id']; ?>" class="task-card">
                        <div class="task-info">
                            <h3>Task: <?php echo htmlspecialchars($task['name']); ?></h3>
                            <p>Task Due: <?php echo htmlspecialchars($task['due_date']); ?></p>
                            <p>Task Leader: <?php echo htmlspecialchars($task['task_leader_name']); ?></p>
                            <p>Cause: <?php echo htmlspecialchars($task['cause']); ?></p>
                            <p>Category: <?php echo htmlspecialchars($task['category']); ?></p>
                            <p>Points: <?php echo htmlspecialchars($task['points']); ?></p>
                        </div>
                        <div class="task-leader-image">
                            <img src="../assets/profile-placeholder.png" alt="Leader">
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="search-section">
                <h2>Search Task</h2>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search task..." oninput="searchTasks()">
                    <button class="search-btn">🔍</button>
                </div>
                <div class="suggested-tasks" id="suggestedTasks">
                    <!-- Suggested tasks will appear here -->
                </div>
            </div>
            <div class="action-buttons">
                <a href="create_task.php" class="action-btn">Create Task</a>
                <a href="leaderboard.php" class="action-btn">Leaderboard</a>
            </div>
        </div>
    </div>

    <script>
        function searchTasks() {
            const searchInput = document.getElementById('searchInput').value;
            const suggestedTasks = document.getElementById('suggestedTasks');
            
            if (searchInput.length > 0) {
                // AJAX call to get suggested tasks
                fetch(`search_tasks.php?query=${searchInput}`)
                    .then(response => response.json())
                    .then(data => {
                        suggestedTasks.innerHTML = '';
                        data.forEach(task => {
                            suggestedTasks.innerHTML += `
                                <div class="suggested-task">
                                    <h4>${task.name}</h4>
                                    <p>${task.category}</p>
                                </div>
                            `;
                        });
                    });
            } else {
                suggestedTasks.innerHTML = '';
            }
        }
    </script>
</body>
</html> 