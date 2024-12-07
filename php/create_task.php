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
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['name']) && isset($_POST['due_date']) && isset($_POST['points'])) {
        $taskData = [
            'name' => trim($_POST['name']),
            'due_date' => trim($_POST['due_date']),
            'task_leader' => $_SESSION['user_id'],
            'cause' => trim($_POST['cause'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'points' => intval($_POST['points'])
        ];

        $result = $taskManager->createTask($taskData);
        
        if ($result['success']) {
            header("Location: task_list.php");
            exit();
        } else {
            $error = "Error: " . $result['message'];
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task - EnviroMens</title>
    <link rel="stylesheet" href="../css/create_task.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <?php include 'components/user_navbar.php'; ?>

    <div class="container">
        <h1 style="font-size: 96px; font-weight: 900;">Create Task</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
              class="task-form" id="createTaskForm" onsubmit="return validateForm(event)">
            <div class="form-group">
                <input type="text" id="name" name="name" placeholder="Task:" required>
            </div>

            <div class="form-group">
                <input type="date" id="due_date" name="due_date" placeholder="Task Due:" required>
            </div>

            <div class="form-group">
                <input type="text" id="task_leader" name="task_leader" 
                       value="<?php echo htmlspecialchars($_SESSION['username']); ?>" 
                       placeholder="Task Leader:" readonly>
            </div>

            <div class="form-group">
                <input type="text" id="cause" name="cause" placeholder="Cause:">
            </div>

            <div class="form-group">
                <select id="category" name="category">
                    <option value="" disabled selected>Category</option>
                    <option value="Land">Land</option>
                    <option value="Air">Air</option>
                    <option value="Water">Water</option>
                </select>
            </div>

            <div class="form-group">
                <select id="points" name="points">
                    <option value="" disabled selected>Points</option>
                    <option value="20">20</option>
                    <option value="40">40</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div class="button-group">
                <button type="submit" class="post-btn">Post</button>
                <div class="back-btn-container">
                    <a href="task_list.php" class="back-btn">Back</a>
                </div>
            </div>
        </form>
    </div>

    <script>
    function validateForm(event) {
        event.preventDefault();
        
        const dueDate = new Date(document.getElementById('due_date').value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (dueDate < today) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date',
                text: 'Task due date cannot be in the past!',
                confirmButtonColor: '#85873C'
            });
            return false;
        }
        const name = document.getElementById('name').value;
        const category = document.getElementById('category').value;
        const points = document.getElementById('points').value;
        const cause = document.getElementById('cause').value;
        
        if (!name || !dueDate || !category || !points || !cause) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please fill in all required fields!',
                confirmButtonColor: '#85873C'
            });
            return false;
        }
        document.getElementById('createTaskForm').submit();
        return true;
    }
    window.addEventListener('load', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('due_date').setAttribute('min', today);
    });
    </script>
</body>
</html> 