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

if (!$userId || !$taskId) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $query = "UPDATE task_participants 
              SET completion_status = 'failed', status = 'notdone' 
              WHERE user_id = :user_id AND task_id = :task_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':user_id' => $userId, ':task_id' => $taskId]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 