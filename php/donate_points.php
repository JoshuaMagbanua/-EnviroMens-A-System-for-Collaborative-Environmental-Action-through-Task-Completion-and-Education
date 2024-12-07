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
$totalPoints = $taskManager->getUserTotalPoints($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Points - EnviroMens</title>
    <link rel="stylesheet" href="../css/donate_points.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <?php include '../php/components/user_navbar.php'; ?>

    <div class="container">
        <div class="donation-card">
            <h1>Donate Your Points</h1>
            <div class="points-display">
                <h2>Your Current Points</h2>
                <div class="points-value"><?php echo number_format($totalPoints); ?></div>
            </div>

            <div class="donation-form">
                <h2>Choose Amount to Donate</h2>
                <div class="quick-amounts">
                    <button onclick="setAmount(100)" class="amount-btn">100 Points</button>
                    <button onclick="setAmount(500)" class="amount-btn">500 Points</button>
                    <button onclick="setAmount(1000)" class="amount-btn">1,000 Points</button>
                    <button onclick="setAmount(<?php echo $totalPoints; ?>)" class="amount-btn">All Points</button>
                </div>
                
                <div class="custom-amount">
                    <label for="donationAmount">Or enter custom amount:</label>
                    <input type="number" id="donationAmount" min="1" max="<?php echo $totalPoints; ?>" 
                           placeholder="Enter points to donate">
                </div>

                <button onclick="confirmDonation()" class="donate-btn">Donate Points</button>
            </div>

            <div class="donation-info">
                <h3>Why Donate?</h3>
                <p>Your donated points will contribute to environmental causes and help support global sustainability initiatives.</p>
                <div class="impact-info">
                    <div class="impact-item">
                        <span class="impact-number">100</span>
                        <span class="impact-text">Points = 1 Tree Planted</span>
                    </div>
                    <div class="impact-item">
                        <span class="impact-number">500</span>
                        <span class="impact-text">Points = Clean Water for 1 Family</span>
                    </div>
                    <div class="impact-item">
                        <span class="impact-number">1000</span>
                        <span class="impact-text">Points = Solar Panel for 1 Home</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const totalPoints = <?php echo $totalPoints; ?>;

        function setAmount(amount) {
            document.getElementById('donationAmount').value = amount;
        }

        function confirmDonation() {
            const amount = parseInt(document.getElementById('donationAmount').value);

            if (!amount || amount <= 0) {
                Swal.fire({
                    title: 'Invalid Amount',
                    text: 'Please enter a valid donation amount greater than 0.',
                    icon: 'error',
                    confirmButtonColor: '#85873C'
                });
                return;
            }

            if (amount > totalPoints) {
                Swal.fire({
                    title: 'Insufficient Points',
                    text: 'You do not have enough points for this donation.',
                    icon: 'error',
                    confirmButtonColor: '#85873C'
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Donation',
                text: `Are you sure you want to donate ${amount} points?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#85873C',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, donate!'
            }).then((result) => {
                if (result.isConfirmed) {
                    processDonation(amount);
                }
            });
        }

        function processDonation(amount) {
            fetch('process_donation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ amount: amount })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Thank You For Your Donation!',
                        html: `
                            <div class="donation-success">
                                <div class="donation-amount">${amount.toLocaleString()} Points</div>
                                <div class="donation-impact">
                                    ${getDonationImpact(amount)}
                                </div>
                                <p class="donation-message">Your contribution will help make our planet greener!</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#85873C',
                        confirmButtonText: 'Continue Making a Difference!',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'donation-success-popup'
                        }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Failed to process donation.',
                        icon: 'error',
                        confirmButtonColor: '#85873C'
                    });
                }
            });
        }

        function getDonationImpact(amount) {
            let impact = '';
            if (amount >= 1000) {
                impact += `<div class="impact-item">🏠 ${Math.floor(amount/1000)} Solar Panel${amount >= 2000 ? 's' : ''} Installed</div>`;
            }
            if (amount >= 500) {
                const families = Math.floor((amount % 1000) / 500);
                if (families > 0) {
                    impact += `<div class="impact-item">💧 ${families} Family${families > 1 ? 'ies' : 'y'} Given Clean Water</div>`;
                }
            }
            const trees = Math.floor((amount % 500) / 100);
            if (trees > 0) {
                impact += `<div class="impact-item">🌳 ${trees} Tree${trees > 1 ? 's' : ''} Planted</div>`;
            }
            return impact;
        }
    </script>
</body>
</html> 