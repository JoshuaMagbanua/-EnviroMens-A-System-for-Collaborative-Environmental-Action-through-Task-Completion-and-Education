<?php
session_start();
require_once '../classes/Database.php';

if (!isset($_SESSION['username']) || !isset($_POST['task_id'])) {
    header("Location: dashboard.php");
    exit();
}

try {
    $conn = Database::getInstance()->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM user_tasks WHERE username = ? AND task_id = ?");
    $stmt->bind_param("si", $_SESSION['username'], $_POST['task_id']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: viewTask.php?task_id=" . $_POST['task_id'] . "&error=already_joined");
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO user_tasks (username, task_id) VALUES (?, ?)");
    $stmt->bind_param("si", $_SESSION['username'], $_POST['task_id']);
    
    if ($stmt->execute()) {
        header("Location: viewTask.php?task_id=" . $_POST['task_id'] . "&success=joined");
    } else {
        header("Location: viewTask.php?task_id=" . $_POST['task_id'] . "&error=failed");
    }
    
} catch (Exception $e) {
    header("Location: viewTask.php?task_id=" . $_POST['task_id'] . "&error=system");
}
exit();
?>
