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

$createdTasks = $taskManager->getTasksByCreator($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EnviroMens</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <ul class="nav-links">
                <li><a href="#" onclick="history.back()" class="back-btn">← Back</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="create_task.php">Create Task</a></li>
                <li><a href="task_list.php">Tasks</a></li>
                <li><a href="leaderboard.php">Leaderboard</a></li>
                <li><a href="admin_dashboard.php">Admin</a></li>
                <li><a href="logout.php">Log Out</a></li>
                <li><a href="create_post.php">Create Post</a></li>
                <li><a href="posts.php">Posts</a></li>
                <li><a href="post_management.php">Manage Posts</a></li>
                <li>
                    <div class="search-box">
                        <form id="searchForm" action="search_results.php" method="GET">
                            <input type="text" id="searchInput" name="query" 
                                   placeholder="Search task...">
                            <button type="submit" class="search-btn">🔍</button>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <h1 class="page-title">Task Management Dashboard</h1>

    <div class="admin-container">
        <?php if (!empty($createdTasks)): ?>
            <?php foreach($createdTasks as $task): ?>
                <?php $analytics = $taskManager->getTaskAnalytics($task['id']); ?>
                <div class="task-section">
                    <div class="task-header">
                        <h2>Task: <?php echo htmlspecialchars($task['name']); ?></h2>
                        <button class="delete-btn" onclick="confirmDelete(<?php echo $task['id']; ?>)">Delete Task</button>
                    </div>
                    
                    <div class="analytics-section">
                        <div class="participation-stats">
                            <h3>Participation Statistics</h3>
                            <div class="stats-grid">
                                <div class="stat-box">
                                    <span class="stat-number"><?php echo $analytics['participation']['total_participants']; ?></span>
                                    <span class="stat-label">Total Volunteers</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number"><?php echo $analytics['participation']['completed']; ?></span>
                                    <span class="stat-label">Completed</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number"><?php echo $analytics['participation']['ongoing']; ?></span>
                                    <span class="stat-label">Ongoing</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number"><?php echo $analytics['participation']['not_done']; ?></span>
                                    <span class="stat-label">Not Started</span>
                                </div>
                            </div>
                            <div class="participation-chart">
                                <canvas id="participationChart_<?php echo $task['id']; ?>"></canvas>
                            </div>
                        </div>
                        <div class="continent-distribution">
                            <h3>Volunteer Distribution by Continent</h3>
                            <canvas id="continentChart_<?php echo $task['id']; ?>"></canvas>
                        </div>
                    </div>

                    <div class="task-details">
                        <p>Due Date: <?php echo htmlspecialchars($task['due_date']); ?></p>
                        <p>Category: <?php echo htmlspecialchars($task['category']); ?></p>
                        <p>Points: <?php echo htmlspecialchars($task['points']); ?></p>
                    </div>
                    
                    <div class="participants-table">
                        <h3>Participants</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                <th>Gender</th>
                                <th>Continent</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $participants = $taskManager->getTaskParticipants($task['id']);
                            if (!empty($participants)):
                                foreach($participants as $participant): 
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($participant['username']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['continent']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['completion_status']); ?></td>
                                    <td>
                                        <button class="view-evidence-btn" 
                                                onclick="viewUserEvidence(<?php echo $participant['user_id']; ?>, 
                                                                        <?php echo $task['id']; ?>, 
                                                                        <?php echo $task['points']; ?>, 
                                                                        '<?php echo htmlspecialchars($participant['username']); ?>')">
                                            View Evidence
                                        </button>
                                    </td>
                                </tr>
                            <?php 
                                endforeach;
                            else: 
                            ?>
                                <tr>
                                    <td colspan="5">No participants yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    </div>
                </div>

                <script>
                    const participationCtx_<?php echo $task['id']; ?> = document.getElementById('participationChart_<?php echo $task['id']; ?>').getContext('2d');
                    new Chart(participationCtx_<?php echo $task['id']; ?>, {
                        type: 'pie',
                        data: {
                            labels: ['Completed', 'Ongoing', 'Not Started'],
                            datasets: [{
                                data: [
                                    <?php echo $analytics['participation']['completed']; ?>,
                                    <?php echo $analytics['participation']['ongoing']; ?>,
                                    <?php echo $analytics['participation']['not_done']; ?>
                                ],
                                backgroundColor: [
                                    '#85873C', 
                                    '#D4D669', 
                                    '#505823'  
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        boxWidth: 12,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Task Completion Status',
                                    font: {
                                        size: 14
                                    }
                                }
                            }
                        }
                    });

                    // Initialize continent chart
                    const continentCtx_<?php echo $task['id']; ?> = document.getElementById('continentChart_<?php echo $task['id']; ?>').getContext('2d');
                    new Chart(continentCtx_<?php echo $task['id']; ?>, {
                        type: 'pie',
                        data: {
                            labels: <?php echo json_encode(array_column($analytics['continents'], 'continent')); ?>,
                            datasets: [{
                                data: <?php echo json_encode(array_column($analytics['continents'], 'percentage')); ?>,
                                backgroundColor: [
                                    '#85873C',
                                    '#505823',
                                    '#C2C784',
                                    '#D4D669',
                                    '#EBEBA7',
                                    '#F2F2D9'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        boxWidth: 12,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Distribution by Continent',
                                    font: {
                                        size: 14
                                    }
                                }
                            }
                        }
                    });
                </script>
                <script>
                    function viewUserEvidence(userId, taskId, points, username) {
                        fetch(`get_evidence.php?task_id=${taskId}&user_id=${userId}`)
                            .then(response => response.json())
                            .then(data => {
                                let imagesHtml = '';
                                if (data.success && data.evidence && data.evidence.length > 0) {
                                    imagesHtml = data.evidence.map(file => 
                                        `<img src="../task_evidence/${file.file_path}" 
                                              alt="Task Evidence" 
                                              style="max-width: 100%; margin-bottom: 10px;">`
                                    ).join('');
                                } else {
                                    imagesHtml = '<p>No evidence uploaded yet.</p>';
                                }

                                Swal.fire({
                                    title: `Task Evidence - ${username}`,
                                    html: `
                                        <div class="evidence-container">
                                            ${imagesHtml}
                                        </div>
                                    `,
                                    showCancelButton: true,
                                    showDenyButton: true,
                                    confirmButtonText: 'Approve',
                                    denyButtonText: 'Decline',
                                    cancelButtonText: 'Close',
                                    confirmButtonColor: '#85873C',
                                    denyButtonColor: '#d33',
                                    cancelButtonColor: '#6c757d',
                                    width: '80%'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        approveTask(userId, taskId, points);
                                    } else if (result.isDenied) {
                                        declineTask(userId, taskId);
                                    }
                                });
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Failed to load evidence',
                                    icon: 'error',
                                    confirmButtonColor: '#85873C'
                                });
                            });
                    }

