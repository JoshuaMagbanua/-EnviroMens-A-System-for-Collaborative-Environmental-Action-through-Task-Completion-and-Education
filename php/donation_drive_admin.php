<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/DonationManager.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$donationManager = new DonationManager($conn);
$myDrives = $donationManager->getUserDonationDrives($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Drive Management - EnviroMens</title>
    <link rel="stylesheet" href="../css/donation_drive_admin.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../php/components/user_navbar.php'; ?>

    <div class="container">
        <h1>Donation Drive Management</h1>

        <?php foreach($myDrives as $drive): ?>
            <?php $donations = $donationManager->getDriveDonations($drive['drive_id']); ?>
            <div class="drive-section">
                <div class="drive-header">
                    <h2><?php echo htmlspecialchars($drive['title']); ?></h2>
                    <div class="drive-stats">
                        <div class="stat-box">
                            <span class="stat-number"><?php echo number_format($drive['current_amount']); ?></span>
                            <span class="stat-label">Points Collected</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-number"><?php echo count($donations); ?></span>
                            <span class="stat-label">Total Donors</span>
                        </div>
                    </div>
                </div>

                <div class="analytics-section">
                    <div class="charts-container">
                        <div class="chart-box">
                            <h3>Top Contributors</h3>
                            <canvas id="contributorsChart_<?php echo $drive['drive_id']; ?>"></canvas>
                        </div>
                        <div class="chart-box">
                            <h3>Donations by Continent</h3>
                            <canvas id="continentChart_<?php echo $drive['drive_id']; ?>"></canvas>
                        </div>
                    </div>
                </div>

                <div class="donations-table">
                    <h3>Donations</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Points</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($donations as $donation): ?>
                                <tr>
                                    <td>
                                        <div class="donor-info">
                                            <img src="../profile_pictures/<?php echo htmlspecialchars($donation['profile_picture']); ?>" 
                                                 alt="Profile" class="donor-pic">
                                            <span><?php echo htmlspecialchars($donation['username']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($donation['amount']); ?> Points</td>
                                    <td><?php echo date('M d, Y H:i', strtotime($donation['donation_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                // Top Contributors Chart
                const contributorsCtx_<?php echo $drive['drive_id']; ?> = document.getElementById('contributorsChart_<?php echo $drive['drive_id']; ?>').getContext('2d');
                <?php
                $topContributors = $donationManager->getTopContributors($drive['drive_id']);
                $contributorLabels = array_map(function($item) {
                    return $item['username'];
                }, $topContributors);
                $contributorValues = array_map(function($item) {
                    return $item['total_amount'];
                }, $topContributors);
                ?>
                new Chart(contributorsCtx_<?php echo $drive['drive_id']; ?>, {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode($contributorLabels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($contributorValues); ?>,
                            backgroundColor: [
                                '#85873C',
                                '#505823',
                                '#D8DB7D',
                                '#C2C784',
                                '#A3A65A'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    color: '#505823',
                                    font: { size: 12 }
                                }
                            },
                            title: {
                                display: true,
                                text: 'Top 5 Contributors',
                                color: '#505823',
                                font: { size: 14 }
                            }
                        }
                    }
                });

                // Continental Distribution Chart
                const continentCtx_<?php echo $drive['drive_id']; ?> = document.getElementById('continentChart_<?php echo $drive['drive_id']; ?>').getContext('2d');
                <?php
                $continentalData = $donationManager->getContinentalDistribution($drive['drive_id']);
                $continentLabels = array_map(function($item) {
                    return $item['continent'];
                }, $continentalData);
                $continentValues = array_map(function($item) {
                    return $item['donor_count'];
                }, $continentalData);
                ?>
                new Chart(continentCtx_<?php echo $drive['drive_id']; ?>, {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode($continentLabels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($continentValues); ?>,
                            backgroundColor: [
                                '#85873C',
                                '#505823',
                                '#D8DB7D',
                                '#C2C784',
                                '#A3A65A',
                                '#6B6D30',
                                '#9B9E4E'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    color: '#505823',
                                    font: { size: 12 }
                                }
                            },
                            title: {
                                display: true,
                                text: 'Donors by Continent',
                                color: '#505823',
                                font: { size: 14 }
                            }
                        }
                    }
                });
            </script>
        <?php endforeach; ?>
    </div>
</body>
</html> 