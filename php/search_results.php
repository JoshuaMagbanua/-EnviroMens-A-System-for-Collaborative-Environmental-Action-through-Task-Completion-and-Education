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
    <link rel="stylesheet" href="../css/search_results.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="//cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
</head>
<body>
    <?php include '../php/components/user_navbar.php'; ?>
    <span class="search-query">Results for: <?php echo htmlspecialchars($searchQuery); ?></span>

    <div class="container">
        <div class="main-content">
            <div class="search-results-container">
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
    </script>
</body>
</html> 