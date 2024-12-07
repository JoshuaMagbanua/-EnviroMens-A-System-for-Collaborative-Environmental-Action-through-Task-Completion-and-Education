<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/TaskManager.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$taskManager = new TaskManager($conn);
$tasks = $taskManager->getAllTasks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List - EnviroMens</title>
    <link rel="stylesheet" href="../../css/task_list.css">
    <link rel="stylesheet" href="../../css/navbar.css">
</head>
<body>
    <?php include '../components/user_navbar.php'; ?>
    
    <!-- Rest of your task list content -->
</body>
</html>