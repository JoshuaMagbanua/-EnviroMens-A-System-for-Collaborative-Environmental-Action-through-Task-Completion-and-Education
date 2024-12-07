<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/PostManager.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$postManager = new PostManager($conn);
$userPosts = $postManager->getUserPosts($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Management - EnviroMens</title>
    <link rel="stylesheet" href="../css/post_management.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-content">
            <ul class="nav-links">
                <li><a href="#" onclick="history.back()" class="back-btn">← Back</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="create_task.php">Create Task</a></li>
                <li><a href="posts.php">Posts</a></li>
                <li><a href="leaderboard.php">Leaderboard</a></li>
                <li><a href="admin_dashboard.php">Admin</a></li>
                <li><a href="logout.php">Log Out</a></li>
                <li><a href="post_management.php">Manage Posts</a></li>
            </ul>
        </div>
    </nav>

    <h1 class="page-title">Post Management</h1>

    <div class="management-container">
        <?php foreach($userPosts as $post): ?>
            <?php $analytics = $postManager->getPostAnalytics($post['post_id']); ?>
            <div class="post-section">
                <div class="post-header">
                    <h2><?php echo substr(htmlspecialchars($post['content']), 0, 50) . '...'; ?></h2>
                    <button class="delete-btn" onclick="confirmDelete(<?php echo $post['post_id']; ?>)">Delete Post</button>
                </div>

                <!-- Post Analytics -->
                <div class="analytics-section">
                    <div class="reaction-stats">
                        <h3>Reaction Statistics</h3>
                        <div class="stats-grid">
                            <div class="stat-box">
                                <span class="stat-number"><?php echo $analytics['statistics']['total_reactions']; ?></span>
                                <span class="stat-label">Total Reactions</span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-number"><?php echo $analytics['statistics']['likes']; ?></span>
                                <span class="stat-label">Likes</span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-number"><?php echo $analytics['statistics']['dislikes']; ?></span>
                                <span class="stat-label">Dislikes</span>
                            </div>
                        </div>
                        <div class="reaction-chart">
                            <canvas id="reactionChart_<?php echo $post['post_id']; ?>"></canvas>
                        </div>
                    </div>
                </div>

                <div class="users-table">
                    <h3>User Reactions</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Reaction</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($analytics['users'])): ?>
                                <?php foreach($analytics['users'] as $user): ?>
                                    <tr>
                                        <td class="user-cell">
                                            <img src="<?php echo isset($user['profile_picture']) && !empty($user['profile_picture']) 
                                                ? '../profile_pictures/' . htmlspecialchars($user['profile_picture']) 
                                                : '../assets/profile-placeholder.png'; ?>" 
                                                alt="Profile" class="user-pic">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </td>
                                        <td><?php echo $user['reaction_type'] === 'like' ? '👍' : '👎'; ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No reactions yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                const ctx_<?php echo $post['post_id']; ?> = document.getElementById('reactionChart_<?php echo $post['post_id']; ?>').getContext('2d');
                new Chart(ctx_<?php echo $post['post_id']; ?>, {
                    type: 'pie',
                    data: {
                        labels: ['Likes', 'Dislikes'],
                        datasets: [{
                            data: [
                                <?php echo $analytics['statistics']['likes']; ?>,
                                <?php echo $analytics['statistics']['dislikes']; ?>
                            ],
                            backgroundColor: [
                                '#85873C',
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
                                    font: { size: 11 }
                                }
                            }
                        }
                    }
                });
            </script>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDelete(postId) {
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
                deletePost(postId);
            }
        });
    }

    function deletePost(postId) {
        fetch(`delete_post.php?post_id=${postId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Your post has been deleted.',
                        icon: 'success',
                        confirmButtonColor: '#85873C'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete post.',
                        icon: 'error',
                        confirmButtonColor: '#85873C'
                    });
                }
            });
    }
    </script>

<style>
    .rankings-section {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin: 20px 0;
    }

    .global-rank, .continent-rank {
        width: 100%;
        background-color: #505823;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }

    .rank-display {
        font-size: 2.5em;
        color: #D8DB7D;
        font-weight: bold;
        margin-top: 10px;
    }

    h2 {
        color: white;
        margin-bottom: 10px;
    }

    .status-icon {
        width: 50px;
        height: 50px;
        object-fit: contain;
    }

    .task-status {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 15px;
        min-width: 60px;
    }

    .task-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        margin-bottom: 10px;
        background-color: #505823;
        border-radius: 10px;
        color: white;
    }

    .task-info {
        flex: 1;
    }

    .task-info p {
        margin: 8px 0;
        font-size: 16px;
    }

    .tasks-section {
        background-color: #505823;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
    }

    .tasks-section h2 {
        color: white;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #D8DB7D;
    }

    .tasks-container {
        max-height: 400px; 
        overflow-y: auto; 
        padding-right: 10px; 
    }

    .tasks-container::-webkit-scrollbar {
        width: 8px;
    }

    .tasks-container::-webkit-scrollbar-track {
        background: #3d421b;
        border-radius: 4px;
    }

    .tasks-container::-webkit-scrollbar-thumb {
        background: #D8DB7D;
        border-radius: 4px;
    }

    .tasks-container::-webkit-scrollbar-thumb:hover {
        background: #85873C;
    }

    .status-icon {
        width: 50px;
        height: 50px;
        object-fit: contain;
    }

    .task-status {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 15px;
        min-width: 60px;
    }

    .task-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        margin-bottom: 10px;
        background-color: #3d421b; 
        border-radius: 10px;
        color: white;
    }

    .task-info {
        flex: 1;
    }

    .task-info p {
        margin: 8px 0;
        font-size: 16px;
    }

    .tasks-container {
        scrollbar-width: thin;
        scrollbar-color: #D8DB7D #3d421b;
    }

    .reaction-stats {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .reaction-chart {
        height: 500px;
        width: 500px;
        margin: 30px auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .chart-container {
        width: 500px;
        height: 500px;
        margin: 0 auto;
        position: relative;
    }
    </style>
</body>
</html> 