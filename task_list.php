<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/TaskManager.php';
require_once 'components/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$database = new Database();
$conn = $database->getConnection();

$taskManager = new TaskManager($conn);

$tasks = $taskManager->getAllTasks();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List - EnviroMens</title>
    <link rel="stylesheet" href="../css/task_list.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/sweet_alert_custom.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-content">
            <div class="user-profile">
                <img src="../profile_pictures/<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" 
                     alt="Profile Picture" class="nav-profile-pic">
                <span class="nav-username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <ul class="nav-links">
                <li><a href="profile.php">Profile</a></li>
                <li><a href="create_task.php">Create Task</a></li>
                <li><a href="task_list.php">Tasks</a></li>
                <li><a href="leaderboard.php">Leaderboard</a></li>
                <li><a href="admin_dashboard.php">Admin</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="logout.php">Log Out</a></li>
                <li><a href="create_post.php">Create Post</a></li>
                <li><a href="posts.php">Posts</a></li>
                <li><a href="post_management.php">Manage Posts</a></li>
                <li>
                    <div class="search-box">
                        <form id="searchForm" action="search_results.php" method="GET">
                            <input type="text" id="searchInput" name="query" 
                                   placeholder="Search task..." oninput="searchTasks()">
                            <button type="submit" class="search-btn">🔍</button>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <div class="task-container">
                <img src="../photos/tasks.png" alt="Tasks" class="tasks-logo">
                <?php if (!empty($tasks)): ?>
                    <?php foreach($tasks as $task): ?>
                        <div class="task-card">
                            <div class="task-info" onclick="showTaskDetails(<?php echo htmlspecialchars(json_encode($task)); ?>)">
                                <div class="task-details">
                                    <h3>Task: <?php echo htmlspecialchars($task['name']); ?></h3>
                                    <p>Task Due: <?php echo htmlspecialchars($task['due_date']); ?></p>
                                    <p>Task Leader: <?php echo htmlspecialchars($task['task_leader_name']); ?></p>
                                    <p>Cause: <?php echo htmlspecialchars($task['cause']); ?></p>
                                    <p>Category: <?php echo htmlspecialchars($task['category']); ?></p>
                                    <p>Points: <?php echo htmlspecialchars($task['points']); ?></p>
                                </div>
                            </div>
                            <div class="task-leader-image">
                                <img src="../profile_pictures/<?php echo htmlspecialchars($task['profile_picture']); ?>" 
                                     alt="Task Leader" 
                                     onerror="this.src='../assets/profile-placeholder.png'">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function searchTasks() {
            const searchInput = document.getElementById('searchInput').value;
            const suggestedTasks = document.getElementById('suggestedTasks');
            
            if (searchInput.length > 0) {
                // AJAX call to get suggested tasks
                fetch(`search_tasks.php?query=${encodeURIComponent(searchInput)}`)
                    .then(response => response.json())
                    .then(data => {
                        suggestedTasks.innerHTML = '';
                        data.forEach(task => {
                            const taskElement = document.createElement('div');
                            taskElement.className = 'suggested-task';
                            taskElement.innerHTML = `
                                <h4>${task.name}</h4>
                                <p>${task.category}</p>
                            `;
                            taskElement.onclick = () => {
                                window.location.href = `search_results.php?query=${encodeURIComponent(task.name)}`;
                            };
                            suggestedTasks.appendChild(taskElement);
                        });
                    });
            } else {
                suggestedTasks.innerHTML = '';
            }
        }

        document.getElementById('searchForm').addEventListener('submit', function(e) {
            const searchInput = document.getElementById('searchInput').value;
            if (!searchInput.trim()) {
                e.preventDefault();
            }
        });

        function showTaskDetails(task) {
            Swal.fire({
                title: task.name,
                html: `
                    <div class="task-details-popup">
                        <p><strong>Due Date:</strong> ${task.due_date}</p>
                        <p><strong>Task Leader:</strong> ${task.task_leader_name}</p>
                        <p><strong>Cause:</strong> ${task.cause}</p>
                        <p><strong>Category:</strong> ${task.category}</p>
                        <p><strong>Points:</strong> ${task.points}</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Join Task',
                cancelButtonText: 'Back',
                confirmButtonColor: '#85873C',
                cancelButtonColor: '#6c757d',
                showCloseButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    joinTask(task.id);
                }
            });
        }

        function joinTask(taskId) {
            fetch(`join_task.php?task_id=${taskId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'You have successfully joined the task',
                            icon: 'success',
                            confirmButtonColor: '#85873C'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to join task',
                            icon: 'error',
                            confirmButtonColor: '#85873C'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong',
                        icon: 'error',
                        confirmButtonColor: '#85873C'
                    });
                });
        }
    </script>
</body>
</html> 