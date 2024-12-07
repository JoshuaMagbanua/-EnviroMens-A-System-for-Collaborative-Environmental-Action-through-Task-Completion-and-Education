<?php
session_start();
require_once '../classes/Database.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['task_id'])) {
    header("Location: dashboard.php");
    exit();
}

try {
    $conn = Database::getInstance()->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $_GET['task_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();

    if (!$task) {
        header("Location: dashboard.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM user_tasks WHERE username = ? AND task_id = ?");
    $stmt->bind_param("si", $_SESSION['username'], $_GET['task_id']);
    $stmt->execute();
    $hasJoined = $stmt->get_result()->num_rows > 0;

} catch (Exception $e) {
    $error = "Error loading task: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Task</title>
    <link rel="stylesheet" href="../assets/css/viewTask.css">
</head>
<body>
    <div class="task-container">
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <div class="task-details">
                <h1><?php echo htmlspecialchars($task['task']); ?></h1>
                <div class="task-info">
                    <p><strong>Due Date:</strong> <?php echo htmlspecialchars($task['task_due']); ?></p>
                    <p><strong>Task Leader:</strong> <?php echo htmlspecialchars($task['task_leader']); ?></p>
                    <p><strong>Cause:</strong> <?php echo htmlspecialchars($task['cause']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($task['category']); ?></p>
                    <p><strong>Points:</strong> <?php echo htmlspecialchars($task['points']); ?></p>
                </div>

                <?php if (!$hasJoined): ?>
                    <form action="joinTask.php" method="POST">
                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                        <button type="submit" class="join-button">Join Task</button>
                    </form>
                <?php else: ?>
                    <p class="joined-message">You have joined this task</p>
                <?php endif; ?>

                <button onclick="window.location.href='dashboard.php'" class="back-button">Back to Dashboard</button>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 