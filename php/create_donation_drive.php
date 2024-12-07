<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/DonationManager.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('User not logged in');
        }

        $database = new Database();
        $conn = $database->getConnection();
        $donationManager = new DonationManager($conn);

        if (empty($_POST['title']) || empty($_POST['description']) || 
            empty($_POST['goal_amount']) || empty($_POST['end_date'])) {
            throw new Exception('All fields are required');
        }

        $driveData = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'goal_amount' => (int)$_POST['goal_amount'],
            'end_date' => $_POST['end_date'],
            'creator_id' => $_SESSION['user_id']
        ];

        if ($driveData['goal_amount'] < 100) {
            throw new Exception('Goal amount must be at least 100 points');
        }

        if (strtotime($driveData['end_date']) <= time()) {
            throw new Exception('End date must be in the future');
        }

        if ($donationManager->createDonationDrive($driveData)) {
            echo json_encode([
                'success' => true,
                'message' => 'Donation drive created successfully!'
            ]);
        } else {
            throw new Exception('Failed to create donation drive');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Donation Drive - EnviroMens</title>
    <link rel="stylesheet" href="../css/create_donation_drive.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include '../php/components/user_navbar.php'; ?>

    <div class="container">
        <div class="create-drive-card">
            <h1>Create Donation Drive</h1>
            <form id="driveForm" class="drive-form">
                <div class="form-group">
                    <label for="title">Drive Title</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="goal_amount">Goal Points</label>
                    <input type="number" id="goal_amount" name="goal_amount" min="100" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>

                <button type="submit" class="create-btn">Create Drive</button>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('driveForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('create_donation_drive.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Failed to create donation drive');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#85873C',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'donation_drives.php';
                });
            } else {
                throw new Error(data.message || 'Failed to create donation drive');
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: error.message || 'An unexpected error occurred',
                icon: 'error',
                confirmButtonColor: '#85873C'
            });
        });
    });

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('end_date').min = today;
    </script>
</body>
</html> 