<?php
header('Content-Type: application/json');
require_once '../database/dbConnection.php';

$taskId = $_GET['task_id'] ?? null;
$userId = $_GET['user_id'] ?? null;

if (!$taskId || !$userId) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT file_path, upload_date 
              FROM task_evidence 
              WHERE task_id = :task_id 
              AND user_id = :user_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':task_id', $taskId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $evidence = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'evidence' => $evidence
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching evidence'
    ]);
}
?> 