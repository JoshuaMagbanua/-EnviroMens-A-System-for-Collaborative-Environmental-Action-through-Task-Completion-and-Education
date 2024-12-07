<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/Profile.php';
require_once '../classes/TaskManager.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$profile = new Profile($conn);
$userProfile = $profile->getUserProfile($_SESSION['user_id']);

$taskManager = new TaskManager($conn);
$userTasks = $taskManager->getUserTasks($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $updateData = [
        'profile_picture' => $_POST['profile_picture'],
        'bio' => $_POST['bio']
    ];
    
    if ($profile->updateProfile($_SESSION['user_id'], $updateData)) {
        $_SESSION['profile_picture'] = $_POST['profile_picture'];
        $success = "Profile updated successfully!";
        $userProfile = $profile->getUserProfile($_SESSION['user_id']);
    } else {
        $error = "Failed to update profile";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - EnviroMens</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="container">
        <div class="profile-card">
            <h2>Profile</h2>
            <div class="profile-image">
                <img src="../profile_pictures/<?php echo htmlspecialchars($userProfile['profile_picture']); ?>" 
                     alt="Profile Picture" class="profile-pic">
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($userProfile['username']); ?></h3>
                <p><?php echo htmlspecialchars($userProfile['age']); ?></p>
                <p><?php echo htmlspecialchars($userProfile['gender']); ?></p>
                <p><?php echo htmlspecialchars($userProfile['continent']); ?></p>
            </div>
            <div class="rankings-section">
                <div class="global-rank">
                    <h2>GLOBAL RANKING</h2>
                    <div class="rank-display">
                        <?php 
                        $globalRank = $taskManager->getUserGlobalRank($_SESSION['user_id']); 
                        echo is_numeric($globalRank) ? "#" . number_format($globalRank) : $globalRank;
                        ?>
                    </div>
                </div>
                
                <div class="continent-rank">
                    <h2>CONTINENT RANKING</h2>
                    <div class="rank-display">
                        <?php 
                        $continentRank = $taskManager->getUserContinentRank($_SESSION['user_id']); 
                        echo is_numeric($continentRank) ? "#" . number_format($continentRank) : $continentRank;
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="tasks-section">
            <h2>My Tasks</h2>
            <div class="tasks-container">
                <?php if (!empty($userTasks)): ?>
                    <?php foreach ($userTasks as $task): ?>
                        <div class="task-card <?php echo htmlspecialchars($task['completion_status']); ?>">
                            <div class="task-info">
                                <p><strong>Task:</strong> <?php echo htmlspecialchars($task['name']); ?></p>
                                <p><strong>Task Due:</strong> <?php echo htmlspecialchars($task['due_date']); ?></p>
                                <p><strong>Task Leader:</strong> <?php echo htmlspecialchars($task['task_leader_name']); ?></p>
                                <p><strong>Cause:</strong> <?php echo htmlspecialchars($task['cause']); ?></p>
                                <p><strong>Category:</strong> <?php echo htmlspecialchars($task['category']); ?></p>
                                <p><strong>Points:</strong> <?php echo htmlspecialchars($task['points']); ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="status-badge <?php echo htmlspecialchars($task['completion_status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($task['completion_status'])); ?>
                                    </span>
                                </p>
                                <div class="task-buttons">
                                    <?php if ($task['completion_status'] !== 'completed' && $task['completion_status'] !== 'failed'): ?>
                                        <form class="evidence-form" id="evidenceForm_<?php echo $task['id']; ?>" enctype="multipart/form-data">
                                            <input type="file" 
                                                   name="evidence" 
                                                   id="evidence_<?php echo $task['id']; ?>" 
                                                   accept="image/*" 
                                                   style="display: none;" 
                                                   onchange="handleFileSelect(this)">
                                            <button type="button" 
                                                    class="upload-btn" 
                                                    onclick="document.getElementById('evidence_<?php echo $task['id']; ?>').click()">
                                                Choose Photo
                                            </button>
                                            <span class="file-name" id="fileName_<?php echo $task['id']; ?>"></span>
                                            <button type="button" 
                                                    class="submit-btn" 
                                                    onclick="uploadEvidence(<?php echo $task['id']; ?>)" 
                                                    style="display: none;">
                                                Upload Evidence
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="task-status">
                                <?php 
                                $statusIcon = '';
                                switch($task['completion_status']) {
                                    case 'completed':
                                        $statusIcon = '<img src="../photos/done.png" alt="Completed" class="status-icon">';
                                        break;
                                    case 'failed':
                                        $statusIcon = '<img src="../photos/notdone.png" alt="Failed" class="status-icon">';
                                        break;
                                    default:
                                        $statusIcon = '<img src="../photos/ongoing.png" alt="Pending" class="status-icon">';
                                        break;
                                }
                                echo $statusIcon;
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-tasks">No tasks joined yet.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="points-section">
            <h2>TOTAL POINTS</h2>
            <div class="points-display">
                <?php 
                $totalPoints = $taskManager->getUserTotalPoints($_SESSION['user_id']);
                echo number_format($totalPoints);
                ?>
            </div>
            <div class="current-points">
                Current Task Points
            </div>
            <div class="current-points-value">
                <?php
                $currentPoints = $taskManager->getUserCurrentPoints($_SESSION['user_id']);
                echo number_format($currentPoints);
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function handleFileSelect(input) {
            const taskId = input.id.split('_')[1];
            const fileNameSpan = document.getElementById(`fileName_${taskId}`);
            const submitBtn = input.parentElement.querySelector('.submit-btn');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                if (!file.type.match('image.*')) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select an image file (JPG, JPEG, PNG, or GIF)',
                        icon: 'error',
                        confirmButtonColor: '#85873C'
                    });
                    input.value = '';
                    fileNameSpan.textContent = '';
                    submitBtn.style.display = 'none';
                    return;
                }
                
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'File size must be less than 5MB',
                        icon: 'error',
                        confirmButtonColor: '#85873C'
                    });
                    input.value = '';
                    fileNameSpan.textContent = '';
                    submitBtn.style.display = 'none';
                    return;
                }

                fileNameSpan.textContent = file.name;
                submitBtn.style.display = 'block';
            } else {
                fileNameSpan.textContent = '';
                submitBtn.style.display = 'none';
            }
        }

        function uploadEvidence(taskId) {
            const fileInput = document.getElementById(`evidence_${taskId}`);
            if (!fileInput.files || !fileInput.files[0]) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select a file first',
                    icon: 'error',
                    confirmButtonColor: '#85873C'
                });
                return;
            }

            const formData = new FormData();
            formData.append('task_id', taskId);
            formData.append('evidence', fileInput.files[0]);

            Swal.fire({
                title: 'Uploading...',
                text: 'Please wait while we upload your evidence',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('upload_evidence.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Evidence uploaded successfully',
                        icon: 'success',
                        confirmButtonColor: '#85873C'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to upload evidence',
                    icon: 'error',
                    confirmButtonColor: '#85873C'
                });
            });
        }
    </script>
</body>
</html> 