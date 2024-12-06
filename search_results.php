<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/TaskManager.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$taskManager = new TaskManager($conn);

$searchQuery = isset($_GET['query']) ? $_GET['query'] : '';
$searchResults = $taskManager->searchTasks($searchQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="../css/task_list.css">
    <link rel="stylesheet" href="//cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
</head>
<body>
    <div class="container">
        <!-- Left Sidebar -->
        <div class="sidebar">
            <!-- Copy sidebar from task_list.php -->
            <div class="logo-section">
                <img src="../photos/tasks.png" alt="Tasks" class="tasks-logo">
            </div>
            <div class="user-info">
                <img src="../profile_pictures/<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" 
                     alt="Profile Picture" class="user-profile-pic">
                <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <nav class="nav-buttons">
                <a href="profile.php" class="nav-btn">Profile</a>
                <a href="homepage.php" class="nav-btn">Home</a>
                <a href="logout.php" class="nav-btn">Log Out</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header-section">
                <a href="task_list.php" class="back-btn">← Back</a>
                <h2>Search Results for: <?php echo htmlspecialchars($searchQuery); ?></h2>
            </div>
            <table id="searchResultsTable">
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Due Date</th>
                        <th>Task Leader</th>
                        <th>Cause</th>
                        <th>Category</th>
                        <th>Points</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchResults as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['name']); ?></td>
                        <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_leader_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['cause']); ?></td>
                        <td><?php echo htmlspecialchars($task['category']); ?></td>
                        <td><?php echo htmlspecialchars($task['points']); ?></td>
                        <td>
                            <button onclick="showTaskDetails(<?php echo htmlspecialchars(json_encode($task)); ?>)" 
                                    class="join-btn">Join Task</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Right Sidebar -->
        <div class="right-sidebar">
            <div class="search-section">
                <h2>Search Task</h2>
                <form id="searchForm" action="search_results.php" method="GET">
                    <div class="search-box">
                        <input type="text" id="searchInput" name="query" 
                               placeholder="Search task..." 
                               oninput="searchTasks()"
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit" class="search-btn">🔍</button>
                    </div>
                </form>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="//cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script>
        // Initialize DataTable
        let table = new DataTable('#searchResultsTable', {
            responsive: true,
            pageLength: 10,
            order: [[1, 'asc']] // Sort by due date by default
        });

        function showTaskDetails(task) {
            Swal.fire({
                title: task.name,
                html: `
                    <div class="task-details">
                        <p><strong>Due Date:</strong> ${task.due_date}</p>
                        <p><strong>Task Leader:</strong> ${task.task_leader_name}</p>
                        <p><strong>Cause:</strong> ${task.cause}</p>
                        <p><strong>Category:</strong> ${task.category}</p>
                        <p><strong>Points:</strong> ${task.points}</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Join Task',
                cancelButtonText: 'Close'
            }).then((result) => {
                if (result.isConfirmed) {
                    joinTask(task.id);
                }
            });
        }

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

        // Handle form submission
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            const searchInput = document.getElementById('searchInput').value;
            if (!searchInput.trim()) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html> 