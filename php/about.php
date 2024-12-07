<?php
session_start();
require_once '../database/dbConnection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - EnviroMens</title>
    <link rel="stylesheet" href="../css/about.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-content">
            <div class="nav-back">
                <a href="#" onclick="history.back()" class="back-btn">← Back</a>
            </div>
            <ul class="nav-links">
                <li><a href="profile.php">Profile</a></li>
                <li><a href="create_task.php">Create Task</a></li>
                <li><a href="task_list.php">Tasks</a></li>
                <li><a href="posts.php">Posts</a></li>
                <li><a href="post_management.php">Manage Posts</a></li>
                <li><a href="leaderboard.php">Leaderboard</a></li>
                <li><a href="logout.php">Log Out</a></li>
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

    <main>
        <h1>About Us</h1>
        
        <div class="text-content">
            <p>
                At EnviroMens, we are driven by a simple but powerful idea: small actions, when 
                taken together, can create a big impact. Our mission is to empower individuals to 
                take daily steps toward protecting and preserving the environment through an 
                engaging, interactive platform. Every day, users are encouraged to complete eco-friendly 
                tasks that contribute to sustainability, learn from a wealth of educational 
                materials, and connect with a community of like-minded individuals who share a 
                passion for environmental action.
            </p>

            <p>
                Our vision is to inspire collective efforts by making environmental responsibility 
                accessible, rewarding, and meaningful. We believe that by turning everyday actions 
                into opportunities for learning and growth, we can spark lasting change. Whether it's 
                reducing waste, conserving energy, planting trees, or spreading awareness, each 
                task is a step toward building a greener and more sustainable planet.
            </p>

            <p>
                EnviroMens is more than just a platform—it's a movement. Through features like 
                profiles that track your environmental contributions, leaderboards that foster friendly 
                competition, and a library of resources to expand your knowledge, we aim to make 
                environmental stewardship an integral and rewarding part of daily life. Together, we 
                can create a future where every small act contributes to the well-being of our planet 
                and future generations. Join us, and let's make a difference—one task at a time.
            </p>
        </div>

        <a href="signup.php" class="get-started">Get Started</a>
    </main>
</body>
</html>