function approveTask(userId, taskId, points) {
    fetch('approve_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: userId,
            task_id: taskId,
            points: points
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: 'Task approved and points awarded',
                icon: 'success',
                confirmButtonColor: '#85873C'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message || 'Failed to approve task',
                icon: 'error',
                confirmButtonColor: '#85873C'
            });
        }
    });
}

function declineTask(userId, taskId) {
    fetch('decline_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: userId,
            task_id: taskId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Task Declined',
                text: 'The task has been declined',
                icon: 'info',
                confirmButtonColor: '#85873C'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message || 'Failed to decline task',
                icon: 'error',
                confirmButtonColor: '#85873C'
            });
        }
    });
}
</script>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-tasks">
                <p>You haven't created any tasks yet.</p>
                <a href="create_task.php" class="create-task-btn">Create a Task</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDelete(taskId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#85873C',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteTask(taskId);
            }
        });
    }

    function viewEvidence(taskId, userId) {
    fetch(`get_evidence.php?task_id=${taskId}&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const images = data.evidence.map(file => 
                    `<img src="../uploads/task_evidence/${file.file_path}" style="max-width: 100%; margin-bottom: 10px;">`
                ).join('');
                
                Swal.fire({
                    title: 'Task Evidence',
                    html: images,
                    width: '80%',
                    confirmButtonColor: '#85873C'
                });
            }
        });
    }

    function deleteTask(taskId) {
        fetch(`delete_task.php?task_id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Your task has been deleted.',
                        icon: 'success',
                        confirmButtonColor: '#85873C'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete task.',
                        icon: 'error',
                        confirmButtonColor: '#85873C'
                    });
                }
            });
    }
    </script>
</body>
</html> 