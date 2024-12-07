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
$drives = $donationManager->getAllDonationDrives();
if (empty($drives)) {
    echo '<div class="no-drives">
            <p>No active donation drives found.</p>
            <a href="create_donation_drive.php" class="create-drive-btn">Create a Drive</a>
          </div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Drives - EnviroMens</title>
    <link rel="stylesheet" href="../css/donation_drives.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="container">
        <div class="drives-header">
            <h1>Donation Drives</h1>
            <a href="create_donation_drive.php" class="create-drive-btn">Create Drive</a>
        </div>

        <div class="drives-grid">
            <?php foreach($drives as $drive): ?>
                <div class="drive-card" onclick="showDriveDetails(<?php echo htmlspecialchars(json_encode($drive)); ?>)">
                    <h2><?php echo htmlspecialchars($drive['title']); ?></h2>
                    <div class="drive-info">
                        <p><?php echo htmlspecialchars($drive['description']); ?></p>
                        <div class="progress-bar">
                            <?php 
                            $percentage = ($drive['current_amount'] / $drive['goal_amount']) * 100;
                            ?>
                            <div class="progress" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                        <div class="drive-stats">
                            <span><?php echo number_format($drive['current_amount']); ?> / <?php echo number_format($drive['goal_amount']); ?> Points</span>
                            <span>Ends: <?php echo date('M d, Y', strtotime($drive['end_date'])); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    function showDriveDetails(drive) {
        Swal.fire({
            title: drive.title,
            html: `
                <div class="drive-details">
                    <p>${drive.description}</p>
                    <div class="drive-progress">
                        <div class="progress-bar">
                            <div class="progress" style="width: ${(drive.current_amount / drive.goal_amount) * 100}%"></div>
                        </div>
                        <p>${drive.current_amount.toLocaleString()} / ${drive.goal_amount.toLocaleString()} Points</p>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Donate',
            confirmButtonColor: '#85873C',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `donate_points.php?drive_id=${drive.drive_id}`;
            }
        });
    }
    </script>
</body>
</html> 