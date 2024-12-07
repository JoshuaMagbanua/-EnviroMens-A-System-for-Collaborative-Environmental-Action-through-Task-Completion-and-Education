<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/TaskManager.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? null;
$taskId = $data['task_id'] ?? null;
$points = $data['points'] ?? 0;

if (!$userId || !$taskId) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$taskManager = new TaskManager($conn);

if ($taskManager->isTaskApproved($taskId, $userId)) {
    echo json_encode(['success' => false, 'message' => 'This task has already been approved']);
    exit;
}

$conn->beginTransaction();

try {
    $query1 = "UPDATE task_participants 
               SET completion_status = 'completed', status = 'done' 
               WHERE user_id = :user_id AND task_id = :task_id";
    $stmt1 = $conn->prepare($query1);
    $stmt1->execute([':user_id' => $userId, ':task_id' => $taskId]);
    
    $query2 = "UPDATE users 
               SET total_points = total_points + :points, 
                   tasks_completed = tasks_completed + 1 
               WHERE user_id = :user_id";
    $stmt2 = $conn->prepare($query2);
    $stmt2->execute([':points' => $points, ':user_id' => $userId]);
    
    $query3 = "SELECT name FROM tasks WHERE id = :task_id";
    $stmt3 = $conn->prepare($query3);
    $stmt3->execute([':task_id' => $taskId]);
    $taskName = $stmt3->fetchColumn();

    $message = "Your task '$taskName' has been approved! You earned $points points.";
    $query4 = "INSERT INTO notifications (user_id, message, created_at) 
               VALUES (:user_id, :message, NOW())";
    $stmt4 = $conn->prepare($query4);
    $stmt4->execute([':user_id' => $userId, ':message' => $message]);

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 