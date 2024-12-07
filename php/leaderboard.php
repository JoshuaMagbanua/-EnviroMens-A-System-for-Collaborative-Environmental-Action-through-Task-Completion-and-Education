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
$filter = $_GET['filter'] ?? 'weekly';
$region = $_GET['region'] ?? null;
$leaderboard = $taskManager->getLeaderboard($filter, $region);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - EnviroMens</title>
    <link rel="stylesheet" href="../css/leaderboard.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <?php include 'components/user_navbar.php'; ?>

    <div class="container">
        <h1 class="page-title">Leaderboard</h1>

        <div class="leaderboard-container">
            <div class="filter-section">
                <button class="filter-btn" onclick="toggleFilter()">FILTER</button>
                <div class="filter-options" id="filterOptions">
                    <a href="?filter=weekly" class="filter-option <?php echo $filter === 'weekly' ? 'active' : ''; ?>">Weekly</a>
                    <a href="?filter=monthly" class="filter-option <?php echo $filter === 'monthly' ? 'active' : ''; ?>">Monthly</a>
                    <a href="?filter=yearly" class="filter-option <?php echo $filter === 'yearly' ? 'active' : ''; ?>">Yearly</a>
                    <div class="continent-select-container">
                        <select class="continent-select" onchange="filterByContinent(this.value)">
                            <option value="" disabled <?php echo $filter !== 'continent' ? 'selected' : ''; ?>>Select Continent</option>
                            <option value="Asia" <?php echo isset($_GET['region']) && $_GET['region'] === 'Asia' ? 'selected' : ''; ?>>Asia</option>
                            <option value="Africa" <?php echo isset($_GET['region']) && $_GET['region'] === 'Africa' ? 'selected' : ''; ?>>Africa</option>
                            <option value="North America" <?php echo isset($_GET['region']) && $_GET['region'] === 'North America' ? 'selected' : ''; ?>>North America</option>
                            <option value="South America" <?php echo isset($_GET['region']) && $_GET['region'] === 'South America' ? 'selected' : ''; ?>>South America</option>
                            <option value="Antarctica" <?php echo isset($_GET['region']) && $_GET['region'] === 'Antarctica' ? 'selected' : ''; ?>>Antarctica</option>
                            <option value="Europe" <?php echo isset($_GET['region']) && $_GET['region'] === 'Europe' ? 'selected' : ''; ?>>Europe</option>
                            <option value="Australia" <?php echo isset($_GET['region']) && $_GET['region'] === 'Australia' ? 'selected' : ''; ?>>Australia</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="leaderboard-content">
                <?php if (!empty($leaderboard)): ?>
                    <?php foreach ($leaderboard as $user): ?>
                        <div class="leaderboard-row">
                            <div class="user-profile">
                                <img src="<?php echo isset($user['profile_picture']) && !empty($user['profile_picture']) 
                                    ? '../profile_pictures/' . htmlspecialchars($user['profile_picture']) 
                                    : '../assets/profile-placeholder.png'; ?>" 
                                     alt="Profile" class="profile-pic">
                                <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
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
    </div>

    <script>
        function toggleFilter() {
            const filterOptions = document.getElementById('filterOptions');
            filterOptions.classList.toggle('show');
        }

        function filterByContinent(region) {
            if (region) {
                window.location.href = `?filter=continent&region=${encodeURIComponent(region)}`;
            }
        }

        document.querySelector('.continent-select').addEventListener('click', function(e) {
            e.stopPropagation();
        });
    </script>
</body>
</html> 