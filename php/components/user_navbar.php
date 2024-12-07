<?php
require_once __DIR__ . '/../includes/user_check.php';
?>

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
            <li><a href="donation_drives.php">Donation Drives</a></li>
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