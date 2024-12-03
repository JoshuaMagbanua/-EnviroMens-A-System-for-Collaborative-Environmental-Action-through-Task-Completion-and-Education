<?php
$servername = "localhost";
$username = "root"; // Missing username
$password = ""; // Missing password 
$dbname = "tasks";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $task = htmlspecialchars($_POST['task']);
    $task_due = htmlspecialchars($_POST['task_due']);
    $task_leader = htmlspecialchars($_POST['task_leader']);
    $cause = htmlspecialchars($_POST['cause']);
    $category = htmlspecialchars($_POST['category']);
    $points = htmlspecialchars($_POST['points']);

    // Simple validation
    if (empty($task) || empty($task_due) || empty($task_leader) || empty($category) || empty($points)) {
        echo "Please fill out all required fields.";
        exit();
    }

    // Create Task object and save to database
    require_once('task.php');
    $taskObj = new Task($task, $task_due, $task_leader, $cause, $category, $points, $conn);
    
    try {
        $taskObj->saveToDatabase();
        echo "Task created successfully!";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Task</title>
    <link rel="stylesheet" href="createStyle.css">
</head>
<body>
    <div class="form-container">
        <h2>Task Details</h2>
        <?php if(isset($task)): ?>
            <p>Task: <?php echo $task; ?></p>
            <p>Due Date: <?php echo $task_due; ?></p>
            <p>Task Leader: <?php echo $task_leader; ?></p>
            <p>Cause: <?php echo $cause; ?></p>
            <p>Category: <?php echo $category; ?></p>
            <p>Points: <?php echo $points; ?></p>
        <?php endif; ?>
        <button onclick="window.location.href='create.php'">Create Another Task</button>
    </div>
</body>
</html>