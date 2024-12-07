<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/TaskManager.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$taskId = $_GET['task_id'] ?? null;

if (!$taskId) {
    echo json_encode(['success' => false, 'message' => 'Task ID is required']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$taskManager = new TaskManager($conn);

// Check if user has already joined this task
if ($taskManager->hasUserJoinedTask($taskId, $_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You have already joined this task']);
    exit;
}

// Add the participant
$result = $taskManager->addTaskParticipant($taskId, $_SESSION['user_id']);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Successfully joined the task']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to join task']);
} 