<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/TaskManager.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$taskManager = new TaskManager($conn);

// Get filter from URL parameter, default to weekly
$filter = $_GET['filter'] ?? 'weekly';
$leaderboard = $taskManager->getLeaderboard($filter);
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
    <!-- Navigation Bar -->
    <nav class="top-nav">
        <div class="nav-content">
            <a href="profile.php">Profile</a>
            <a href="task_list.php">Tasks</a>
            <a href="homepage.php">Home</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <h1 class="page-title">Leaderboard</h1>

    <div class="leaderboard-container">
        <!-- Filter Section -->
        <div class="filter-section">
            <button class="filter-btn" onclick="toggleFilter()">FILTER</button>
            <div class="filter-options" id="filterOptions">
                <a href="?filter=weekly" class="filter-option <?php echo $filter === 'weekly' ? 'active' : ''; ?>">Weekly</a>
                <a href="?filter=monthly" class="filter-option <?php echo $filter === 'monthly' ? 'active' : ''; ?>">Monthly</a>
                <a href="?filter=yearly" class="filter-option <?php echo $filter === 'yearly' ? 'active' : ''; ?>">Yearly</a>
                <a href="?filter=continent" class="filter-option <?php echo $filter === 'continent' ? 'active' : ''; ?>">Continent</a>
            </div>
        </div>

        <!-- Leaderboard Content -->
        <div class="leaderboard-content">
            <?php if (!empty($leaderboard)): ?>
                <?php foreach ($leaderboard as $user): ?>
                    <div class="leaderboard-row">
                        <div class="user-profile">
                            <div class="profile-pic"></div>
                            <span class="user-name"><?php echo htmlspecialchars($user['fullname']); ?></span>
                        </div>
                        <div class="points-value">
                            <?php echo number_format($user['total_points'] ?? 0); ?> Points
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">No data yet</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleFilter() {
            const filterOptions = document.getElementById('filterOptions');
            filterOptions.classList.toggle('show');
        }
    </script>
</body>
</html> 